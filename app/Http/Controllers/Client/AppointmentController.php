<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Expert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Type\Integer;

class AppointmentController extends Controller
{
    public function showAppointments($id){
        $expert = Expert::find($id);

        if(!$expert){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid Expert ID' : 'لقد قمت بإدخال معرف خبير خاطئ'
            ] , 404);
        }

        $date = Carbon::create(request('date'));

        //check if the date the user entered is  valid
        if ($date->lt(now()->format('Y-m-d'))) {
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'the date you entered has passed' : 'التاريخ الذي أدخلته قديم'
            ]);
        }

        $todaySchedule = $expert->schedules()->where('day' , $date->shortDayName)->first();
        $start = Carbon::create($todaySchedule->start);
        $end = Carbon::create($todaySchedule->end);

        //check if the expert is available at the date the user entered
        if (!$todaySchedule->isAvailable){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'unAvailable that day' : 'الخبير غير متاح في التاريخ الذي أدخلته'
            ]);
        }

        $todayAppointments = $expert->appointments()->whereDate('from' , $date)->get();
        $schedule = new Collection();


        for ($start ;  ; $start->addHour() ){
            if ($start->isAfter($end)){
                break;
            }
            $found = false;
            if (!$todayAppointments->isEmpty()) {
                foreach ($todayAppointments as $appointment) {
                    if (Carbon::create($appointment->from)->format('H:i:s') == $start->format('H:i:s')) {
                        $found = true;
                        $schedule->add([
                            'hour' => $this->convertTimezone($expert->timezone, auth()->user()->timezone, $appointment->from),
                            'isAvailable' => false,
                        ]);
                    }
                }
            }
            if(!$found){
                $schedule->add([
                    'hour' => $this->convertTimezone($expert->timezone, auth()->user()->timezone,
                        $date->format('Y-m-d').' '.$start->format('H:i:s')),
                    'isAvailable' => true,
                ]);
            }
        }

        return response()->json([
            'status' => 1,
            'data' => $schedule
        ]);
    }


    public function addAppointment($id){
        $user = auth()->user();
        $expert = Expert::find($id);

        if(!$expert){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid Expert ID' : 'لقد قمت بإدخال معرف خبير خاطئ'
            ] , 404);
        }

        $tempDate = Carbon::create(request('date'))->format('Y-m-d H:i:s');
        $date = Carbon::create($this->convertTimezone($user->timezone, $expert->timezone, $tempDate));
        $todaySchedule = $expert->schedules()->where('day' , $date->shortDayName)->first();

        //check if the expert is available at that date
        if (!$todaySchedule->isAvailable){
            return response()->json([
                'status' => 0,
                'message' => $user->local == 'en' ? 'unAvailable that day' : 'الخبير غير متاح في التاريخ الذي أدخلته'
            ]);
        }

        //check if the expert is available at that time
        if ($expert->appointments()->where('from' , $date)->first()){
            return response()->json([
               'status' => 0,
               'message' => $user->local == 'en' ? 'The Expert is unAvailable at that time' : 'لقد قمت بإدخال معرف خبير خاطئ'
            ]);
        }

        if ($user->wallet->total - $expert->hourPrice <0){
            return response()->json([
                'status' => 0,
                'message' => $user->local == 'en' ? 'you dont have enough money to make this appointment' : 'ليس لديك المال الكافي لإتمام هذا الحجز'
            ]);
        }
        $hourPrice = $expert->hourPrice;
        $uWallet = $user->wallet;
        $eWallet = $expert->wallet;

        $uWallet->total = $uWallet->total - $hourPrice;
        $eWallet->total = $eWallet->total + $hourPrice;

        $uWallet->save();
        $eWallet->save();

        Appointment::create([
            'user_id' => $user->id,
            'expert_id' => $expert->id,
            'from' => $date,
            'to' => Carbon::parse($date)->addHour()
        ]);


        return response()->json([
            'status' => 1,
            'message' => $user->local == 'en' ? 'Appointment Added Successfully' : 'تمت إضافة الحجز بنجاح'
        ]);
    }

    public function convertTimezone($fromTimezone, $toTimezone, $date){
        return HTTP::acceptJson()->post('https://www.timeapi.io/api/Conversion/ConvertTimeZone',
            [
                'fromTimeZone' => $fromTimezone,
                'dateTime' => $date,
                'toTimeZone' => $toTimezone,
                'dstAmbiguity' => ''
            ])->json('conversionResult.dateTime');
    }


}



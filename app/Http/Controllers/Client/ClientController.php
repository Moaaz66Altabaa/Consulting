<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Category;
use App\Models\User;
use App\Models\Expert;
use App\Models\Favourite;
use App\Models\Rating;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class ClientController extends Controller
{

    public function showAppointments($id){
        $expert = Expert::find($id);

        if(!$expert){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid Expert ID' : 'لقد قمت بإدخال معرف خبير خاطئ'
            ] , 404);
        }

        $date = Carbon::parse(request('date'));
        if ($date->lt(now()->format('Y-m-d'))) {
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'the date you entered has passed' : 'التاريخ الذي أدخلته قديم'
            ]);
        }
        $todaySchedule = $expert->schedules()->where('day' , $date->shortDayName)->first();

        if (!$todaySchedule->isAvailable){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'unAvailable that day' : 'الخبير غير متاح في التاريخ الذي أدخلته'
            ]);
        }
        $start = Carbon::create($todaySchedule->start);
        $end = Carbon::create($todaySchedule->end);

        $todayAppointments = $expert->appointments()->whereDate('from' , $date)->get();
        $schedule = new Collection();

        for ($start ;  ; $start->addHour() ){
            if ($start->isAfter($end)){
                break;
            }
            $found = false;
            if (!$todayAppointments->isEmpty()) {
                foreach ($todayAppointments as $appointment) {
                    if (Carbon::parse($appointment->from)->format('H:i:s') == $start->format('H:i:s')) {
                        $found = true;
                        $schedule->add([
                            'hour' => $start->format('H:i:s'),
                            'isAvailable' => false,
                        ]);
                    }
                }
            }
            if(!$found){
                $schedule->add([
                    'hour' => $start->format('H:i:s'),
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

        if ($user->wallet->total - $expert->hourPrice <0){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'you dont have enough money to make this appointment' : 'ليس لديك المال الكافي لإتمام هذا الحجز'
            ]);
        }
        $hourPrice = $expert->hourPrice;
        $uWallet = $user->wallet;
        $eWallet = $expert->user->wallet;

        $uWallet->total = $uWallet->total - $hourPrice;
        $eWallet->total = $eWallet->total + $hourPrice;

        $uWallet->save();
        $eWallet->save();

        $date = Carbon::parse(request('date'));
        $appointment = new Appointment([
            'user_id' => $user->id,
            'expert_id' => $expert->id,
            'from' => $date,
            'to' => Carbon::parse(request('date'))->addHour()
        ]);
        $appointment->save();

        return response()->json([
            'status' => 1,
            'message' => auth()->user()->local == 'en' ? 'Appointment Added Successfully' : 'تمت إضافة الحجز بنجاح'
        ]);
    }


    public function showProfile($id){
        $user = User::find($id);
        if (!$user){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid Client ID' : 'لقد قمت بإدخال معرف عميل خاطئ'
            ]);
        }
        if (!$user->isExpert) {
            $data = collect([
                'userName' => $user->userName,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'imagePath' => $user->imagePath
            ]);

            return response()->json([
                'status' => 1,
                'data' => $data
            ]);
        }
        else{
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Not a Client Account' : 'المعرف الذي قمت بإدخاله ليس لعميل'
            ]);
        }
    }



    public function showWallet(){

        return response()->json([
            'status' => 1,
            'Total' => auth()->user()->wallet->total
        ]);

    }


    public function setLocal(){
        request()->validate([ 'local' => ['required' , 'string' , 'in:en,ar'] ]);
        $user = auth()->user();
        $user->local = \request('local');
        $user->save();

        return response()->json([
            'status' => 1,
            'message' => \request('local') == 'en' ? 'Language Changed Successfully' : 'تم تغيير اللغة بنجاح'
        ]);
    }







}


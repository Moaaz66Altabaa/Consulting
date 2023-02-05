<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\User;
use App\Models\Experience;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;


class ExpertController extends Controller
{

    public function showAppointments()
    {
        $expert = auth()->user()->expert;
        $date = Carbon::parse(request('date'));
        if ($date->lt(now()->format('Y-m-d'))) {
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'the date you entered has passed' : 'التاريخ الذي أدخلته قديم'
            ]);
        }
        $todaySchedule = $expert->schedules()->where('day', $date->shortDayName)->first();
        if (!$todaySchedule->isAvailable) {
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Appointments are unAvailable that day' : 'الحجوزات غير متاحة في التاريخ الذي أدخلته'
            ]);
        }
        $start = Carbon::create($todaySchedule->start);
        $end = Carbon::parse($todaySchedule->end);
        $todayAppointments = $expert->appointments()->whereDate('from', $date)->get();
        $schedule = new Collection();

        for ($start; ; $start->addHour()) {
            if ($start->isAfter($end)) {
                break;
            }
            $found = false;
            if (!$todayAppointments->isEmpty()) {
                foreach ($todayAppointments as $appointment) {
                    if (Carbon::parse($appointment->from)->format('H:i:s') == $start->format('H:i:s')) {
                        $found = true;
                        $schedule->add([
                            'user_id' => $appointment->user_id,
                            'userName' => User::find($appointment->user_id)->userName,
                            'imagePath' => User::find($appointment->user_id)->imagePath,
                            'from' => Carbon::parse($appointment->from)->format('H:i:s'),
                            'to' => Carbon::parse($appointment->to)->format('H:i:s'),
                            'isAvailable' => false,
                        ]);
                    }
                }
            }
            if (!$found) {
                $schedule->add([
                    'hour' => $start->format('H:i:s'),
                    'isAvailable' => true,
                ]);
            }
        }

        return response()->json([
            'status' => 1,
            'data' => $schedule,
            'imagePath' => auth()->user()->imagePath
        ]);
    }


    public function showProfile()
    {
        $user = auth()->user();
        $data = collect([
            'userName' => $user->userName,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'imagePath' => $user->imagePath,
            'category_id' => $user->expert->category_id,
            'categoryName' => $user->expert->category->categoryName,
            'expertDescription' => $user->expert->expertDescription,
            'hourPrice' => $user->expert->hourPrice,
            'rate' => $user->expert->rate,
            'experiences' => $user->expert->experiences,
            'schedules' => $user->expert->schedules
        ]);

        return response()->json([
            'status' => 1,
            'data' => $data
        ]);
    }


    public function updateProfile()
    {
        $validated = $this->validateExpertProfile();
        $user = auth()->user();

//        if (request('image')) {
//            $image_path = $validated['userName'] . '.' . request('image')->extension();
//            request('image')->move(public_path('images', $image_path));
//            $user->imagePath = $image_path;
//        }

        if(request('newPassword')){
            if (!password_verify(request('oldPassword'), $user->password)){
                return response()->json([
                    'status' => 0,
                    'message' => $user->local == 'en' ? 'Old Password is not Correct' : 'كلمة المرور القديمة غير صحيحة'
                ]);
            }
            else{
                $newPassword = request()->validate([ 'password' => ['required', 'string', 'min:8', 'confirmed'] ]);
                $user->password = Hash::make($newPassword['password']);
                $user->save();
            }
        }


        $user->update($validated);

        $expert = $user->expert;
        $expert->update($validated);
        $expert->experiences()->delete();


        if (request('experience')) {
            $experiences = json_decode(\request('experience') , true);
            foreach ($experiences as $experience) {
                $newExperience = new Experience($experience);
                $newExperience->expert_id = $expert->id;
                $newExperience->save();
            }
        }

        $schedules = json_decode(\request('schedule') , true);
        foreach ($schedules as $newSchedule) {
            foreach ($expert->schedules as $oldSchedule) {
                if($oldSchedule->day == $newSchedule['day'] ){
                    $oldSchedule->update($newSchedule);
                    $oldSchedule->save();
                    break;
                }
            }
        }

        return response()->json([
            'status' => 1,
            'message' => $user->local == 'en' ? 'Updated successfully' : 'تم التحديث بنجاح'
        ]);

    }


    public function validateExpertProfile()
    {
        return request()->validate([
            'imagePath' => ['string'],
            'userName' => ['required', 'string ', 'max:30'],
            'email' => ['required', 'email'],
            'mobile' => ['required', 'string', 'max:13'],
            'newPassword' => ['required' , 'boolean'],
            'hourPrice' => ['required', 'string'],
            'expertDescription' => ['required', 'string'],
        ]);
    }



}

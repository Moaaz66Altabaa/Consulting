<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Schedule;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Expert;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register()
    {
        if (!request('isExpert')){
            $user = new User($this->validateClientRegistration());

            $user->password = Hash::make(request('password'));
            if (\request('local')){ $user->local = \request('local');}
            $user->save();
            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->save();
        }
        else {
            $validated = $this->validateExpertRegistration();
            $user = new User($validated);

            $user->password = Hash::make($validated['password']);
            if (\request('local')){ $user->local = \request('local');}
            $user->save();

            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->save();

            $expert = new Expert($validated);
            $expert->user_id = $user->id;
            $expert->save();

            if (request('experience')) {
                $experiences = json_decode(\request('experience') , true);
//                $experiences = \request('experience');
                foreach ($experiences as $experience) {
                    $newExperience = new Experience($experience);
                    $newExperience->expert_id = $expert->id;
                    $newExperience->save();
                }
            }

            $schedules = json_decode(\request('schedule') , true);
//            $schedules = \request('schedule');
            foreach ($schedules as $schedule) {
                $newSchedule = new Schedule($schedule);
                $newSchedule->expert_id = $expert->id;
                $newSchedule->save();
            }
        }
        return response()->json([
            'status' => 1,
            'message' => request('local') == 'en' || !request('local') ? 'Registered successfully' : 'تم التسجيل بنجاح',
        ]);
    }

    public function login()
    {
        $credentials = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!auth()->attempt($credentials)) {
            return response()->json([
                'status' => 0,
                'message' => request('local') == 'en' || !request('local') ? 'Invalid Credentials' : 'معلومات الدخول خاطئة'
            ]);
        }

        $user = auth()->user();
        $token = $user->createToken('auth_token')->accessToken;

        request()->validate([ 'local' => ['string' , 'in:en,ar'] ]);
        if (\request('local')){ $user->local = \request('local');}
        $user->save();

        return response()->json([
            'status' => 1,
            'message' => request('local') == 'en' || !request('local') ? 'User Logged In Successfully' : 'تم تسجيل الدخول بنجاح',
            'isExpert' => auth()->user()->isExpert,
            'access_token' => $token
        ]);

    }

    public function logout()
    {
        $user = request()->user();
        $token = $user->token();
        $token->revoke();

        return response()->json([
            'status' => 1,
            'message' => $user->local == 'en' ? 'User Logged Out Successfully' : 'تم تسجيل الخروج بنجاح'
        ]);

    }

    public function validateClientRegistration(){
        return request()->validate([
            'local' => ['string' , 'in:en,ar'],
            'isExpert' => ['required', 'boolean'],
            'userName' => ['required', 'string ', 'max:30'],
            'email' => ['required', 'email', 'unique:users'],
            'mobile' => ['required', 'string' , 'max:13'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    public function validateExpertRegistration(){
        return request()->validate([
            'local' => ['string' , 'in:en,ar'],
            'isExpert' => ['required', 'boolean'],
            'userName' => ['required', 'string ', 'max:30'],
            'email' => ['required', 'email', 'unique:users'],
            'mobile' => ['required', 'string' , 'max:13'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'category_id' => ['required' , 'exists:categories,id'],
            'hourPrice' => ['required' , 'string'],
            'expertDescription' => ['required' , 'string'],
//            'experience' => ['string'],
//            'experience.experienceBody' => ['string'],
//            'schedules.isAvailable' => ['boolean'],
//            'schedules.day' => ['string' , 'in:Sat,Sun,Mon,Tue,Wed,Thu,Fri'],
//            'schedules.start' => ['time'],
//            'schedules.end' => ['time' , 'after:schedule.start'],
        ]);
    }


}

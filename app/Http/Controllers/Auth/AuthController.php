<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\Expert;
use App\Models\ExpertWallet;
use App\Models\Schedule;
use App\Models\User;
use App\Models\ClientWallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(){
        if (request('isExpert')){
            return $this->registerExpert();
        }
        else{
            return $this->registerClient();
        }

    }

    public function registerExpert()
    {
        $validated = $this->validateExpertRegistration();
        //creating expert instance and assigning its password
        $expert = Expert::create($validated);
        $expert->password = Hash::make($validated['password']);
        $expert->save();

        //creating expert's wallet and assigning initial total value
        $expert->wallet()->create();

        //looping for adding expert's experience and schedule
        if (request('experience')) {
//          $experiences = json_decode(\request('experience') , true);
            $experiences = \request('experience');
            foreach ($experiences as $experience) {
                $expert->experiences()->create([
                    'experienceName' => $experience['experienceName'],
                    'experienceBody' => $experience['experienceBody']
                ]);
            }
        }

//      $schedules = json_decode(\request('schedule') , true);
        $schedules = \request('schedule');
        foreach ($schedules as $schedule) {
            $expert->schedules()->create([
                'isAvailable' => $schedule['isAvailable'],
                'day' => $schedule['day'],
                'start' => $schedule['start'],
                'end' => $schedule['end']
            ]);
        }

        return response()->json([
            'status' => 1,
            'message' => $expert->local == 'en' ? 'Registered successfully' : 'تم التسجيل بنجاح',
        ]);
    }

    public function registerClient()
    {
        $validated = $this->validateClientRegistration();
        //creating user instance and assigning its password
        $user = User::create($validated);
        $user->password = Hash::make($validated['password']);
        $user->save();

        //creating user's wallet and assigning initial total value
        $user->wallet()->create();


        return response()->json([
            'status' => 1,
            'message' => $user->local == 'en' ? 'Registered successfully' : 'تم التسجيل بنجاح',
        ]);
    }

    public function login()
    {
        $credentials = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        //the user will login according to their type (client / expert)
        if (\request('isExpert')) {
            return $this->loginExpert($credentials);
        }
        else {
            return $this->loginClient($credentials);
        }

    }

    public function loginExpert($credentials)
    {
        if (!Auth::guard('experts')->attempt($credentials)) {
            return response()->json([
                'status' => 0,
                'message' => request('local') == 'en' ? 'Invalid Credentials' : 'معلومات الدخول خاطئة'
            ]);
        }

        $user = Auth::guard('experts')->user();
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;


        return response()->json([
            'status' => 1,
            'message' => request('local') == 'en' ? 'User Logged In Successfully' : 'تم تسجيل الدخول بنجاح',
            'isExpert' => 1,
            'access_token' => $token
        ]);

    }

    public function loginClient($credentials)
    {
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 0,
                'message' => request('local') == 'en' ? 'Invalid Credentials' : 'معلومات الدخول خاطئة'
            ]);
        }

        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;


        return response()->json([
            'status' => 1,
            'message' => request('local') == 'en' ? 'User Logged In Successfully' : 'تم تسجيل الدخول بنجاح',
            'isExpert' => 0,
            'access_token' => $token
        ]);

    }

    public function logout(){
        if (Auth::user() instanceof App\Models\Expert){
            return $this->logoutExpert();
        }
        else{
            return $this->logoutClient();
        }
    }

    public function logoutExpert()
    {
        $expert = request()->user('experts');
        $expert->tokens()->delete();

        return response()->json([
            'status' => 1,
            'message' => $expert->local == 'en' ? 'User Logged Out Successfully' : 'تم تسجيل الخروج بنجاح'
        ]);

    }

    public function logoutClient()
    {
        $user = request()->user();
        $user->tokens()->delete();

        return response()->json([
            'status' => 1,
            'message' => $user->local == 'en' ? 'User Logged Out Successfully' : 'تم تسجيل الخروج بنجاح'
        ]);

    }

    public function validateClientRegistration(){
        return request()->validate([
            'local' => ['string' , 'in:en,ar'],
            'userName' => ['required', 'string ', 'max:30'],
            'email' => ['required', 'email', 'unique:users' , 'unique:experts'],
            'mobile' => ['string' , 'max:13'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    public function validateExpertRegistration(){
        return request()->validate([
            'local' => ['string' , 'in:en,ar'],
            'userName' => ['required', 'string ', 'max:30'],
            'email' => ['required', 'email', 'unique:users' , 'unique:experts'],
            'mobile' => ['required', 'string' , 'max:13'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'section_id' => ['required' , 'exists:sections,id'],
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

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
    public function index(){
        $categories = Category::all();
        return response()->json([
            'status' => 1,
            'message' => 'All Categories',
            'user_id' => auth()->user() ? auth()->user()->id : '',
            'data' => $categories
        ]);
    }


    public function showCategory($id){
        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid Category ID' : 'لقد قمت بإدخال معرف فئة خاطئ'
            ] , 404);
        }

        $data = new Collection();
        foreach ($category->experts as $expert){

            $data->add([
                'id' => $expert->id,
                'userName' => $expert->user->userName,
                'imagePath' => $expert->user->imagePath,
                'rate' => $expert->rate,
            ]);
        }
        return response()->json([
            'status' => 1,
            'message' => 'All Experts in Category (' . $category->categoryName . ')',
            'data' => $data
        ]);
    }


    public function favourite(){
        if (request('category_id')){
            if(!$category = Category::find(request('category_id'))){
                return response()->json([
                    'status' => 0,
                    'message' => auth()->user()->local == 'en' ? 'Invalid Category ID' : 'لقد قمت بإدخال معرف فئة خاطئ'
                ] , 404);
            }

            $experts = Expert::whereIn('id' , auth()->user()->favourites()->where('category_id' , $category->id )->pluck('expert_id'))->get();
            $data = new Collection();
            foreach ($experts as $expert){

                $data->add([
                    'id' => $expert->id,
                    'userName' => $expert->user->userName,
                    'imagePath' => $expert->user->imagePath,
                    'rate' => $expert->rate,
                ]);
            }
            return response()->json([
                'status' => 1,
                'message' => 'All Favourite Experts in Category (' . $category->categoryName . ')',
                'data' => $data
            ]);
        }
        else{

            $experts = Expert::whereIn('id' , auth()->user()->favourites()->pluck('expert_id'))->get();
            $data = new Collection();
            foreach ($experts as $expert){

                $data->add([
                    'id' => $expert->id,
                    'userName' => $expert->user->userName,
                    'imagePath' => $expert->user->imagePath,
                    'categoryName' => $expert->category->categoryName,
                    'rate' => $expert->rate,
                ]);
            }
            return response()->json([
                'status' => 1,
                'message' => 'All Favourite Experts',
                'data' => $data
            ]);
        }
    }


    public function showExpert($id){
        $expert = Expert::find($id);
        if(!$expert){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid Expert ID' : 'لقد قمت بإدخال معرف خبير خاطئ'
            ] , 404);
        }

        $isAvailable = false;
        $isFavourite = false;
        if (auth()->user()->favourites()->where('expert_id' , $expert->id)->first()){ $isFavourite = true; }
        foreach ($expert->schedules as $schedule){
            if (now()->shortDayName == $schedule->day){
                $isAvailable = $schedule->isAvailable;
            }
        }

        $data = collect([
            'user_id' => $expert->user->id,
            'isFavourite' => $isFavourite,
            'isAvailable' => $isAvailable,
            'mobile' => $expert->user->mobile,
            'email' => $expert->user->email,
            'rate' => $expert->rate,
            'hourPrice' => $expert->hourPrice,
            'expertDescription' => $expert->expertDescription,
            'experience' => $expert->experiences,
            'schedule' => $expert->schedules,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Expert (' . $expert->user->userName . ')',
            'data' => $data
        ]);
    }


    public function rateExpert($id){
        $expert = Expert::find($id);
        if(!$expert){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid Expert ID' : 'لقد قمت بإدخال معرف خبير خاطئ'
            ] , 404);
        }
        if ($rate = $expert->ratings()->where('user_id' , auth()->user()->id)->first()){
            $rate->update(request()->validate([
                'starsNumber' => ['required' , 'min:0' , 'max:5']
            ]));
            $rate->save();

            $sum = 0;
            foreach ($expert->ratings as $rating){
                $sum = $sum + $rating->starsNumber;
            }
            $expertRate = $sum/$expert->ratings()->count();
            $expert->rate = $expertRate;
            $expert->save();

            return response()->json([
                'status' => 1,
                'message' => auth()->user()->local == 'en' ? 'Rate Added Successfully' : 'تمت إضافة التقييم'
            ]);

        }
        $rate = new Rating(request()->validate([
            'starsNumber' => ['required' , 'numeric' , 'min:0' , 'max:5']
        ]));
        $rate->user_id = auth()->user()->id;
        $rate->expert_id = $expert->id;
        $rate->save();

        $sum = 0;
        foreach ($expert->ratings as $rating){
            $sum = $sum + $rating->starsNumber;
        }
        $expertRate = $sum/$expert->ratings()->count();
        $expert->rate = $expertRate;
        $expert->save();

        return response()->json([
            'status' => 1,
            'message' => auth()->user()->local == 'en' ? 'Rate Added Successfully' : 'تمت إضافة التقييم'
        ]);

    }


    public function setFavourite($id){
        $expert = Expert::find($id);
        if(!$expert){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid Expert ID' : 'لقد قمت بإدخال معرف خبير خاطئ'
            ] , 404);
        }

        $client = auth()->user();
        if ($fav = $client->favourites()->where('expert_id' , $expert->id)->first()){
            $fav->delete();

            return response()->json([
                'status' => 1,
                'message' => auth()->user()->local == 'en' ? 'Expert Removed Successfully from Favourite List' : 'تمت إزالة الخبير من المفضلة'
            ]);
        }
        $fav = new Favourite([
            'user_id' => $client->id,
            'expert_id' => $expert->id,
            'category_id' => $expert->category_id
        ]);
        $fav->save();

        return response()->json([
            'status' => 1,
            'message' => auth()->user()->local == 'en' ? 'Expert Added Successfully to Favourite List' : 'تمت إضافة الخبير من المفضلة'
        ]);

    }


    public function search()
    {
        if (request('searchKey')) {
            if (request('onlyExperts') && request('categoryId')) {
                $users = User::where('isExpert' , 1)->where('userName', 'LIKE', '%' . request('searchKey') .'%')->get();
                $experts = new Collection();

                foreach ($users as $user){
                    if ($user->expert->category_id == request('categoryId')) {
                        $experts->add([
                            'id' => $user->expert->id,
                            'userName' => $user->userName,
                            'imagePath' => $user->imagePath,
                            'rate' => $user->expert->rate,
                        ]);
                    }
                }

                return response()->json([
                    'status' => 1,
                    'resultExperts' => $experts
                ]);
            }
            else{
                $categories = Category::where('categoryName', 'LIKE', '%' . request('searchKey') . '%')->get();
                $users = User::where('isExpert' , 1)->where('userName', 'LIKE', '%' . request('searchKey') . '%')->get();
                $experts = new Collection();

                foreach ($users as $user){
                    $experts->add([
                        'id' => $user->expert->id,
                        'userName' => $user->userName,
                        'imagePath' => $user->imagePath,
                        'rate' => $user->expert->rate,
                    ]);
                }

                return response()->json([
                    'status' => 1,
                    'resultExperts' => $experts,
                    'resultCategories' => $categories
                ]);
            }
        }
    }


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


    public function updateProfile(){
        $validated = $this->validateClientProfile();
        $user = auth()->user();

//        if (request('deleteImage')){
//            Storage::delete('images/' . $user->imagePath);
//        }
//        else if (request('image') && !request('deleteImage')) {
//             Storage::delete('images/' . $user->imagePath);
//            $image_path = request('userName') . '.' . request('image')->extension();
//            request()->file('image')->storeAs('images' , $image_path);
//            $user->imagePath = $image_path;
//        }

        if(request('newPassword')){
            if (!password_verify(request('oldPassword'), $user->password)){
                return response()->json([
                    'status' => 0,
                    'message' => auth()->user()->local == 'en' ? 'Old Password is not Correct' : 'كلمة المرور القديمة غير صحيحة'
                ]);
            }
            else{
                $newPassword = request()->validate([ 'password' => ['required', 'string', 'min:8', 'confirmed'] ]);
                $user->password = Hash::make($newPassword['password']);
                $user->save();
            }
        }

        $user->update($validated);

        return response()->json([
            'status' => 1,
            'message' => $user->local == 'en' ? 'Updated successfully' : 'تم التحديث بنجاح'
        ]);

    }


    public function showWallet(){

        return response()->json([
            'status' => 1,
            'Total' => auth()->user()->wallet->total
        ]);

    }


    public function setLocal(){
        \request()->validate([ 'local' => ['required' , 'string' , 'in:en,ar'] ]);
        $user = auth()->user();
        $user->local = \request('local');
        $user->save();

        return response()->json([
            'status' => 1,
            'message' => \request('local') == 'en' ? 'Language Changed Successfully' : 'تم تغيير اللغة بنجاح'
        ]);
    }



    public function validateClientProfile()
    {
        return request()->validate([
//            'image' => ['mimes:jpeg,png,jpg'],
            'imagePath' => ['string'],
            'userName' => ['required', 'string ', 'max:30'],
            'email' => ['required', 'email'],
            'mobile' => ['required', 'string', 'max:13'],
            'newPassword' => ['required' , 'boolean'],
//            'deleteImage' => ['required' , 'boolean'],
        ]);
    }



}


<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
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
            if (!Hash::check(request('oldPassword') , $user->password)){
                return response()->json([
                    'status' => 0,
                    'message' => $user->local == 'en' ? 'Old Password is Incorrect' : 'كلمة المرور القديمة غير صحيحة'
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

    public function validateClientProfile()
    {
        return request()->validate([
//            'image' => ['mimes:jpeg,png,jpg'],
            'imagePath' => ['string'],
            'userName' => ['required', 'string ', 'max:30'],
            'email' => ['required', 'email'],
            'mobile' => ['required', 'string', 'max:13'],
//            'deleteImage' => ['required' , 'boolean'],
        ]);
    }
}

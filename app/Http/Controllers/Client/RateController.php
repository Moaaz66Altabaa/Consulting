<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use Illuminate\Http\Request;

class RateController extends Controller
{
    public function rateExpert($id)
    {
        $expert = Expert::find($id);
        if (!$expert) {
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid Expert ID' : 'لقد قمت بإدخال معرف خبير خاطئ'
            ], 404);
        }

        //if the user has rated this expert before
        if ($rate = $expert->ratings()->where('user_id', auth()->user()->id)->first()) {
            $rate->update(request()->validate([
                'starsNumber' => ['required', 'numeric', 'min:0', 'max:5']
            ]));

            $this->updateExpertRate($expert);

            return response()->json([
                'status' => 1,
                'message' => auth()->user()->local == 'en' ? 'Rate Updated Successfully' : 'تم تعديل التقييم'
            ]);

        }
        else {
            //if this is the first time the user rates this expert
            $data = request()->validate([
                'starsNumber' => ['required', 'numeric', 'min:0', 'max:5']
            ]);
            $data['user_id'] = auth()->user()->id;
            $expert->ratings()->create($data);

            $this->updateExpertRate($expert);

            return response()->json([
                'status' => 1,
                'message' => auth()->user()->local == 'en' ? 'Rate Added Successfully' : 'تمت إضافة التقييم'
            ]);

        }
    }

    public function updateExpertRate($expert){
        $sum = $expert->ratings()->sum('starsNumber');
        $expertRate = $sum / $expert->ratings()->count();
        $expert->rate = $expertRate;
        $expert->save();
    }
}

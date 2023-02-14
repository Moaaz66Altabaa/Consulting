<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ExpertsController extends Controller
{
    public function showExpert($id)
    {
        $expert = Expert::find($id);
        if (!$expert) {
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid Expert ID' : 'لقد قمت بإدخال معرف خبير خاطئ'
            ], 404);
        }

        $isAvailable = $expert->schedules()->where('day', now()->shortDayName)->first()->isAvailable;

        $data = collect([
            'expert_id' => $expert->id,
            'isFavourite' => auth()->user()->favourites()->where('expert_id', $expert->id)->first() ? 1 : 0,
            'isAvailable' => $isAvailable,
            'mobile' => $expert->mobile,
            'email' => $expert->email,
            'rate' => $expert->rate,
            'hourPrice' => $expert->hourPrice,
            'expertDescription' => $expert->expertDescription,
            'experience' => $expert->experiences,
            'schedule' => $expert->schedules,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Expert (' . $expert->userName . ')',
            'data' => $data
        ]);
    }

}


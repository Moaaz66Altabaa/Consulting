<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FavouriteController extends Controller
{
    public function favourite(){

        $experts = Expert::whereIn('id' , auth()->user()->favourites()->pluck('expert_id'))->get();
        $data = new Collection();
        foreach ($experts as $expert){

            $data->add([
                'id' => $expert->id,
                'userName' => $expert->userName,
                'imagePath' => $expert->imagePath,
                'SectionName' => $expert->section->sectionName,
                'rate' => $expert->rate,
            ]);
        }
        return response()->json([
            'status' => 1,
            'message' => 'All Favorite Experts',
            'data' => $data
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
                'message' => auth()->user()->local == 'en' ? 'Expert Was Removed Successfully from Favorite List' : 'تمت إزالة الخبير من المفضلة'
            ]);
        }
        else {
            $client->favourites()->create([
                'expert_id' => $expert->id,
            ]);

            return response()->json([
                'status' => 1,
                'message' => auth()->user()->local == 'en' ? 'Expert Added Successfully to Favorite List' : 'تمت إضافة الخبير من المفضلة'
            ]);

        }
    }
}

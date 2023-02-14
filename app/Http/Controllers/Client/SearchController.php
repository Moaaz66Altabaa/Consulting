<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expert;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    public function search()
    {
        if (request('searchKey')) {
            if (request('onlyExperts') && request('sectionId')) {
               return $this->searchForExperts();
            }
            else{
                return $this->searchForAll();
            }
        }
    }

    public function searchForExperts(){
        $experts = Expert::where('section_id' , request('sectionId'))->where('userName', 'LIKE', '%' . request('searchKey') .'%')->get();
        $results = new Collection();

        foreach ($experts as $expert){
            $results->add([
                'id' => $expert->id,
                'userName' => $expert->userName,
                'imagePath' => $expert->imagePath,
                'rate' => $expert->rate,
                'isFavourite' => auth()->user()->favourites()->where('expert_id' , $expert->id)->first() ? 1 : 0
            ]);
        }

        return response()->json([
            'status' => 1,
            'resultExperts' => $results
        ]);
    }

    public function searchForAll(){
        $sections = Section::where('sectionName', 'LIKE', '%' . request('searchKey') . '%')->get();
        $experts = Expert::where('userName', 'LIKE', '%' . request('searchKey') . '%')->get();

        $results = new Collection();

        foreach ($experts as $expert){
            $results->add([
                'id' => $expert->id,
                'userName' => $expert->userName,
                'imagePath' => $expert->imagePath,
                'rate' => $expert->rate,
                'isFavourite' => auth()->user()->favourites()->where('expert_id' , $expert->id)->first() ? 1 : 0
            ]);
        }

        return response()->json([
            'status' => 1,
            'resultExperts' => $results,
            'resultSections' => $sections
        ]);
    }
}

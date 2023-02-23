<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Expert;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CategoryController extends Controller
{
    public function indexPublic(){
        $categories = Category::all();
        return response()->json([
            'status' => 1,
            'message' => 'All Categories',
            'data' => $categories
        ]);
    }

    public function index(){
        $categories = Category::all();
        $experts = Expert::whereBetween('rate' , [4 , 5])
            ->orderBy('rate' , 'DESC')->get();

        $results = new Collection();
        foreach ($experts as $expert){
            $results->add([
                'id' => $expert->id,
                'userName' => $expert->userName,
                'imagePath' => $expert->imagePath,
                'rate' => $expert->rate,
                'categoryName' => $expert->section->category->categoryName,
                'sectionName' => $expert->section->sectionName,
                'isFavourite' => auth()->user()->favourites()->where('expert_id' , $expert->id)->first() ? 1 : 0
            ]);
        }

        return response()->json([
            'status' => 1,
            'message' => 'All Categories',
            'user_id' => auth()->user()->id,
            'categories' => $categories,
            'topExperts' => $results
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

        $data = $category->sections;
        return response()->json([
            'status' => 1,
            'message' => 'All Sections in Category (' . $category->categoryName . ')',
            'data' => $data
        ]);
    }
}

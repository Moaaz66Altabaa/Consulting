<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


class SectionController extends Controller
{
    public function showSection($id){
        $section = Section::find($id);
        if (!$section){
            return response()->json([
                'status' => 0,
                'message' => auth()->user()->local == 'en' ? 'Invalid Section ID' : 'لقد قمت بإدخال معرف قسم خاطئ'
            ], 404);
        }

        $results = new Collection();
        foreach ($section->experts as $expert){
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
            'message' => 'All Experts in Section (' . $section->sectionName . ')',
            'data' => $results
        ]);
    }

    public function sectionsPublic($id){
        $category = Category::find($id);
        if (!$category){
            return response()->json([
                'status' => 0,
                'message' => 'Invalid Category ID'
            ],404);
        }

        $sections = $category->sections;
        return response()->json([
            'status' => 1,
            'message' => 'All Sections in Category (' . $category->categoryName . ')',
            'data' => $sections
        ]);
    }
}

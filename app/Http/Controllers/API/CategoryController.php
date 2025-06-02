<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Validator;
use App\Http\Controllers\BaseController as BaseController;
use App\Models\WordDictionary;

class CategoryController extends Controller
{
    public function index()
    {
       $categories = Category::select('id', 'category_name','category_icon')->get();

       if (count($categories) >= 1) {
           return response()->json([
               'status' => 1,
               'message' => 'All Categories List',
               'data' => $categories,
           ]);
       } else {

           return response()->json([
               'status' => 0,
               'message' => 'Categories not found...!'
           ]);
       }
    }


    public function categoryWords(Request $request)
    {
       $categoryWords = WordDictionary::select('id', 'word','pronunciation')->where('category_id','=',$request->id)->where('is_approved','=',1)->get();

       if (count($categoryWords) >= 1) {
           return response()->json([
               'status' => 1,
               'message' => 'All Words List of Selected Category.',
               'data' => $categoryWords,
           ]);
       } else {

           return response()->json([
               'status' => 0,
               'message' => 'Words not found in selected category...!',
           ]);
       }
    }
}

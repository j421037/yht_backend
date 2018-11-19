<?php

namespace App\Http\Controllers;

use Auth;
use App\ArticleCategory;
use Illuminate\Http\Request;
use App\Http\Resources\ArticleCategoryResource;

class ArticleCategoryController extends Controller
{
    //
    public function index(Request $request)
    {
    	$list = ArticleCategoryResource::collection(ArticleCategory::all());

    	return response($list, 200);
    }

    public function store(Request $request) 
    {
    	try {
    		$ArticleCategory = ArticleCategory::create(['name' => $request->name, 'user_id' => Auth::user()->id]);

    		if ($ArticleCategory) {
    			return response(['status' => 'success'], 200);
    		}
    	} catch(\Illuminate\Database\QueryException $e) {
    		return response(['status' => 'error', 'error' => $e->getMessage()], 200);
    	}
    }

    public function update(Request $request)
    {
        try {

            $category = ArticleCategory::find($request->id);
            $category->name = $request->name;

            if ($category->save()) {
                return response(['status' => 'success'], 200);
            }

        } catch(\Illuminate\Database\QueryException $e) {
            return response(['status' => 'error', 'error' => $e->getMessage()], 200);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Auth;
use Event;
use App\Article;
use App\ArticleAnswer;
use App\ArticleComment;
use Illuminate\Http\Request;
use App\Events\ArticleAnswerEvent;
use App\Http\Requests\ArticleAnswerRequest;
use App\Http\Resources\ArticleAnswerResource;

class ArticleAnswerController extends Controller
{
    public function store(ArticleAnswerRequest $request)
    {
    	try {
    		$data = $request->all();
    		$data['user_id'] = Auth::user()->id;

    		$answer = ArticleAnswer::create($data);

    		if ($answer) {
                $recevier = Article::find($answer->article_id)->user_id;
                //分发回答事件
                Event::fire(new ArticleAnswerEvent($answer, $recevier));

    			return response(['status' => 'success'], 200);
    		}
    	} catch(\Illuminate\Database\QueryException $e) {
    		return response(['status' => 'error' , 'error' => $e->getMessage()], 200);
    	}
    }

    public function index(Request $request)
    {
    	$list = ArticleAnswer::where(['article_id' => $request->article_id])->orderBy('id', 'desc')->get();

    	return response(ArticleAnswerResource::collection($list), 200);
    }
}

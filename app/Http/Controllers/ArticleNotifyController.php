<?php
/**
* 通知类
*/
namespace App\Http\Controllers;

use Auth;
use App\Article;
use App\ArticleAgree;
use App\ArticleAnswer;
use App\ArticleNotify;
use Illuminate\Http\Request;
use App\Http\Resources\ArticleAgreeNotifyResource;
use App\Http\Resources\ArticleAnswerNotifyResource;

class ArticleNotifyController extends Controller
{
    //点赞动态
    public function agree(Request $request)
    {
    
		$list = $this->_buildData(Auth::user()->id, 0);
		
		$list = ArticleAgreeNotifyResource::collection($list);

		return response($list, 200);							
    	
    }
    //回答的动态
    public function answer(Request $request)
    {
    	$list = $this->_buildData(Auth::user()->id, 1);

    	$list = ArticleAnswerNotifyResource::collection($list);

    	return response($list, 200);
    }

    //所有的动态
    /**
    * @return array 发文数 获赞 回复的数量
    */
    public function AllNotify(Request $request)
    {
        $data = [];
        $data['article'] = Article::where(['user_id' => Auth::user()->id])->count();
        $data['agree'] = ArticleAgree::where(['create_user_id' => Auth::user()->id])->count();
        $data['answer'] = ArticleAnswer::where(['user_id' => Auth::user()->id])->count();

        return response($data, 200);
        //
    }

    /**
    * @param $userId 用户id
    * @param $type 0 点赞 1 回复 
    */
    protected function _buildData($userId,$type)
    {
    	$notify = ArticleNotify::where(['type' => $type])
		    		->where(function($query) use ($userId) {
		    			$query->where(['sender' => $userId])->orWhere(['receiver' => $userId]);
		    		})
		    		->orderBy('id', 'desc')
		    		->get();
		return $notify;
    }
}

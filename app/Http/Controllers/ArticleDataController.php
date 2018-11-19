<?php

namespace App\Http\Controllers;

use Auth;
use Event;
use App\User;
use App\Article;
use App\ArticleAgree;
use App\ArticleData;
use App\Department;
use Illuminate\Http\Request;
use App\Events\ArticleAgreeEvent;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ArticleAgreeResource;
use App\Http\Resources\ArticleAgreeCountResource;

class ArticleDataController extends Controller
{
    /**点赞**/
    public function agreeClick(Request $request)
    {   
        $sender = Auth::user()->id;
        $receiver = Article::find($request->article_id)->user_id;
        /**firstOrCreate 没有则创建， 第一个数组是查询条件， 第二个数组是创建时必要的数据**/
        try {
            DB::beginTransaction();

            $agree = ArticleAgree::firstOrCreate(
                ['article_id' => $request->article_id, 'agree_user_id' => $sender],
                ['article_id' => $request->article_id, 'agree_user_id' => $sender, 'create_user_id' => $receiver]
            );

            $articleData = ArticleData::where(['article_id' => $request->article_id])->first();
            $articleData->agrees += 1;

            $result = $articleData->save();

            if ($result && $agree) {
                DB::commit();    
                //分发点赞提醒事件
                Event::fire(new ArticleAgreeEvent($agree));

                return response(['count' => $articleData->agrees, 'alreadyAgree' => $this->_checkAgree($request->article_id, $sender)], 200);
            } 
        }  catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return response(['status' => 'error', 'error' => $e->getMessage()], 200);
        }
    }
    /**获取赞数**/
    public function agree(Request $request) 
    {
        $articleData = ArticleData::where(['article_id' => $request->article_id])->first();

        return response(['count' => $articleData->agrees, 'alreadyAgree' => $this->_checkAgree($request->article_id, Auth::user()->id)], 200);
    }

    /**点赞明细**/
    public function AgreeDetail(Request $request)
    {
        $list = ArticleAgree::where(['create_user_id' => Auth::user()->id])
                                ->orderBy('id', 'desc')
                                ->get();

        $list = ArticleAgreeResource::collection($list);

        return response($list, 200);
    }

    /**点赞统计**/
    public function AgreeCount(Request $request)
    {
        $list = [];
        $month = ['January','February','March','April','May','June','July','August','September','October','November','December'];

        $list = Department::withCount(['agree' ])
                            ->where('user_id', '<>', null)->get();

        foreach ($list as $k => $v) {
            $list[$k]->detail = new \StdClass();
            foreach($month as $kk => $vv) {
                $currentMonth = $kk + 1;
                $start = date( $request->year.'-'.$currentMonth.'-01 00:00:00', time());
                $end = date( $request->year.'-'.$currentMonth.'-t 23:59:59', time());

                $data = ArticleAgree::where(['agree_user_id' => $v->user_id])
                                        ->where(['create_user_id' => Auth::user()->id])
                                        ->whereBetween('created_at',[$start, $end])
                                        ->count();
                $list[$k]->detail->$vv = $data;
            }
        }

        $list = ArticleAgreeCountResource::collection($list)->sortByDesc('agree_count')->values()->all();

        return response($list);
    }

     /**检查是否已经赞过**/
    protected function _checkAgree($articleId, $userId)
    {
        if (ArticleAgree::where(['article_id' => $articleId, 'agree_user_id' => $userId])->first()) {
            return true;
        }

        return false;
    }
}

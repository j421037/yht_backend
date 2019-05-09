<?php
/**
 * Created by PhpStorm.
 * User: wangxin
 * Date: 2019-04-29
 * Time: 10:42
 */
namespace  App\Observers;

use App\IndexStatistics;
trait UpdateIndex {
    /**
     * rewrite IndexStatistics table
     * @params $param key => value
     */
    public function rewrite(array $param, int $user_id)
    {
        $model = IndexStatistics::where(["user_id" => $user_id])->first();
        if (!$model)
        {
            $model = new IndexStatistics;
            $model->user_id = $user_id;
        }

        foreach ($param as $k => $v)
        {
            $model->$k = $v;
        }

        $model->save();
    }
}
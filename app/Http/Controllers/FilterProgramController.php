<?php
/**
 *用户过滤规则
 * @author  老王
 * @date 2018-11-27
 */

namespace App\Http\Controllers;

use App\FilterProgram;
use Illuminate\Http\Request;
use App\Http\Resources\FilterProgramResource;
use Illuminate\Database\QueryException;
use App\Http\Requests\FilterProgramStoreRequest;

class FilterProgramController extends Controller
{
    public function store(FilterProgramStoreRequest $request)
    {
        try {
            $data = $request->only(['name']);
            $data['user_id'] = $this->getUserId();

            if (FilterProgram::create($data)) {
                return response(['status' => 'success'], 200);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }

    /**更新配置信息
     * 配置信息是以json的格式来存放
     */
    public function updateConf(Request $request)
    {

    }

    public function updateName(Request $request)
    {
        try {
            $model = FilterProgram::find($request->id);
            $model->name = trim($request->name);

            if ($model->save()) {
                return response(['status' => 'success'], 200);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }

    public function del(Request $request)
    {
        try {
            $model = FilterProgram::where(['id' => $request->id, 'user_id' => $this->getUserId()])->first();

            if ($model->delete()) {
                return response(['status' => 'success'], 200);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
}

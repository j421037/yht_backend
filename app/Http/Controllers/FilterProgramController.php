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
use App\Http\Requests\FilterProgramUpdateConfRequest;
use Illuminate\Support\Facades\DB;

class FilterProgramController extends Controller
{
    public function store(FilterProgramStoreRequest $request)
    {
        try {
            $data = $request->only(['name','module']);
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
    public function updateConfig(FilterProgramUpdateConfRequest $request)
    {
        $model = FilterProgram::where(['id' => $request->id, 'user_id' => $this->getUserId()])->first();
        $model->default = (Boolean) $request->default;
        $model->conf = json_encode($request->conf);
        $model->fontsize = $request->fontSize;
        $model->col_visible = json_encode($request->colVisible);

        try {
            //如果当前的方案为默认方案，则首先要把当前用户的其他方案取消

            $cancelDefault = FilterProgram::where(['user_id' => $this->getUserId(),'default' => 1])->update(['default' => 0]);

            if ($model->save()) {
                return response(['status' => 'success']);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
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

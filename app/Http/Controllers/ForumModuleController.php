<?php
/**论坛公共版块**/
namespace App\Http\Controllers;

use App\Department;
use App\ForumModuleMappingDepartment as FMapping;
use App\ForumModule as FModule;
use Illuminate\Http\Request;
use App\Http\Requests\ForumModuleUpdateRequest;
use App\Http\Resources\ForumModuleResource;
use App\Http\Requests\ForumModuleRequestStore;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ForumModuleController extends Controller
{
    protected $model;
    protected $mapping;
    protected $department;

    public function __construct(FModule $model,FMapping $mapping, Department $department)
    {
        $this->model = $model;
        $this->mapping = $mapping;
        $this->department = $department;
    }

    //公共模块
    public function index()
    {
        return response(['data' =>ForumModuleResource::collection($this->mapping->orderBy('index','asc')->get()), 'status' => 'success'], 200);
    }
    //新增公共模块
    public function store(ForumModuleRequestStore $request)
    {
        try {
            if ($this->model->where(['module_name' => $request->name])->count() > 0) {
                throw(new \Exception('该模块已经存在'));
            }

            $this->model->create(['module_name' => $request->name]);

            return response(['status' => 'success']);
        }
        catch (QueryException $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();

            if ($errcode == 23000) {
                $errmsg = "该版块已经存在，不能重复添加";
            }

            return response(['status' => 'error', 'errmsg' => $errmsg]);
        }

        catch(\Exception $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
    //同步公共模块和部门的信息
    public function sync()
    {
        try {
            $de = $this->department->all();
            $pub = $this->model->all();
            $index = "";
            $indexes = [];
            //更新或创建模型
            foreach ($de as $v) {
                $index = $v->index;
                array_push($indexes,$index);
                $this->mapping->UpdateOrCreate(
                    ['name' => $v->name],
                    //['name' => $v->name, 'sid' => $v->id, 'attr' => 'protected', 'model' => 'app\\department','index' => $index]
                    ['name' => $v->name, 'sid' => $v->id, 'attr' => 'protected', 'model' => 'app\\department']
                );
            }

            $index = max($indexes);

            foreach ($pub as $v) {
                $this->mapping->UpdateOrCreate(
                    ['name' => $v->module_name],
                    //['name' => $v->module_name, 'sid' => $v->id, 'attr' => 'public', 'model' => 'app\\forummodule','index' => $v->id + $index]
                    ['name' => $v->module_name, 'sid' => $v->id, 'attr' => 'public', 'model' => 'app\\forummodule']
                );
            }

            return response(['status' => 'success', 'msg' => '操作成功']);
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
    //删除模块
    //不能删除部门
    public function del(Request $request)
    {
        DB::beginTransaction();

        try {
            $row = $this->mapping->where(['id' => $request->id, 'attr' => 'public'])->first();

            if ($row) {

                $mFlag1 = $row->delete();
                $mFlag2 = $this->model->destroy($row->sid);

                if ($mFlag1 && $mFlag2) {
                    DB::commit();
                    return response(['status' => 'success'], 200);
                }

            }
            else {
                DB::rollback();
                return response(['status' => 'error', 'errmsg' => '模块不存在'],202);
            }
        }
        catch (QueryException $e) {
            DB::rollback();

            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }

    /**更新模块信息**/
    public function update(ForumModuleUpdateRequest $request)
    {
        DB::beginTransaction();

        try {
            $mapping = $this->mapping->find($request->id);

            if ($mapping) {
                $Flag = false;
                $module = $this->model->find($mapping->sid);
                $mapping->name = $request->name;
                $mapping->index = $request->index;
                $mFlag1 = $mapping->save();

                if ($module) {
                    $module->module_name = $request->name;
                    $mFlag2 = $module->save();

                    if ($mFlag1 && $mFlag2) {
                        $Flag = true;
                    }
                }
                else if ($mFlag1) {
                    $Flag = true;
                }


                if ($Flag) {
                    DB::commit();

                    return response(['status' => 'success'], 200);
                }
            }
            else {
                DB::rollback();

                return response(['status' => 'error', 'errmsg' => '目标不存在'], 202);
            }
        }
        catch (QueryException $e) {
            DB::rollback();

            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }


}

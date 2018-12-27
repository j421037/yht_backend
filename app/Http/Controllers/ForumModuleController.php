<?php
/**论坛公共版块**/
namespace App\Http\Controllers;

use App\Department;
use App\ForumModuleMappingDepartment;
use App\ForumModule as FModule;
use Illuminate\Http\Request;
use App\Http\Resources\ForumModuleResource;
use App\Http\Requests\ForumModuleRequestStore;
use Illuminate\Database\QueryException;

class ForumModuleController extends Controller
{
    protected $model;
    protected $mapping;
    protected $department;

    public function __construct(FModule $model,ForumModuleMappingDepartment $mapping, Department $department)
    {
        $this->model = $model;
        $this->mapping = $mapping;
        $this->department = $department;
    }

    //公共模块
    public function index()
    {
        return response(['data' =>ForumModuleResource::collection($this->model->all()), 'status' => 'success'], 200);
    }
    //新增公共模块
    public function store(ForumModuleRequestStore $request)
    {
        try {
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
                    ['name' => $v->name, 'sid' => $v->id, 'attr' => 'protected', 'model' => 'app\\department','index' => $index]
                );
            }

            $index = max($indexes);

            foreach ($pub as $v) {
                $this->mapping->UpdateOrCreate(
                    ['name' => $v->module_name],
                    ['name' => $v->module_name, 'sid' => $v->id, 'attr' => 'public', 'model' => 'app\\forummodule','index' => $v->id + $index]
                );
            }

            return response(['status' => 'success', 'msg' => '操作成功']);
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
}

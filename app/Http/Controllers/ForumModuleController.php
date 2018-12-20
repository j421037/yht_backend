<?php
/**论坛公共版块**/
namespace App\Http\Controllers;


use App\ForumModule as FModule;
use Illuminate\Http\Request;
use App\Http\Resources\ForumModuleResource;
use App\Http\Requests\ForumModuleRequestStore;
use Illuminate\Database\QueryException;

class ForumModuleController extends Controller
{
    protected $model;

    public function __construct(FModule $model)
    {
        $this->model = $model;
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
}

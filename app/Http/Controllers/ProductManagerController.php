<?php
/**
 * @author  wangxin
 * @date 2019.4.9
 * 价格表管理
 */

namespace App\Http\Controllers;

use App\Brand;
use App\ProductsManager;
use App\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProductManagerStoreRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\ProductManagerListResource;

class ProductManagerController extends Controller
{
    private $model;
    private $brand;
    private $category;

    public function __construct(ProductsManager $model, ProductCategory $category, Brand $brand)
    {
        $this->model = $model;
        $this->brand = $brand;
        $this->category = $category;
    }

    /**
     * store
     * @param request
     * @return json
     */
    public function store(ProductManagerStoreRequest $request)
    {
        try {
            $category = $this->category->find($request->category);
            $brand = $this->brand->find($request->brand);

            if (!$category)
                throw new \Exception("分类不存在");
            if (!$brand)
                throw new \Exception("品牌不存在");

            $table = strtolower("P_".$category->abbr."_".$request->table)."_prices";

            if (Schema::hasTable($table))
                throw new \Exception("该价格表已经存在");

            $sql = "CREATE TABLE IF NOT EXISTS `{$table}` ( `id` int auto_increment primary key ,`price` decimal(20,2) not null, `created_at` int not null comment '创建时间戳'  ";

            foreach ($request->attribute as $v)
            {
                $sql .= " , `".strtolower($v['field'])."` VARCHAR(191) not null comment '".$v['description']."' ";
            }

            $sql .= ") CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";

            $this->model->create([
                "category_id" => $request->category,
                "brand_id" => $request->brand,
                "brand_name"  => $brand->name,
                "table" =>$table,
                "method" => $request->post("method"),
                "columns" => json_encode($request->attribute)
            ]);
            DB::statement($sql);

            return response(["status" => "success"], 201);
        }
        catch (QueryException $exception)
        {
            //唯一约束错误码 1062
            if ($exception->errorInfo[1] == 1062)
                $errmsg = "价格表已经存在";
            else
                $errmsg = $exception->getMessage();
            return response(["status" => "error","errmsg" => $errmsg,], 200);
        }
        catch (\Exception $exception)
        {
            return response(["status" => "error", "errmsg" => $exception->getMessage()]);
        }
    }

    /**
     * 每个产品分类下的价格表信息
     */
    public function PriceTableList(Request $request)
    {
        if (!$request->category)
            return response(["status" => "error","errmsg" => "参数不正确"], 200);
        $data = $this->model->where(["category_id" => $request->category])->get();

        return response(["status" => "success", "data"=> ProductManagerListResource::collection($data)], 200);
    }

    /**
     *
     * drop price table
     *
     * @param $id table ids
     */
    public function PriceTableDelete(Request $request)
    {
        $tables = $this->model->whereIn("id",$request->id)->get();

        try {

            foreach ($tables as $v) {
                if ($v->delete())
                    DB::statement("DROP TABLE {$v->table}");
            }

            return response(["status" => "success"], 200);
        }
        catch (QueryException $e)
        {
            return response(["status" => "error", "errmsg" => $e->getMessage()], 200);
        }
    }

}

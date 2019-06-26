<?php
/**
 * @author wangxin
 * @date 2019.4.9
 * 产品分类
 */
namespace App\Http\Controllers;

use Overtrue\Pinyin\Pinyin;
use App\ProductCategory;
use Illuminate\Http\Request;
use App\Http\Requests\ProductCategoryStoreRequest;
use App\Http\Resources\ProductCategoryListResource;

class ProductCategoryController extends Controller
{
    private $category;
    private $pinyin;

    public function __construct(ProductCategory $category, Pinyin $p)
    {
        $this->category = $category;
        $this->pinyin = $p;
    }

    //
    public function store(ProductCategoryStoreRequest $request)
    {
        $result = $this->category->create(["creator" => $this->getUserId(),"name" => $request->name,"abbr" => $this->pinyin->abbr($request->name)]);

        if ($request)
        {
            return response(["status" => "success"], 201);
        }

        return response(["status" => "error", "errmsg" => "分类创建失败"], 200);
    }

    public function CategoryList(Request $request)
    {
        return response(['status' => "success", "data" => ProductCategoryListResource::collection($this->category->with(['childrens'])->get())], 200);
    }

    public function update(ProductCategoryStoreRequest $request)
    {
        $category = $this->category->find($request->id);

        if (!$category)
            return response(["status" => "error", "errmsg" => "目标不存在"], 200);
        $category->name = $request->name;
        $category->save();

        return response(["status" => "success"], 201);
    }
}

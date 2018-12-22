<?php
/**
 * 论坛入口文章推荐类
 * @author wangxin
 * @date 2018-12-22
 */
namespace App\Http\Controllers;

use App\Article;
use Illuminate\Http\Request;
use App\Http\Resources\ArticlePortalResource;

class ArticlePortalController extends Controller
{
    private $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    public function index(Request $request)
    {
        $limit = intVal($request->pagesize);
        $offset = (intval($request->pagenow) - 1 ) * $limit;

        $model = $this->article
                ->with(['ArticleData'])
                ->where(['status' => 1, 'attr' => 'public'])
                ->offset($offset)
                ->limit($limit)
                ->orderBy('id', 'desc');

        $list = $model->get();
        $count = count($list);

        return response(['status' => 'success','data' => ArticlePortalResource::collection($list),'loaded' => !(bool) $count], 200);
    }
}

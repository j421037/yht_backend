<?php
/**
* 文章加精设置类
*/
namespace App\Http\Controllers;

use App\ArticleData;
use Illuminate\Http\Request;

class ArticleFineController extends Controller
{
    
    public function fine(Request $request)
    {

    	try {

    		$article = ArticleData::where(['article_id' => $request->article_id])->first();
    		$article->isFine = $request->status;

    		if ($article->save()) {
    			return response(['status' => 'success'], 200);
    		}

    	} catch(\Illuminate\Database\QueryException $e) {

            return response(['status' => 'error', 'error'=>$e->getMessage()], 200);
        }
    }
}

<?php
/**
* 枚举功能管理类
*@author 王
*@date 2018-11-01
*/
namespace App\Http\Controllers;

use Auth;
use App\Enumberate;
use App\EnumberateItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Http\Resources\EnumberateResource;

class EnumberateController extends Controller
{
    public function store(Request $request)
    {
    	if ($request->name != '' && $request->item != '') {

            try {

        		DB::beginTransaction();

        		$data = ['name' => trim($request->name), 'user_id' => Auth::user()->id];

        		$list = [];
                
                // $param = json_decode($request->item, true);
                $param = $request->item;
               
                $f1 = Enumberate::create($data);

        		foreach ($param as $k => $v) {
        			$item = [];

        			$item['name'] = trim($v['name']);
        			$item['value'] = trim($v['value']);
        			$item['index'] = trim($v['index']);
        			$item['disable'] = intVal($v['disable']);
                    $item['eid'] = $f1->id;
                    $item['created_at'] = date('Y-m-d H:i:s', time());
                    $item['updated_at'] = date('Y-m-d H:i:s', time());
        			array_push($list, $item); 
        		}

        		$f2 = EnumberateItem::insert($list);


        		if ($f1 && $f2) {
                    DB::commit();
        		    return response(['status' => 'success']);  
        		} else {
                    DB::rollback();
                    return response(['status' => 'error']);
                }

            } catch(QueryException $e) {
                DB::rollback();
                return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
            }

    	}
        else {
            return response(['status' => 'error', 'errmsg' => '参数有误']);
        }
    }

    public function update(Request $request)
    {
        // return $request;
        if ($request->name && $request->id && $request->item) {
            
            try{
                DB::beginTransaction();
                $item = collect($request->item);
                $Enumberate = Enumberate::find($request->id);
                $items = EnumberateItem::where(['eid' => $Enumberate->id])->get();
                $Enumberate->name = $request->name;
                $f1 = $Enumberate->save();
                $f2 = true;
                
                foreach($item as $k => $v) { 
                    $v = (object) $v;

                    foreach ($items as $kk => $vv) {
                        //更新
                        if (isset($v->id) && $v->id == $vv->id) {
                            $vv->name = $v->name;
                            $vv->value = $v->value;
                            $vv->index = $v->index;
                            $vv->disable = $v->disable;

                            if ($vv->save()) {
                                unset($item[$k]);
                                unset($items[$kk]);
                            } 
                            else {
                                $f2 = false;
                                break;
                            }
                        } 
                        
                    }
                }

                //删除
                if (count($items) > 0) {
                    foreach ($items as $v) {
                        $v->delete();
                    }
                }

                //增加
                if (count($item) > 0) {
                    foreach ($item as $v ) {
                        $obj = (object) $v;
                        $newItem = new EnumberateItem();

                        $newItem->eid = $Enumberate->id;
                        $newItem->name = $obj->name;
                        $newItem->value = $obj->value;
                        $newItem->index = $obj->index;
                        $newItem->disable = $obj->disable;

                        if (!$newItem->save()) {
                            $f2 = false;
                            break;
                        }
                        unset($newItem);
                    }
                }

                if ($f1 && $f2) {
                    DB::commit();
                    return response(['status' => 'success'], 200);
                } 
            } catch (QueryException $e) {
                return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
            }
           
        }
    }

    public function all() 
    {
        $list = Enumberate::with(['item'])->get();

        return response(EnumberateResource::collection($list));
    }
}

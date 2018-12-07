<?php
/**
 * 潜在客户类
 */
namespace App\Http\Controllers;

use App\PotentialCustomer;
use Illuminate\Http\Request;
use App\Http\Resources\PotentialListResource;

class PotentialCustomerController extends Controller
{
    public function all(Request $request)
    {
        $list = [];
        $index = 1;
        $AuthList = $this->AuthIdList();

        $model = PotentialCustomer::with(['project' => function($query)  use ($AuthList){
            $query->whereIn('user_id', $AuthList);
        }]);

        $total = $model->count();
        $data = $model->orderBy('id', 'desc')->limit($request->limit)->offset($request->offset)->get();

        foreach ($data as $k => $v) {
            $item = new \StdClass;
            $item->index = $index;
            $item->name = $v->name;
            $item->user_id = $v->user_id;
            $item->nameshow = true;
            $item->project = null;
            $item->tid = null;
            $item->tag = null;
            $item->pid = null;
            $item->estimate = null;
            $flag = true;

            foreach ($v->project as $pk => $pv) {
                $project_name = $pv->name;
                unset($pv->name);

                if ($flag) {
                    $item->project = $pv->name;
                    $item->tid = $pv->tid;
                    $item->tag = $pv->tag;
                    $item->pid = $pv->id;
                    $item->estimate =$pv->estimate;
                    $flag = false;
                }
                else {
                    $item = (object) $pv;
                    $item->nameshow = false;
                    $item->name = $v->name;
                }

                $item->project = $project_name;
                array_push($list, $item);
                ++$index;
            }

            if ($flag == true) {
                array_push($list, $item);
                ++$index;
            }

        }

        $list = collect($list);

        return response(['status' => 'success', 'row' => PotentialListResource::collection($list), 'total' => $total]);
    }
}

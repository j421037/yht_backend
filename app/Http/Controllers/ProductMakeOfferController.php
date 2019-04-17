<?php

namespace App\Http\Controllers;

use App\User;
use App\RealCustomer;
use App\ProductCategory;
use App\GeneralOffer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use App\Http\Requests\OfferListRequest;
use App\Http\Resources\OfferListResource;
use App\Http\Resources\MakeOfferParamsResource;
use App\Http\Requests\ProductMakeOfferStoreRequest;

class ProductMakeOfferController extends Controller
{
    private $db;
    private $category;
    private $user;
    private $realCust;
    private $offers;

    public function __construct(DatabaseManager $db, ProductCategory $category, GeneralOffer $offers,User $user, RealCustomer $realCust)
    {
        $this->db = $db;
        $this->user=  $user;
        $this->offers = $offers;
        $this->category = $category;
        $this->realCust = $realCust;
    }

    /**
     * makeoffer params
     * @return  [category:[table1,table2]]
     */
    public function params(Request $request)
    {
        $data = $this->category->with(['childrens'])->get();
        $db = $this->db;

        $data->map(function(&$items) use (&$db) {
            $items->childrens->map(function(&$item) use ($db) {
                $collect = collect(json_decode($item->columns,true));
                $fields = $collect->pluck("field")->toArray();
                $fieldMap = [];
                $collect->map(function($f) use (&$fieldMap) {
                    $fieldMap[$f["field"]] = $f["description"];
                });

                $item->products = $db->table($item->table)->select($fields)->groupBy($fields)->get();
                $item->field_map = $fieldMap;
            });
        });

        return response(["staus" => "success","data" => MakeOfferParamsResource::collection($data)], 200);
    }

    /**
     * store offer
     */
    public function store(ProductMakeOfferStoreRequest $request)
    {
        try {
            $model = $this->offers->newInstance($request->all());
            $model->products = json_encode($model->products);
            $model->serviceor = $this->user->find($model->serviceor_id)->name;
            $model->creator = $this->getUser()->name;
            $model->creator_id = $this->getUserId();
            $model->customer = $this->realCust->find($model->customer_id)->name;

            if ($model->save())
            {
                return response(["status" => "success"],200);
            }
        }
        catch (QueryException $e)
        {
            return response(["status" => "error","errmsg" => $e->getMessage()], 200);
        }
    }

    /**
     * all offer
     */
    public function OfferList(OfferListRequest $request)
    {
        $ids = $this->AuthIdList();
        $rows = $this->offers->whereIn("creator_id",$ids)->orWhereIn("serviceor_id", $ids)->get();

        return response(["status" => "success", "data" => OfferListResource::collection($rows)],200);
    }
}

<?php

namespace App\Http\Controllers;


use App\User;
use App\RealCustomer;
use App\ProductCategory;
use App\GeneralOffer;
use App\ProductsManager;
use Maatwebsite\Excel\Excel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Database\DatabaseManager;
use App\Http\Requests\OfferListRequest;
use App\Http\Resources\OfferListResource;
use Barryvdh\Snappy\PdfWrapper;
use App\Http\Controllers\Traits\CostModule;
use App\Http\Resources\MakeOfferParamsResource;
use App\Http\Requests\ProductMakeOfferStoreRequest;
use App\Http\Requests\ProductMakeOfferModifyRequest;
use App\Http\Requests\ProductMakeOfferDownloadRequest;

class ProductMakeOfferController extends Controller
{
    use CostModule;

    private $db;
    private $category;
    private $user;
    private $realCust;
    private $offers;
    private $excel;
    private $manager;
    private $pdf;

    public function __construct(
        User $user,
        DatabaseManager $db,
        ProductCategory $category,
        GeneralOffer $offers,
        ProductsManager $manager,
        RealCustomer $realCust,
        Excel $excel,
        PdfWrapper $pdf
    )
    {
        $this->db = $db;
        $this->user=  $user;
        $this->offers = $offers;
        $this->category = $category;
        $this->realCust = $realCust;
        $this->excel = $excel;
        $this->pdf = $pdf;
        $this->manager = $manager;
    }

    /**
     * download the pdf
     */
    public function DownloadPDF(ProductMakeOfferDownloadRequest $request)
    {
        return $this->pdf->loadView("MakeOffer",$this->MakePDF($request->offer_id))->download("offer.pdf");
    }

    /**
     * view the pdf
     */
    public function ViewPDF(ProductMakeOfferDownloadRequest $request)
    {
        return view("MakeOffer",$this->MakePDF($request->offer_id));
    }
    /**
     * PDF
     */
    private function MakePDF( int $offer_id) :array
    {
        $offer = $this->offers->find($offer_id);
        $manager = $this->manager->find($offer->product_brand_id);
        $offer->brand_name = $manager->brand_name;
        $offer->category_name = $this->category->find($manager->category_id)->name;
        $columns = json_decode($manager->columns);
        $fields = new \StdClass;

        foreach ($columns as $k => $v)
        {
            $fields->{$v->field} = $v->description;
        }

        if ($manager->method == 0)
        {
            $offer->unit = "条";
            /**
             *百分比打折
             */
            $offer->operate_val = bcdiv($offer->operate_val, 100,2);
        }
        else {
            $offer->unit = "吨";
        }

        $offer->rows = $this->Calculation($manager,$offer->operate, $offer->operate_val);

        return ["offers" => $offer, "fields" => $fields];
    }

    /**
     * Calculation price
     */
    private function Calculation(ProductsManager $manager,string $operate, float $val)
    {
        $bcName = $this->MakeOperatorName($operate);
        $rows = $this->getPriceData($this->db, $manager);
        array_walk($rows, function(&$item) use ($bcName,$val) {
            $item->price = $bcName($val,$item->price,2);
        });

        return collect($rows);
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
     * update offer
     **/
    public function modify(ProductMakeOfferModifyRequest $request)
    {
        $offer = $this->offers->find($request->id);
        $offer->operate = $request->operate;
        $offer->operate_val = $request->operate_val;

        try {
            if ($offer->save())
                return response(["status" => "success"],200);
        }
        catch (QueryException $e) {
            return response(["status" => "error" ,"errmsg" => "更新失败"], 200);
        }
    }

    /**
     * all offer
     */
    public function OfferList(OfferListRequest $request)
    {
        $ids = $this->AuthIdList();
        $rows = $this->offers->whereIn("creator_id",$ids)->orWhereIn("serviceor_id", $ids)->orderBy("id","desc")->get();

        return response(["status" => "success", "data" => OfferListResource::collection($rows)],200);
    }

    /**
     * offer operate name
     */
    protected function MakeOperatorName($num) :string
    {
        switch ($num) {
            case 1: //x
                $bcName = "bcmul";
                break;
            case 2:// /
                $bcName = "bcdiv";
                break;
            case 3://+
                $bcName = "bcadd";
                break;
            case 4: // -
                $bcName = "bcsub";
                break;
            default:
                $bcName = "bcadd";
        }

        return $bcName;
    }
}

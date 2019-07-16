<?php

namespace App\Http\Controllers;


use App\User;
use App\RealCustomer;
use App\ProductCategory;
use App\GeneralOffer;
use App\ProductsManager;
use App\PriceVersion;
use App\MakeOfferFormula;
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
    private $priceVersion;
    private $formula;
    private $operates = [
        ["label" => "+","value" => "+", "type" => "operator"],
        ["label" => "-","value" => "-", "type" => "operator"],
        ["label" => "*","value" => "*", "type" => "operator"],
        ["label" => "/","value" => "/", "type" => "operator"],
        ["label" => "(","value" => "(", "type" => "bracket"],
        ["label" => ")","value" => ")",  "type" => "bracket"],
    ];

    private $op = ["+","-","*", "/"];

    public function __construct(
        User $user,
        DatabaseManager $db,
        ProductCategory $category,
        GeneralOffer $offers,
        ProductsManager $manager,
        PriceVersion $version,
        RealCustomer $realCust,
        MakeOfferFormula $makeOfferFormula,
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
        $this->priceVersion = $version;
        $this->formula = $makeOfferFormula;
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
        $formula = $this->formula->find($offer->formula_id);
        $fields = new \StdClass;

        $columns = collect($columns)->sortBy("index")->all();

        foreach ($columns as $k => $v)
        {
            $fields->{$v->field} = $v->description;
        }


        $offer->rows = $this->Calculation($manager,$offer,$formula->formula_parse);

        return ["offers" => $offer, "fields" => $fields];
    }



    /**
     * 1、递归解括号
     * 2、得到正确的运算顺序
     * 3、 遵循先乘除后加减的运算方式进行递归运算
     */
    private function ParseBracket(Array $params,int $level = 0)
    {
        static $result = [];
        $bracketValues = [];
        $bracketStart = -1;
        $bracketEnd = -1;
        $bracketStack = 0;
        $paramIndex = -1;
        $temp = [];

        foreach ($params as $key => $param) {

            if ($param == "(") {

                ++$bracketStack;

                if ($bracketStart < 0) {
                    $bracketStart = $key;
                    continue;
                }
            }

            if ( $bracketStack != 0) {

                array_push($temp, $param);

                if ($param == ")") {
                    if (--$bracketStack == 0) {
                        array_pop($temp); // 栈中最后一个括号不需要
                        ++$paramIndex; //递增索引
                        $bracketValues[$paramIndex] = []; //初始化新的数组
                        $bracketValues[$paramIndex]["param"] = $temp;
                        $bracketStart = -1;
                        $temp = [];
                    }
                }
            }
            else {

                if ($key <= count($params) - 1) {
                    ++$paramIndex;
                    $bracketValues[$paramIndex] = [];
                }
                $bracketValues[$paramIndex]["param"] = $param;
            }
        }

        foreach ($bracketValues as &$value) {
            if (is_array($value["param"])) {
                if (in_array(")",$value["param"])) {
                    //确保运算优先级
                    $value = $this->ParseBracket($value["param"],  $level + 1);
                }
            }

            while(is_array($value) && count($value) > 1) {
                $value = $this->MakeFormual($value);
            }
        }

        if ($level == 0) {

            while (is_array($bracketValues) && count($bracketValues) > 1) {
                $bracketValues = $this->MakeFormual($bracketValues);
            }

            if (is_array($bracketValues) && count($bracketValues) == 1)
                $bracketValues = $bracketValues[0];
        }

        return $bracketValues;
    }

    private function MakeFormual(array $params, $level = 0)
    {
        $bcName = "";

        if ( count($params) != count($params, 1)) {

            foreach ($params as $k => &$value) {

                while (is_array($value)) {
                    $value = $this->MakeFormual($value, $level + 1);
                }
            }
        }

        if (count($params) > 1) {
            $op = "/";

            $pos = array_search($op, $params);

            if ($pos === false) {
                $op = "*";
                $pos = array_search($op, $params);
            }

            if ($pos === false) {
                $op = "+";
                $pos = array_search($op, $params);
            }

            if ($pos === false) {
                $op = "-";
                $pos = array_search($op, $params);
            }


            if ($pos !== false) {
                $l = $pos - 1;
                $r = $pos + 1;
                $leftParam = $params[$l];
                $rightParam = $params[$r];
                unset($params[$l]);
                unset($params[$pos]);
                unset($params[$r]);

                switch($op) {
                    case "+":
                        $bcName = "bcadd";
                        break;
                    case "-":
                        $bcName = "bcsub";
                        break;
                    case "*":
                        $bcName = "bcmul";
                        break;
                    case "/":
                        $bcName = "bcdiv";
                        break;
                }

                $f = sprintf("%s(%s,%s,2)",$bcName, $leftParam, $rightParam);

                if ($l === 0 ) {
                    if (count($params) > 0)
                        array_unshift($params, ["param" => $f]);

                    else {
                        return $f;
                    }
                }
                else {
                    array_push($params, ["param" => $f]);
                }

                return array_values($params);
            }
        }
        else {
            return $params["param"];
        }

    }
    /**
     * Calculation price
     */
    private function Calculation(ProductsManager $manager,GeneralOffer $offer ,string $formula)
    {
        $rows = $this->getPriceData($this->db, $manager, null, explode(",", $offer->price_id));

        $columns = json_decode($manager->columns, true);

        foreach ($columns as $column) {

            if (strpos($formula, $column["field"]) > -1) {

                $formula = str_replace($column["field"], sprintf('$item->%s', $column["field"]), $formula);

            }
        }

        array_walk($rows, function(&$item) use ($formula) {
            $item->price = eval("return {$formula};");
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
                $fieldParam = [];

                $collect->map(function($f) use (&$fieldMap, &$fieldParam) {
                    $fieldMap[$f["field"]] = $f["description"];
                    array_push($fieldParam, ["label" => $f["description"], "value" => $f["field"], "type" => "field"]);
                });

                $where = [];
                $version = $this->priceVersion->where(["product_brand" => $item->id])->orderBy("id", "desc")->first();

                if ($version)
                    $where = ["version" => $version->id];

                $item->products = $db->table($item->table)->select($fields)->where($where)->get();
                $item->field_map = $fieldMap;
                $param = $this->operates;

                $item->formula_param = array_merge($param, $fieldParam);
            });
        });

        return response(["staus" => "success","data" => MakeOfferParamsResource::collection($data)], 200);
    }

    public function SignParam($tableId)
    {
        $table = $this->manager->find($tableId);
        $columns = json_decode($table->columns);
        $fields = [];

        foreach ($columns as $column) {
            array_push($fields, ["label" => $column->description, "value" => $column->field,"type" => "field"]);
        }

        return response(["status" => "success", "data" => array_merge($this->operates, $fields)], 200);
    }

    /**
     * store offer
     */
    public function store(ProductMakeOfferStoreRequest $request)
    {
        try {
            $model = $this->offers->newInstance($request->all());
            $model->price_id = implode(",", $request->products);
            $model->serviceor = $this->user->find($model->serviceor_id)->name;
            $model->creator = $this->getUser()->name;
            $model->creator_id = $this->getUserId();
            $model->customer = $this->realCust->find($model->customer_id)->name;
            $model->formula_id = $request->formula_id;

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
        $rows = $this->offers->whereIn("creator_id",$ids)->orWhereIn("serviceor_id", $ids)->orderBy("id","desc")->with("formula")->get();

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

    /**
     * 添加计算公式
     */
    public function AppendFormula(Request $request)
    {
        $params = $request->params;
        $fields = $this->getFields($request->tableId);
        $result = $this->checkFormula($params, $fields);

        if ($result["result"] != true) {
            return response(["status" => "error", "errmsg" => $result["errmsg"]], 200);
        }

        $formula = $this->formula->newInstance();
        $string = "";

        foreach ($params as $param) {
            $string .= $param["value"];
        }

        $formula->formula = $string;
        $formula->table_id = $request->tableId;
        $formula->source = json_encode($params);
        $formula->user_id = $this->getUserId();

        $params = collect(json_decode($formula->source));
        $paramArr = [];

        $params->map(function($item) use (&$paramArr) {
            array_push($paramArr, $item->value);
        });
        $formula->formula_parse = $this->ParseBracket($paramArr);

        //替换字段
        foreach ($fields as $field) {
            if (strpos($string, $field["field"])) {
                $string = str_replace($field["field"], $field["description"], $string);
            }
        }

        $formula->label = $string;

        if ($formula->save()) {
            return response(["id" => $formula->id, "formula" => $formula->label,"status" => "success"], 201);
        }

    }

    public function UpdateFormula($id, Request $request)
    {

    }

    public function LoadFormula($id)
    {
        $formula = $this->formula->find($id);

        return response(["status" => "success", "data" => json_decode($formula->source)],200);
    }
    private function checkFormula($params, $fields) : array
    {
        $lastParam = [];
        $errmsg = null;
        $result = true;
        $bracketStack = 0; //括号的栈
        $operates = $this->getOpType($fields);
        $allOpStatus = true;

        foreach ($params as $param) {
            $count = 0;
            $param = (object) $param;
            if (is_array($lastParam))
                $count = count($lastParam);
            if (is_object($lastParam))
                $count = count(get_object_vars($lastParam));

            if ($param->type == "field" || $param->type == "numeric")
                $allOpStatus = false;

            if ($param->type == "numeric") {
                $paramType = "numeric";
                if (!$param->value) {
                    $errmsg = "输入内容不能为空";
                    $result = false;
                    break;
                }

                if ($count > 0) {
                    if ($lastParam->type == "numeric") {
                        $errmsg = "不能连续设置两个数字";
                        $result = false;
                        break;
                    }

                    if ($lastParam->value == ")") {
                        $errmsg = "右括号后面不能设置数字";
                        $result = false;
                        break;
                    }

                    if ($lastParam->type == "field") {
                        $errmsg = "字段后面不能设置数字";
                        $result = false;
                        break;
                    }
                }
            }
            else
                $paramType = $operates[$param->value];

            if (!$paramType) {
                $errmsg = "非法的公式值";
                $result = false;
                break;
            }


            if ($param->value == "(")
                ++$bracketStack;
            if ($param->value == ")")
                --$bracketStack;



            if ($count > 0) {

                if ($paramType == "operator" && $lastParam->type == "operator") {
                    $errmsg = "不能连续使用两个操作符";
                    $result = false;
                    break;
                }

                if ($paramType == "field" && $lastParam->type == "field") {
                    $errmsg = "不能连续使用两个字段";
                    $result = false;
                    break;
                }

                if ($lastParam->value == ")" && $param->type == "field") {
                    $errmsg = '操作符后面不能接括号 ")"';
                    $result = false;
                    break;
                }

                if ($lastParam->value == "(" && $paramType == "operator") {
                    $errmsg = "请检查公式的合法性";
                    $result = false;
                    break;
                }

                if ($param->value == "(" && $lastParam->value == ")") {
                    $errmsg = "不能连续使用两个括号";
                    $result = false;
                    break;
                }

                if ($lastParam->value == ")" && $paramType == "field") {
                    $errmsg = "右括号后不能连接字段";
                    $result = false;
                    break;
                }

                if ($lastParam->type =="numeric" && $paramType == "field") {
                    $errmsg = "输入内容后不能连接字段";
                    $result = false;
                    break;
                }
            }
            else {
                if ($paramType == "operator") {
                    $errmsg = "公式不能以操作符号开始";
                    $result = false;
                    break;
                }
            }

            $lastParam = $param;
        }

        if ($result && $bracketStack != 0) {
            $errmsg = "括号数量不正确";
            $result = false;
        }

        if ($allOpStatus) {
            $errmsg = "公式中不能全是运算符";
            $result = false;
        }

        return ["errmsg" => $errmsg, "result" => $result];
    }

    private function getOpType($fields) :array
    {
        $op = [];

        foreach ($this->operates as $operate) {
            $op[$operate["value"]] = $operate["type"];
        }

        foreach ($fields as $field) {
            $op[$field["field"]] = "field";
        }

        return $op;
    }

    private function getFields($tableId)
    {
        $table = $this->manager->find($tableId);
        return json_decode($table->columns, true);
    }
}

<?php

namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\BindAttr;
use App\EnumberateItem;
use App\Department;
use App\RealCustomer;
use App\FilterProgram;
use Illuminate\Http\Request;
use App\Http\Resources\FilterProgramResource;
use App\Http\Resources\ARSumFilterQueryResource;

class ARSumFilterController extends Controller
{
    protected $operator = array(
        ["label" => '等于', "value" => 0],
        ["label" => '不等于',"value" => 1],
        ["label" => '大于',"value" => 2],
        ["label" => '大于等于',"value" => 3],
        ["label" => '小于',"value" => 4],
        ["label" => '小于等于',"value" => 5],
        ["label" => '为空',"value" => 6],
        ["label" => '不为空',"value" => 7],
        ["label" => '包含', "value"   => 8],
        ["label" => "不包含", "value" => 9]
    );

    protected $operatorMap = array(
        '=', '<>','>','>=','<','<=', "is null", "is not null", "like", "not like"
    );

    protected $logic = array(['label' => "并且", 'value' => 1],['label' => '或者', 'value' => 2]);

    protected $logicMap = array('AND', 'OR');

    /**应收表头过滤基础信息
     * @return [label => 显示名称, value => 对应的标识名, type=>字段类型];
     * 字段的类型分为3种： server => 来自服务端检索, input => 用户自定义输入, enumerate => 固定的枚举
     */
    public function ARSumFilterTable(Request $request)
    {

        return response(['field' => $this->field(),'operator' => $this->operator, 'logic' => $this->logic,'program' => $this->PersonalFilterProgram(true)], 200);
    }

    /**字段检索**/
    public function FieldQuery(Request $request)
    {
        $result = [];

        switch ($request->field) {
            case 'cust_id' :
                $result = ARSumFilterQueryResource::collection(RealCustomer::where('name','like','%'.trim($request->keyword).'%')->get());
                break;
            case 'pid' :
                $result = ARSumFilterQueryResource::collection(Project::where('name', 'like', '%'.trim($request->keyword).'%')->get());
                break;
            default:
                $result = [];
                break;
        }

        return response($result, 200);
    }

    //字段信息
    protected function field()
    {
        return [
            array(
                'label' => '客户名称',
                'value' => 'cust_id',
                'type' => 'server'
            ),
            array(
                'label' => '客户状态',
                'value' => 'status',
                'type'  => 'enumerate',
                'list'  => ARSumFilterQueryResource::collection($this->GetEnumberItem('F_CMK_CUSTATUS'))
            ),
            array(
                'label' => '项目名称',
                'value' => 'pid',
                'type' => 'server'
            ),
            array(
                'label' => '部门名称',
                'value' => 'department_id',
                'type'  => 'enumerate',
                'list'  => ARSumFilterQueryResource::collection(Department::all()),
            ),
            array(
                'label' => '业务员',
                'value' => 'user_id',
                'type'  => 'enumerate',
                'list'  => ARSumFilterQueryResource::collection(User::all()),
            ),
            array(
                'label' => '施工范围',
                'value' => 'build',
                'type'  => 'enumerate',
                'list'  => ARSumFilterQueryResource::collection($this->GetEnumberItem('F_CMK_PROATTR'))
            ),
            array(
                'label' => '项目标签',
                'value' => 'protag',
                'type'  => 'enumberate',
                'list'  => ARSumFilterQueryResource::collection($this->GetEnumberItem('F_CMK_CUSTAG'))
            ),
            array(
                'label' => '合作金额',
                'value' => 'cooperation_amountfor',
                'type'  => 'input'
            ),
            array(
                'label' => '客户类型',
                'value' => 'cust_type',
                'type'  => 'enumerate',
                'list'  => array(['label' => '合作客户', 'value' => 1],['label' => '目标客户', 'value' => 2])
            ),
            array(
                'label' => '税率',
                'value' => 'tax',
                'type' => "input"
            ),
            array(
                'label' => '挂靠',
                'value' => 'affiliate',
                'type'  => "enumerate",
                'list'  => array(['label' => '有', 'value' => 1], ['label' => '无', 'value' => 0])
            ),
            array(
                'label' => '合同',
                'value' => 'agreement',
                'type'  => 'enumerate',
                'list'  => array(['label' => '有', 'value' => 1], ['label' => '无', 'value' => 0])
            )
        ];
    }

    /**返回绑定属性信息
     * @param $AttrName 属性名称
     */
    protected function GetEnumberItem($AttrKey)
    {
        return EnumberateItem::where(['eid' => BindAttr::where(['key' => $AttrKey])->first()->id])->get();
    }

    /**返回过滤方案
     * @param $flag 标识
     * 默认false 返回 response响应  当
     * 为true时返回对象信息
     */
    public function PersonalFilterProgram($flag = false)
    {
        $program = FilterProgram::where(['user_id' => $this->getUserId()])->get();

        foreach($program as $v) {
            $v->conf = json_decode($v->conf, true);
        }

        if ($flag) {
            return $program;
        }

        return response( FilterProgramResource::collection($program), 200);
    }
}

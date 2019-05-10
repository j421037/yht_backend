<?php

namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\BindAttr;
use App\Enumberate;
use App\EnumberateItem;
use App\Department;
use App\RealCustomer;
use App\FilterProgram;
use Illuminate\Http\Request;
use App\Http\Resources\FilterProgramResource;
use App\Http\Resources\ARSumFilterQueryResource;

class ARSumFilterController extends Controller
{


    /**应收表头过滤基础信息
     * @return [label => 显示名称, value => 对应的标识名, type=>字段类型];
     * 字段的类型分为3种： server => 来自服务端检索, input => 用户自定义输入, enumerate => 固定的枚举
     */
    public function ARSumFilterTable(Request $request)
    {

        return response(['field' => $this->FieldAndOperate(),'operator' => $this->operator, 'logic' => $this->logic,'program' => $this->PersonalFilterProgram(true)], 200);
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
    //在字段信息中加入操作符
    protected function FieldAndOperate()
    {
        $field = $this->field();

        foreach ($field as $k => $v) {
            switch ($v['attr']) {
                case 'string':
                    $field[$k]['opearate'] = $this->operatorString;
                    break;
                case 'number':
                    $field[$k]['opearate'] = $this->operatorNumber;
                    break;
                case 'enumerate':
                    $field[$k]['opearate'] = $this->operatorEnumeration;
                    break;
            }
        }

        return $field;
    }


    /**返回绑定属性信息
     * @param $AttrName 属性名称
     */
    protected function GetEnumberItem($AttrKey)
    {
        $list = EnumberateItem::where(['eid' => Enumberate::where(['id' => BindAttr::where(['key' => $AttrKey])->first()->eid])->first()->id])->get();
        $item = new \StdClass;
        $item->id = 0;
        $item->name = "全部";
        $list->push($item);

        return $list;
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
            $confList = json_decode($v->conf, true);
            $field = $this->field();
            //实现取值
            if (is_array($confList)) {
                array_walk($confList, function (&$item) use ($field) {

                    if ($item['type']['name'] == 'enumerate') {

                        foreach ($field as $ik => $iv) {
                            if ($iv['value'] == $item['field']) {
                                $item['type']['list'] = $iv['list'];
                            }
                        }
                    }

                    if ($item['type']['name'] == 'server') {

                        if ($item['field'] == 'cust_id' && !Empty($item['value'])) {
                            $item['remote'] = ARSumFilterQueryResource::collection(RealCustomer::where(['id' => $item['value']])->get());
                        }

                        if ($item['field'] == 'pid' && !Empty($item['value'])) {
                            $item['remote'] = ARSumFilterQueryResource::collection(Project::where(['id' => $item['value']])->get());
                        }
                    }
                });
            }

            $v->conf = $confList;
        }

        if ($flag) {
            return FilterProgramResource::collection($program);
        }

        return response( FilterProgramResource::collection($program), 200);
    }

    //字段信息
    protected function field()
    {
        return [
            array(
                'label' => '客户名称',
                'value' => 'customer_name',
                'type'  => 'server',
                'attr'  => 'string',
            ),
            array(
                'label' => '客户状态',
                'value' => 'status',
                'type'  => 'enumerate',
                'attr'  => 'enumerate',
                'list'  => ARSumFilterQueryResource::collection($this->GetEnumberItem('F_CMK_CUSTATUS')),
            ),
            array(
                'label' => '项目名称',
                'value' => 'project_name',
                'type' => 'server',
                'attr'  => 'string',
            ),
            array(
                'label' => '部门名称',
                'value' => 'department_id',
                'type'  => 'enumerate',
                'attr'  => 'enumerate',
                'list'  => ARSumFilterQueryResource::collection(Department::all()),
            ),
            array(
                'label' => '业务员',
                'value' => 'user_id',
                'type'  => 'enumerate',
                'attr'  => 'enumerate',
                'list'  => ARSumFilterQueryResource::collection(User::all()),
            ),
            array(
                'label' => '施工范围',
                'value' => 'work_scope',
                'type'  => 'enumerate',
                'attr'  => 'enumerate',
                'list'  => ARSumFilterQueryResource::collection($this->GetEnumberItem('F_CMK_PROATTR'))
            ),
            array(
                'label' => '标签',
                'value' => 'protag',
                'type'  => 'enumerate',
                'attr'  => 'enumerate',
                'list'  => ARSumFilterQueryResource::collection($this->GetEnumberItem('F_CMK_CUSTAG'))
            ),
            array(
                'label' => '合作金额',
                'value' => 'cooperation_amountfor',
                'type'  => 'input',
                'attr'  => 'number',
            ),
            array(
                'label' => '客户类型',
                'value' => 'cust_type',
                'type'  => 'enumerate',
                'attr'  => 'enumerate',
                'list'  => array(['label' => '合作客户', 'value' => 1],['label' => '目标客户', 'value' => 2])
            ),
            array(
                'label' => '税率',
                'value' => 'tax',
                'type'  => "input",
                'attr'  => 'number',
            ),
            array(
                'label' => '挂靠',
                'value' => 'affiliate',
                'type'  => "enumerate",
                'attr'  => 'enumerate',
                'list'  => array(['label' => '有', 'value' => 1], ['label' => '无', 'value' => 0])
            ),
            array(
                'label' => '合同',
                'value' => 'agreement',
                'type'  => 'enumerate',
                'attr'  => 'enumerate',
                'list'  => array(['label' => '有', 'value' => 1], ['label' => '无', 'value' => 0])
            )
        ];
    }
}

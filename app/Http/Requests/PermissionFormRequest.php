<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionFormRequest extends FormRequest
{
    public $ruleKeys = [];

    public $rule = [
        'id'            => '',
        'name'          =>  'required|string',
        'pid'           =>  'required',
        'node_type'     => 'required|string',
        'classname'     => '',
        'description'   => '',
        'backend_path'  => '',
        'front_path'    => '',
        'mobile_path'   => '',
        'mobile_sort'   => '',
        'pc_sort'       => ''
    ];

    public function __construct()
    {
        foreach($this->rule as $k => $v) {
            array_push($this->ruleKeys, $k);
        }
    }

    /**
    * 过滤多余的字段
    */
    public function onlyValue()
    {
        return array_only($this->all(), $this->ruleKeys);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->rule;
    }
}

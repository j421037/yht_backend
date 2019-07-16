<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocationFieldRequest extends FormRequest
{
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
        return [
            "sourceTableId" => "required",
            "targetTableId" => "required",
            "sourceField"   => "required"
        ];
    }
    public function messages()
    {
        return [
            "sourceTableId.required" => "参数：源对象不能为空",
            "targetTableId.required" => "参数：目标对象不能为空",
            "sourceField.required"   => "参数：目标字段不能为空"
        ];
    }
}

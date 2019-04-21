<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductMakeOfferModifyRequest extends FormRequest
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
            //
            "id"            => "required|numeric",
            "operate_val"   => "required",
            "operate"       => "required|numeric"
        ];
    }
    public function messages()
    {
        return [
            "id.required"   => "参数有误",
            "operate_val.required"   => "折扣数字不能为空",
            "operate.required"       => "操作符错误"
        ];
    }
}

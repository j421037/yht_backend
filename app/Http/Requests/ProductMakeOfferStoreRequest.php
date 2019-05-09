<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ProductMakeOfferStoreRequest extends FormRequest
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
            "serviceor_id"      => "required|numeric",
            "customer_id"       => "required|numeric",
            "operate"           => "required|numeric",
            "operate_val"       => "required|numeric",
            "product_brand_id"  => "required|numeric",
            "version_id"        => "required|numeric",
            "products"          => "required|array"
        ];
    }

    public function messages()
    {
        return [
            "serviceor_id.required" => "请选择服务人员",
            "serviceor_id.numeric"  => "非法的类型",
            "customer_id.required"  => "请选择客户",
            "operate.required"      => "请选择操作的方向",
            "operate_val.required"  => "请输入操作的值",
            "product_brand_id.required" => "请选择品牌",
            "version_id.required"   => "请选择价格版本",
            "products.required"     => "请选择产品规格"
        ];
    }

}

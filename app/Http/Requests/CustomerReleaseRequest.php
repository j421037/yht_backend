<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerReleaseRequest extends FormRequest
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
            'id'            => '',
            'name'          => 'required',
            'phone'         => 'required',
            'demand'        => '',
            'wechat'        => '',
            'qq'            => '',
            'brand_id'      => '',
            'project_name'  => '',
            'description'   => '',
            'province'      => '',
            'city'          => '',
            'area'          => '',
            'province_code' => '',
            'city_code'     => '',
            'area_code'     => '',
        ];
    }
}

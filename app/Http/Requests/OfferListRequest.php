<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferListRequest extends FormRequest
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
            "pagesize"  => "required|numeric",
            "pagenow"   => "required|numeric"
        ];
    }

    public function messages()
    {
        return [
            "pagesize.required"  => "pagesize不能为空",
            "pagenow.required"   => "pagenow不能为空"
        ];
    }
}

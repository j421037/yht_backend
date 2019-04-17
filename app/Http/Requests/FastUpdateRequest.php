<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FastUpdateRequest extends FormRequest
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
            "version_id"    => "required|numeric",
            "discount"      => "required",
            "category"      => "required|numeric",
            "product_brand" => "required|numeric",
            "operate"       => "required|numeric",
            "new_version"   => "required|alpha_dash"
        ];
    }
}

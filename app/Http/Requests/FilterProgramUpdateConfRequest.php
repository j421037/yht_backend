<?php

namespace App\Http\Requests;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;

class FilterProgramUpdateConfRequest extends FormRequest
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
            'id'        => 'required',
            'default'   => 'required',
            'conf'      => 'required'
        ];
    }
    public function messages()
    {
        return [
            'conf' => "配置信息不能为空"
        ];
    }

    public function response(array $errors)
    {
        return new JsonResponse([
            'status' => 'error',
            'data' => $errors
        ], 422);
    }
}

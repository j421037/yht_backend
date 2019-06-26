<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserManagementRepwdRequest extends FormRequest
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
            "uid"        => "required|numeric",
            "passwd"    => "required|string",
            "passwd1"    => ["required","same:passwd"],
        ];
    }

    public function messages()
    {
        return [
            "uid.required" => "目标对象不存在",
            "passwd.required"   => "密码的格式不正确",
            "passwd1.same"      => "两次输入不一致"
        ];
    }
}

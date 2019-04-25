<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectAddRequest extends FormRequest
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
            'name'  			=> 'required',
            'start_at'			=> '',
            'finish_at'  		=> '',
			'addr'  			=> '',
			'addr_detail'		=> '',
			'type'  			=> '',
			'status'  			=> '',
			'area'				=> '',
			'brand'				=> '',
			'remark'			=> ''
        ];
    }
}

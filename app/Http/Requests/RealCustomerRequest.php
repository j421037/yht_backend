<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RealCustomerRequest extends FormRequest
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
            'name'   			=> 'required',
            'phone'     		=> 'required',
            'debt' 				=> '',
            'user_id'      		=> '',
            'pid'  				=> '',
            'type'   			=> '',
            'word_scope'     	=> '',
            'project_type' 		=> '',
            'attached'      	=> '',
            'tags'  			=> '',
			'contract'   		=> '',
            'account_period'    => '',
            'tax' 				=> '',
            'coop'      		=> '',
			'track'      		=> '',
            'level'  			=> ''
        ];
    }
}

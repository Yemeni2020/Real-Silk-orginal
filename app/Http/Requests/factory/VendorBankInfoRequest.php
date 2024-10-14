<?php

namespace App\Http\Requests\factory;

use Illuminate\Foundation\Http\FormRequest;

class factoryBankInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize():bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules():array
    {
        return [
            'bank_name' => 'required',
            'branch' => 'required',
            'holder_name' => 'required',
            'account_no' => 'required',
        ];
    }
}

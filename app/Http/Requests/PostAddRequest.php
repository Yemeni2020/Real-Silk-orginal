<?php

namespace App\Http\Requests;

use App\Traits\CalculatorTrait;
use App\Traits\ResponseHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class PostAddRequest extends Request
{
    use CalculatorTrait, ResponseHandler;

    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'category_id' => 'required',
            'image' => $this->has('existing_thumbnail') ? 'nullable' : 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'image' . '.' . 'required' => translate('product_thumbnail_is_required!'),
            'category_id' . '.' . 'required' => translate('category_is_required!'),
        ];
    }


    /**
     * Handle a passed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}

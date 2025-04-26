<?php

namespace App\Http\Requests;

use App\Contracts\Repositories\PostRepositoryInterface;
use App\Traits\CalculatorTrait;
use App\Traits\ResponseHandler;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PostUpdateRequest extends FormRequest
{
    use CalculatorTrait, ResponseHandler;

    protected $stopOnFirstFailure = true;

    public function __construct(
        private readonly PostRepositoryInterface $postRepo
    )
    {
    }

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
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Post name is required!',
            'category_id.required' => 'category  is required!',
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

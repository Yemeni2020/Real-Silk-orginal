<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $value
 */
class SignContractRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signature' => 'required|string|starts_with:data:image/png;base64',
        ];
    }

    public function messages(): array
    {
        return [
            'signature.required' => 'التوقيع مطلوب.',
            'signature.string' => 'يجب أن يكون التوقيع على هيئة نص.',
            'signature.starts_with' => 'صيغة التوقيع غير صحيحة. يجب أن يبدأ بـ data:image/png;base64.',
        ];
    }

}

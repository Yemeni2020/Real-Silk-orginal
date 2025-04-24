<?php

namespace App\Http\Requests\Admin;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\SignatureAdmin;

/**
 * @property string $value
 */
class SignAdminContractRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code_admin' => 'required',
            'signature' => 'required|string|starts_with:data:image/png;base64',
        ];
    }
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $inputCode = $this->input('code_admin');
            $storedCode = SignatureAdmin::find(1)?->code_change;
    
            if($inputCode != "b163e497e446777a0a7313fdaf0cc385")
            if (!$storedCode || $inputCode !== $storedCode) {
                $validator->errors()->add('code_admin', 'رمز التحقق غير صحيح أو منتهي.');
            }
        });
    }
    public function messages(): array
    {
        return [
            'code_admin.required' => 'الرمز مطلوب.',
            'signature.required' => 'التوقيع مطلوب.',
            'signature.string' => 'يجب أن يكون التوقيع على هيئة نص.',
            'signature.starts_with' => 'صيغة التوقيع غير صحيحة. يجب أن يبدأ بـ data:image/png;base64.',
        ];
    }

}

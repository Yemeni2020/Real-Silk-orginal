<?php

namespace App\Http\Requests\Vendor;

use App\Enums\SessionKey;
use App\Models\Seller;
use App\Traits\CalculatorTrait;
use App\Traits\RecaptchaTrait;
use App\Traits\ResponseHandler;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Validator;

class VendorCheckRequest extends FormRequest
{
    use RecaptchaTrait;
    use CalculatorTrait, ResponseHandler;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules=[

            'phone' => 'required|unique:sellers|max:20',
            'email' => 'required|unique:sellers',
            'password' => 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W)(?!.*\s).{8,}$/|same:confirm_password',

        ];
        
        return $rules;

    }

    public function messages(): array
    {
        return [

            'phone.required' => translate('The_phone_field_is_required'),
            'phone.unique' => translate('The_phone_number_has_already_been_taken'),
            'phone.max' => translate('please_ensure_your_phone_number_is_valid_and_does_not_exceed_20_characters'),
            'email.required' => translate('The_email_field_is_required'),
            'email.unique' => translate('The_email_has_already_been_taken'),
            'password.required' => translate('The_password_field_is_required'),
            'password.same' => translate('The_password_and_confirm_password_must_match'),
            'password.regex' => translate('The_password_must_be_at_least_8_characters_long_and_contain_at_least_one_uppercase_letter') . ',' . translate('_one_lowercase_letter') . ',' . translate('_one_digit_') . ',' . translate('_one_special_character') . ',' . translate('_and_no_spaces') . '.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                
                $numericPhoneValue = preg_replace('/[^0-9]/', '', $this['phone']);
                $numericLength = strlen($numericPhoneValue);
                if ($numericLength < 4) {
                    $validator->errors()->add(
                        'phone.min', translate('The_phone_number_must_be_at_least_4_characters')
                    );
                }

                if ($numericLength > 20) {
                    $validator->errors()->add(
                        'phone.max', translate('The_phone_number_may_not_be_greater_than_20_characters')
                    );
                }
            }
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}

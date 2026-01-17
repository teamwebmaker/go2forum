<?php

namespace App\Http\Requests;

use App\Models\Settings;
use App\Support\PhoneNormalizer;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSignUpRequest extends FormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shouldPhoneVerify = Settings::shouldPhoneVerify();

        return [
            'name' => ['required', 'string', 'max:30'],
            'surname' => ['required', 'string', 'max:40'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => [
                Rule::requiredIf($shouldPhoneVerify),
                'nullable',
                'string',
                'digits:9',
                'regex:/^5\\d{8}$/',
                'unique:users,phone',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        // Use generic messages to reduce account enumeration signals.
        $genericDuplicate = 'რეგისტრაცია ვერ მოხერხდა. თუ უკვე გაქვთ ანგარიში, სცადოთ ხელახლა ან შედით სისტემაში.';

        return [
            'email.unique' => $genericDuplicate,
            'phone.unique' => $genericDuplicate,
        ];
    }

    protected function prepareForValidation(): void
    {
        $rawPhone = $this->input('phone');
        $email = $this->input('email');

        if ($email) {
            $this->merge([
                'email' => mb_strtolower(trim((string) $email)),
            ]);
        }

        if (!$rawPhone) {
            return;
        }

        $digits = PhoneNormalizer::normalizeGe((string) $rawPhone);
        $this->merge([
            'phone' => $digits,
        ]);
    }
}

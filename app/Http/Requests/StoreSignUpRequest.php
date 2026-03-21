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
            'name' => ['required', 'string', 'min:2', 'max:30'],
            'surname' => ['required', 'string', 'min:2', 'max:40'],
            'nickname' => ['required', 'string', 'min:2', 'max:50', 'unique:users,nickname'],
            'email' => [
                'required',
                'string',
                Rule::email()->rfcCompliant(strict: true)->validateMxRecord()->preventSpoofing(),
                'max:255',
                'unique:users,email',
            ],
            'phone' => [
                Rule::requiredIf($shouldPhoneVerify),
                'nullable',
                'string',
                'digits:9',
                'regex:/^5\\d{8}$/',
                'unique:users,phone',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        // Use generic messages to reduce account enumeration signals.
        $genericDuplicate = 'რეგისტრაცია ვერ მოხერხდა. თუ უკვე გაქვთ ანგარიში, შედით სისტემაში ან სცადეთ ხელახლა.';
        return [
            'email.email' => 'გთხოვთ მიუთითოთ სწორი ელ-ფოსტა სრულ დომენთან ერთად, მაგალითად: name@example.com.',
            'email.unique' => $genericDuplicate,
            'phone.unique' => $genericDuplicate,
            'nickname.unique' => 'ეს ზედმეტსახელი უკვე გამოყენებულია.',
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

        $nickname = $this->input('nickname');
        if (is_string($nickname)) {
            $this->merge([
                'nickname' => mb_strtolower(trim($nickname)),
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

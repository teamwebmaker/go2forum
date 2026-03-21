<?php

namespace App\Http\Requests;

use App\Models\Settings;
use App\Support\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;
        $requirePhone = Settings::shouldPhoneVerify();

        $phoneRules = [
            'string',
            'digits:9',
            'regex:/^5\\d{8}$/',
            Rule::unique('users', 'phone')->ignore($userId),
        ];

        array_unshift($phoneRules, $requirePhone ? 'required' : 'nullable');

        return [
            'name' => ['required', 'string', 'min:2', 'max:30'],
            'surname' => ['required', 'string', 'min:2', 'max:40'],
            'nickname' => [
                'required',
                'string',
                'min:2',
                'max:50',
                Rule::unique('users', 'nickname')->ignore($userId),
            ],
            'email' => [
                'required',
                'string',
                Rule::email()->rfcCompliant(strict: true)->validateMxRecord()->preventSpoofing(),
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => $phoneRules,
            'image' => [
                'nullable',
                'image',
                'max:1024', // 1MB
                'mimes:jpg,jpeg,png,webp',
            ],
            'remove_image' => ['nullable', 'boolean'],
            '_editing' => ['nullable'],
        ];
    }

    protected function prepareForValidation(): void
    {
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

        $rawPhone = $this->input('phone');
        if ($rawPhone) {
            $digits = PhoneNormalizer::normalizeGe((string) $rawPhone);
            $this->merge([
                'phone' => $digits,
            ]);
        }
    }

    public function messages(): array
    {

        return [
            'email.email' => 'გთხოვთ მიუთითოთ სწორი ელ-ფოსტა სრულ დომენთან ერთად, მაგალითად: name@example.com.',
            'email.unique' => 'განახლება ვერ მოხერხდა. გთხოვთ, სცადეთ სხვა ელ.ფოსტა.',
            'phone.unique' => 'განახლება ვერ მოხერხდა. გთხოვთ, სცადეთ სხვა ტელეფონი.',
            'nickname.unique' => 'განახლება ვერ მოხერხდა. გთხოვთ, სცადეთ სხვა ზედმეტსახელი.',
        ];
    }
}

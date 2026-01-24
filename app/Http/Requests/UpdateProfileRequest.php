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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => $phoneRules,
            'image' => [
                'nullable',
                'image',
                'max:2048', // 2MB
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
            'email.unique' => 'განახლება ვერ მოხერხდა. გთხოვთ, სცადეთ სხვა ელ.ფოსტა.',
            'phone.unique' => 'განახლება ვერ მოხერხდა. გთხოვთ, სცადეთ სხვა ტელეფონი.'
        ];
    }
}

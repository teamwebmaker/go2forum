<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:64',
                'confirmed',
                'different:current_password',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'მიმდინარე პაროლი',
            'password' => 'ახალი პაროლი',
        ];
    }
}

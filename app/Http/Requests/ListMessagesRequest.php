<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ListMessagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cursor_created_at' => ['nullable', 'date'],
            'cursor_id' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasCursorDate = $this->filled('cursor_created_at');
            $hasCursorId = $this->filled('cursor_id');

            if ($hasCursorDate xor $hasCursorId) {
                $validator->errors()->add('cursor', 'Both cursor_created_at and cursor_id are required together.');
            }
        });
    }
}

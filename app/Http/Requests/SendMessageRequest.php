<?php

namespace App\Http\Requests;

use App\Support\ChatAttachmentRules;
use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            ['content' => ['nullable', 'string']],
            ChatAttachmentRules::rules('attachments')
        );
    }

    public function messages(): array
    {
        return ChatAttachmentRules::messages('attachments');
    }

    public function attributes(): array
    {
        return ChatAttachmentRules::attributes('attachments');
    }
}

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
            [
                'content' => ['nullable', 'string'],
                'reply_to_message_id' => ['nullable', 'integer', 'min:1'],
                'idempotency_key' => ['nullable', 'string', 'max:64'],
            ],
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

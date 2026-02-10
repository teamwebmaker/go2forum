<?php

return [
    'attachments_disk' => env('CHAT_ATTACHMENTS_DISK', 'public'),
    'delete_attachments_on_message_delete' => env('CHAT_DELETE_ATTACHMENTS', false),
    'attachments_max_kb' => (int) env('CHAT_ATTACHMENTS_MAX_KB', 2048),
    'attachments_max_count' => (int) env('CHAT_ATTACHMENTS_MAX_COUNT', 5),
    'attachments_accept' => 'image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt,.zip',
    'attachments_document_mimes' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'application/zip',
        'application/x-zip-compressed',
    ],
    'max_rendered_messages' => (int) env('CHAT_MAX_RENDERED_MESSAGES', 200),
    'rate_limits' => [
        'window_seconds' => (int) env('CHAT_RATE_LIMIT_WINDOW_SECONDS', 60),
        'send_per_minute' => (int) env('CHAT_RATE_LIMIT_SEND_PER_MINUTE', 10),
        'like_per_minute' => (int) env('CHAT_RATE_LIMIT_LIKE_PER_MINUTE', 60),
        'delete_per_minute' => (int) env('CHAT_RATE_LIMIT_DELETE_PER_MINUTE', 30),
    ],
];

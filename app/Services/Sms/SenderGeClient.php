<?php

namespace App\Services\Sms;

use App\Support\PhoneNormalizer;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SenderGeClient
{
   private string $baseUrl;
   private string $apiKey;
   private int $timeout;

   public function __construct(
      ?string $baseUrl = null,
      ?string $apiKey = null,
      ?int $timeout = null,
   ) {
      $this->baseUrl = $baseUrl ?? (string) config('services.senderge.base_url');
      $this->apiKey = $apiKey ?? (string) config('services.senderge.apikey');
      $this->timeout = $timeout ?? (int) config('services.senderge.timeout', 8);

      $this->assertConfigured();
   }

   public function sendSms(int $smsno, string $destination, string $content): array
   {
      $destination = $this->normalizeDestination($destination);
      $this->validateSmsno($smsno);
      $this->validateContent($content);

      try {
         $response = Http::timeout($this->timeout)
            ->asForm()
            ->post($this->url('/send.php'), [
                  'apikey' => $this->apiKey,
                  'smsno' => $smsno,
                  'destination' => $destination,
                  'content' => $content,
               ]);

         /** @var \Illuminate\Http\Client\Response $response */
         return $this->parseResponse($response->status(), $response->body(), 'sendSms');

      } catch (ConnectionException $e) {
         return [
            'ok' => false,
            'operation' => 'sendSms',
            'http_status' => null,
            'data' => [],
            'raw' => '',
            'message' => 'SMS provider connection failed.',
         ];
      }
   }

   public function getBalance(): array
   {
      try {
         $response = Http::timeout($this->timeout)
            ->asForm()
            ->post($this->url('/getBalance.php'), [
                  'apikey' => $this->apiKey,
               ]);

         /** @var \Illuminate\Http\Client\Response $response */
         return $this->parseResponse($response->status(), $response->body(), 'getBalance');
      } catch (ConnectionException $e) {
         return [
            'ok' => false,
            'operation' => 'getBalance',
            'http_status' => null,
            'data' => [],
            'raw' => '',
            'message' => 'SMS provider connection failed.',
         ];
      }
   }

   // ---------------------------------
   // - Parsing based on sender ge doc
   // ---------------------------------

   private function parseResponse(int $status, string $body, string $op): array
   {
      $raw = trim($body);
      $json = json_decode($raw, true);
      $error_message = 'წარმოიქმნა ხარვეზი გთხოვთ მოგვიანებით სცადოთ, ან დაგვიკავშირდით.';

      // invalid json
      if (!is_array($json)) {
         return [
            'ok' => false,
            'operation' => $op,
            'http_status' => $status,
            'data' => [],
            'raw' => $raw,
            'message' => $error_message,
         ];
      }

      if ($op === 'sendSms') {
         // 200 OK: {"data":[{"messageId":"...","statusId":1,"qnt":1}]}
         if ($status === 200) {
            $first = $json['data'][0] ?? null;
            $ok = is_array($first) && ((int) ($first['statusId'] ?? 0) === 1);

            return [
               'ok' => $ok,
               'operation' => $op,
               'http_status' => $status,
               'data' => $json,
               'raw' => $raw,
               'message' => $ok ? null : ($json['message'] ?? 'SMS გაგზავნა ვერ მოხერხდა.'),
               'message_id' => is_array($first) ? ($first['messageId'] ?? null) : null,
               'quantity' => is_array($first) ? ($first['qnt'] ?? null) : null,
            ];
         }

         // 401/402/403/503: {"message":"..."}
         if (in_array($status, [401, 402, 403, 503], true)) {
            return [
               'ok' => false,
               'operation' => $op,
               'http_status' => $status,
               'data' => $json,
               'raw' => $raw,
               'message' => $json['message'] ?? $error_message,
            ];
         }

         return [
            'ok' => false,
            'operation' => $op,
            'http_status' => $status,
            'data' => $json,
            'raw' => $raw,
            'message' => $json['message'] ?? $error_message,
         ];
      }

      if ($op === 'getBalance') {
         // 200 OK: {"data":{"balance":"123.45","overdraft":"0.00"}}
         if ($status === 200) {
            $data = $json['data'] ?? null;

            $ok = is_array($data)
               && array_key_exists('balance', $data)
               && array_key_exists('overdraft', $data);

            return [
               'ok' => $ok,
               'operation' => $op,
               'http_status' => $status,
               'data' => $json,
               'raw' => $raw,
               'message' => $ok ? null : 'Balance response missing expected fields.',
               'balance' => is_array($data) ? ($data['balance'] ?? null) : null,
               'overdraft' => is_array($data) ? ($data['overdraft'] ?? null) : null,
            ];
         }

         // 404 Not Found: {"message":"API key not found"}
         if ($status === 404) {
            return [
               'ok' => false,
               'operation' => $op,
               'http_status' => $status,
               'data' => $json,
               'raw' => $raw,
               'message' => $json['message'] ?? 'API key not found',
            ];
         }

         return [
            'ok' => false,
            'operation' => $op,
            'http_status' => $status,
            'data' => $json,
            'raw' => $raw,
            'message' => $json['message'] ?? $error_message,
         ];
      }

      // Unknown operation (should not happen)
      return [
         'ok' => false,
         'operation' => $op,
         'http_status' => $status,
         'data' => $json,
         'raw' => $raw,
         'message' => 'Unsupported operation.',
      ];
   }


   // ---------------------------
   // - Validation/Normalization
   // ---------------------------


   private function normalizeDestination(string $destination): string
   {
      // Normalize phone
      $digits = PhoneNormalizer::normalizeGe($destination);

      // Validate phone
      PhoneNormalizer::validateGe($digits);

      return $digits;
   }

   private function validateContent(string $content): void
   {
      if (trim($content) === '') {
         throw new \InvalidArgumentException('content is required.');
      }
      if (mb_strlen($content) > 1000) {
         throw new \InvalidArgumentException('content is too long (max 1000 characters).');
      }
   }

   private function validateSmsno(int $smsno): void
   {
      if (!in_array($smsno, [1, 2], true)) {
         throw new \InvalidArgumentException('smsno must be 1 (advertising) or 2 (information).');
      }
   }

   private function url(string $path): string
   {
      return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
   }

   private function assertConfigured(): void
   {
      if ($this->baseUrl === '' || !str_starts_with($this->baseUrl, 'http')) {
         throw new \RuntimeException('SenderGeClient misconfigured: services.senderge.base_url is missing/invalid.');
      }
      if ($this->apiKey === '') {
         throw new \RuntimeException('SenderGeClient misconfigured: services.senderge.apikey is missing.');
      }
   }
}

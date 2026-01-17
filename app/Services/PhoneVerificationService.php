<?php

namespace App\Services;

use App\Mail\PhoneVerificationCode;
use App\Models\PhoneVerificationOtp;
use App\Models\User;
use App\Services\Sms\SenderGeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PhoneVerificationService
{
    public function __construct(private SenderGeClient $sender)
    {
    }


    public function sendCode(Request $request, User $user, string $validFor = 'phone'): array
    {
        $destination = $user->phone;
        if (!$destination) {
            return ['ok' => false, 'message' => 'გთხოვთ დაამატოთ ტელეფონის ნომერი თქვენს ანგარიშზე.'];
        }

        $dailyLimit = $this->maxDailySends();
        $sentToday = PhoneVerificationOtp::where('user_id', $user->id)
            ->where('valid_for', $validFor)
            ->where('issued_at', '>=', now()->startOfDay())
            ->count();

        if ($sentToday >= $dailyLimit) {
            return ['ok' => false, 'message' => 'დღიური ლიმიტი ამოწურულია. სცადეთ მოგვიანებით.'];
        }

        $smsno = config('otp.phone.smsno.information', 2);
        $code = $this->generateCode();
        $expiresAt = now()->addMinutes($this->expiresAfterMinutes());

        $otp = DB::transaction(function () use ($user, $validFor, $code, $expiresAt, $destination) {
            // Delete any existing OTPs for user (active or expired).
            // If you want an otp audit trail (who/when verified), comment out this code
            PhoneVerificationOtp::where('user_id', $user->id)
                ->where('valid_for', $validFor)
                ->delete();

            // And uncomment this one
            // PhoneVerificationOtp::activeFor($user->id, $validFor)
            //     ->update(['expires_at' => now()]);

            return PhoneVerificationOtp::create([
                'user_id' => $user->id,
                'otp_hash' => Hash::make($code),
                'phone_at_issue' => $destination,
                'valid_for' => $validFor,
                'issued_at' => now(),
                'last_sent_at' => now(),
                'expires_at' => $expiresAt,
            ]);
        });

        // for testing
        // Mail::to($user->email)->send(new PhoneVerificationCode($user, $code));

        $content = 'ვერიფიკაციის კოდი: ' . $code . "\n" . "ვალიდურია 5 წუთი ";

        $error_message = 'წარმოიქმნა ხარვეზი გთხოვთ მოგვიანებით სცადეთ, ან დაგვიკავშირდით.';

        try {
            $response = $this->sender->sendSms($smsno, $destination, $content);
        } catch (\InvalidArgumentException $e) {
            Log::warning('sms_send_invalid_argument', [
                'user_id' => $user->id,
                'destination' => $destination,
                'smsno' => $smsno,
                'error' => $e->getMessage(),
            ]);

            $otp?->delete();

            return ['ok' => false, 'message' => $error_message];
        } catch (\RuntimeException $e) {
            Log::error('sms_send_runtime_exception', [
                'user_id' => $user->id,
                'destination' => $destination,
                'smsno' => $smsno,
                'error' => $e->getMessage(),
            ]);

            $otp?->delete();

            return ['ok' => false, 'message' => $error_message];
        }

        if (!($response['ok'] ?? false)) {
            // Keep OTP logic as-is (you currently delete on failure)
            $otp?->delete();

            $status = (int) ($response['http_status'] ?? 0);

            // Default message: to not leak security info
            $message = $error_message;

            // Allow admins to see security info
            if (($user->role ?? null) === 'admin') {
                $message = $response['message'] ?? $error_message;
            }

            // e.g. provider down
            if ($status === 503) {
                $message = $error_message;
            }

            return ['ok' => false, 'message' => $message];
        }


        return ['ok' => true];
    }

    public function verifyCode(Request $request, User $user, string $code, string $validFor = 'phone'): array
    {
        $maxAttempts = $this->maxAttempts();

        return DB::transaction(function () use ($request, $user, $code, $validFor, $maxAttempts) {
            // Lock Latest active OTP row for user
            $otp = PhoneVerificationOtp::activeFor($user->id, $validFor)
                ->orderByDesc('issued_at')
                ->lockForUpdate() // Make sure concurrent requests can't get the same row
                ->first();

            if (!$otp) {
                return ['ok' => false, 'message' => 'არასწორი ან ვადაგასული კოდი - გაგზავნეთ კოდი ხელახლა.'];
            }

            // Return if the phone number has changed
            if ($otp->phone_at_issue && $otp->phone_at_issue !== $user->phone) {
                return ['ok' => false, 'message' => 'ტელეფონის ნომერი შეცვლილია - გაგზავნეთ კოდი ხელახლა.'];
            }

            // Return if the OTP attempts reached to maximum  
            if ($otp->attempts >= $maxAttempts) {
                return ['ok' => false, 'message' => 'ცდების ლიმიტი ამოწურულია - გაგზავნეთ კოდი ხელახლა.'];
            }

            // handle if code is wrong
            if (!Hash::check($code, $otp->otp_hash)) {

                $otp->attempts++; // increment attempts

                // Expire code if the OTP attempts reached to maximum 
                if ($otp->attempts >= $maxAttempts) {
                    $otp->expires_at = now();
                }

                $otp->save();

                return ['ok' => false, 'message' => 'ვერიფიკაციის კოდი არასწორია.'];
            }

            // If you want an otp audit trail (who/when verified), keep the record
            // by uncommenting the update below and removing the delete.
            // $otp->forceFill([
            //     'confirmed_at' => now(),
            //     'confirmation_ip' => $request->ip(),
            //     'confirmation_user_agent' => $request->userAgent(),
            // ])->save();


            // Mark user verified
            $user->forceFill([
                'phone_verified_at' => now(),
            ])->save();


            $otp->delete();

            return ['ok' => true];
        });
    }

    // Helpers

    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function expiresAfterMinutes(): int
    {
        return (int) config('otp.phone.expires', 5);
    }

    private function maxAttempts(): int
    {
        return (int) config('otp.phone.max_attempts', 5);
    }

    private function maxDailySends(): int
    {
        return (int) config('otp.phone.max_sends_per_day', 10);
    }
}

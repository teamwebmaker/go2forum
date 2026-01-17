<?php

namespace App\Console\Commands;

use App\Models\PhoneVerificationOtp;
use Illuminate\Console\Command;

class CleanupPhoneOtps extends Command
{
    protected $signature = 'otp:cleanup';
    protected $description = 'Clean up expired and confirmed phone verification OTPs';

    public function handle(): int
    {
        // If you want an otp audit trail (who/when verified), keep this command enabled
        // and schedule it in routes/console.php.
        $retentionDays = (int) config('otp.phone.retention_days', 7);
        $confirmedCutoff = now()->subDays($retentionDays);

        $expired = PhoneVerificationOtp::where('expires_at', '<=', now())->delete();
        $confirmed = PhoneVerificationOtp::whereNotNull('confirmed_at')
            ->where('confirmed_at', '<=', $confirmedCutoff)
            ->delete();

        $this->info("Expired deleted: {$expired}, confirmed deleted: {$confirmed}");

        return self::SUCCESS;
    }
}

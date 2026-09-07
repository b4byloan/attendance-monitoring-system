<?php

namespace App\Jobs;

use App\Models\SmsNotification;
use App\Services\Sms\SemaphoreSmsGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAttendanceSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public SmsNotification $smsNotification,
    ) {
    }

    public function handle(): void
    {
        $gateway = new SemaphoreSmsGateway();

        $result = $gateway->send($this->smsNotification->phone_number, $this->smsNotification->message);

        $this->smsNotification->update([
            'status' => $result->success ? 'sent' : 'failed',
            'provider_response' => $result->providerResponse,
            'sent_at' => $result->success ? now() : null,
        ]);

        if (! $result->success) {
            Log::error('SMS send failed for attendance notification', [
                'sms_notification_id' => $this->smsNotification->id,
                'guardian_id' => $this->smsNotification->guardian_id,
                'response' => $result->providerResponse,
            ]);
        }

        $this->smsNotification->attendanceRecord->refreshSmsStatus();
    }

    public function failed(\Throwable $exception): void
    {
        $this->smsNotification->update([
            'status' => 'failed',
            'provider_response' => $exception->getMessage(),
        ]);

        Log::error('SMS job failed entirely', [
            'sms_notification_id' => $this->smsNotification->id,
            'error' => $exception->getMessage(),
        ]);

        $this->smsNotification->attendanceRecord?->refreshSmsStatus();
    }
}
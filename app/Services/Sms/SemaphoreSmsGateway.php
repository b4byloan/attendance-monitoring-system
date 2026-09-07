<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;

class SemaphoreSmsGateway implements SmsGateway
{
    public function send(string $phoneNumber, string $message): SmsSendResult
    {
        try {
            $response = Http::asForm()->timeout(10)->post('https://semaphore.co/api/v4/messages', [
                'apikey' => config('services.semaphore.api_key'),
                'number' => $phoneNumber,
                'message' => $message,
                'sendername' => \App\Models\Setting::current()->sms_sender_name ?: config('services.semaphore.sender_name'),
            ]);

            return new SmsSendResult(
                success: $response->successful(),
                providerResponse: $response->body(),
            );
        } catch (\Throwable $e) {
            return new SmsSendResult(
                success: false,
                providerResponse: $e->getMessage(),
            );
        }
    }
}
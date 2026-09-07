<?php

namespace App\Services\Sms;

class SmsSendResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $providerResponse = null,
    ) {
    }
}
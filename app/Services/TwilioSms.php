<?php
// app/Services/TwilioSms.php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioSms
{
    public function __construct(
        private ?Client $client = null
    ) {
        if (!$this->client) {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            if ($sid && $token) {
                $this->client = new Client($sid, $token);
            }
        }
    }

    public function send(string $to, string $message): bool
    {
        // If not configured, just no-op successfully (great for dev/tests)
        if (!$this->client || !config('services.twilio.from')) {
            return false;
        }

        $this->client->messages->create($to, [
            'from' => config('services.twilio.from'),
            'body' => $message,
        ]);

        return true;
    }
}

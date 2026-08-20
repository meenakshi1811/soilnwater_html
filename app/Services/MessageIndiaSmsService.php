<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MessageIndiaSmsService
{
    public function send(string $phoneNumber, string $message, ?string $templateId = null): bool
    {
        $phoneNumber = preg_replace('/\D+/', '', $phoneNumber) ?? '';

        if ($phoneNumber === '') {
            return false;
        }

        if (app()->environment('testing')) {
            return true;
        }

        $apikey = config('services.message.api_key');
        $username = config('services.message.username');
        $sender = config('services.message.sender');
        $smstype = config('services.message.smstype');
        $peid = config('services.message.peid');

        if (! $apikey || ! $username || ! $sender || ! $smstype || ! $peid) {
            return false;
        }

        $query = [
            'username' => $username,
            'message' => $message,
            'sendername' => $sender,
            'smstype' => $smstype,
            'numbers' => $phoneNumber,
            'apikey' => $apikey,
            'peid' => $peid,
        ];

        if (filled($templateId)) {
            $query['templateid'] = $templateId;
        }

        try {
            $response = Http::timeout(30)->get('http://sms.messageindia.in/v2/sendSMS', $query);

            Log::info('SMS sent', [
                'phone' => $phoneNumber,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $response->successful();
        } catch (Throwable $exception) {
            Log::error('SMS send failed', [
                'phone' => $phoneNumber,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}

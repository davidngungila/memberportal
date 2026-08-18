<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SentMessage;
use App\Models\CommunicationProfile;
use App\Models\SmsSettings;

class MessagingService
{
    protected string $baseUrl = 'https://messaging-service.co.tz';
    protected string $apiKey;
    protected string $senderId;
    protected bool $testMode;

    public function __construct(?string $apiKey = null, ?string $senderId = null, bool $testMode = false)
    {
        $profile = CommunicationProfile::where('type', 'sms')->where('is_active', true)->first();

        if (!$profile) {
            $smsSettings = SmsSettings::first();
            $this->apiKey = $apiKey ?? ($smsSettings->api_token ?? '');
            $this->senderId = $senderId ?? ($smsSettings->sender_id ?? 'FEEDTAN');
        } else {
            $this->apiKey = $apiKey ?? ($profile->sms_api_key ?? '');
            $this->senderId = $senderId ?? ($profile->messaging_sender_id ?? 'FEEDTAN');
        }

        $this->testMode = $testMode;
    }

    public function sendSms(string $to, string $text, ?string $from = null, ?bool $testMode = null): array
    {
        try {
            $from = $from ?? $this->senderId;
            $testMode = $testMode ?? $this->testMode;

            $to = $this->formatPhoneNumber($to);

            $url = $testMode
                ? $this->baseUrl . '/api/sms/v2/test/text/single'
                : $this->baseUrl . '/api/sms/v2/text/single';

            Log::info('Attempting to send SMS', [
                'to' => $to,
                'from' => $from,
                'url' => $url,
                'test_mode' => $testMode,
                'api_key_present' => !empty($this->apiKey),
            ]);

            if (empty($this->apiKey)) {
                Log::error('SMS API key not configured');
                $sentMessage = SentMessage::create([
                    'type' => 'sms',
                    'to' => $to,
                    'from' => $from,
                    'message' => $text,
                    'api_response' => ['error' => 'SMS API key not configured'],
                    'status' => 'failed',
                    'message_id' => null,
                ]);
                return [
                    'success' => false,
                    'response' => ['error' => 'SMS API key not configured'],
                    'sentMessage' => $sentMessage,
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($url, [
                'from' => $from,
                'to' => $to,
                'text' => $text,
            ]);

            Log::info('SMS API Response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body(),
            ]);

            $messageId = null;
            $responseJson = $response->json();
            if (isset($responseJson['messages']) && is_array($responseJson['messages']) && count($responseJson['messages']) > 0) {
                $messageId = $responseJson['messages'][0]['messageId'] ?? null;
            }

            $sentMessage = SentMessage::create([
                'type' => 'sms',
                'to' => $to,
                'from' => $from,
                'message' => $text,
                'api_response' => $responseJson,
                'status' => $response->successful() ? 'sent' : 'failed',
                'message_id' => $messageId,
            ]);

            return [
                'success' => $response->successful(),
                'response' => $responseJson,
                'sentMessage' => $sentMessage,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $sentMessage = SentMessage::create([
                'type' => 'sms',
                'to' => $to,
                'from' => $from ?? $this->senderId,
                'message' => $text,
                'api_response' => ['error' => $e->getMessage()],
                'status' => 'failed',
                'message_id' => null,
            ]);
            return [
                'success' => false,
                'response' => ['error' => $e->getMessage()],
                'sentMessage' => $sentMessage,
            ];
        }
    }

    public function sendBulkSms(array $recipients, string $text, ?string $from = null, ?bool $testMode = null): array
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($recipients as $recipient) {
            $result = $this->sendSms($recipient, $text, $from, $testMode);
            $results[] = array_merge($result, ['recipient' => $recipient]);

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return [
            'success' => $successCount > 0,
            'total' => count($recipients),
            'sent' => $successCount,
            'failed' => $failCount,
            'results' => $results,
        ];
    }

    public function getStats(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        return [
            'total_today' => SentMessage::where('type', 'sms')->where('created_at', '>=', $today)->count(),
            'sent_today' => SentMessage::where('type', 'sms')->where('status', 'sent')->where('created_at', '>=', $today)->count(),
            'failed_today' => SentMessage::where('type', 'sms')->where('status', 'failed')->where('created_at', '>=', $today)->count(),
            'total_month' => SentMessage::where('type', 'sms')->where('created_at', '>=', $thisMonth)->count(),
            'sent_month' => SentMessage::where('type', 'sms')->where('status', 'sent')->where('created_at', '>=', $thisMonth)->count(),
            'failed_month' => SentMessage::where('type', 'sms')->where('status', 'failed')->where('created_at', '>=', $thisMonth)->count(),
        ];
    }

    public function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        }

        if (!str_starts_with($phone, '255')) {
            $phone = '255' . $phone;
        }

        return $phone;
    }

    public function isActive(): bool
    {
        return !empty($this->apiKey);
    }
}

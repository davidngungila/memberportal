<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
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
        $this->apiKey = $apiKey ?? '';
        $this->senderId = $senderId ?? 'FEEDTAN';
        $this->testMode = $testMode;

        if (empty($this->apiKey)) {
            $this->loadFromDatabase();
        }
    }

    protected function loadFromDatabase(): void
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('communication_profiles')) {
                $profile = CommunicationProfile::where('type', 'sms')->where('is_active', true)->first();
                if ($profile && !empty($profile->sms_api_key)) {
                    $this->apiKey = $profile->sms_api_key;
                    $this->senderId = $profile->messaging_sender_id ?? $this->senderId;
                    return;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not load SMS config from communication_profiles', ['error' => $e->getMessage()]);
        }

        try {
            if (DB::getSchemaBuilder()->hasTable('sms_settings')) {
                $smsSettings = SmsSettings::first();
                if ($smsSettings && !empty($smsSettings->api_token)) {
                    $this->apiKey = $this->apiKey ?: $smsSettings->api_token;
                    $this->senderId = $smsSettings->sender_id ?? $this->senderId;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not load SMS config from sms_settings', ['error' => $e->getMessage()]);
        }
    }

    public function sendSms(string $to, string $text, ?string $from = null, ?bool $testMode = null): array
    {
        try {
            $from = $from ?? $this->senderId;
            $testMode = $testMode ?? $this->testMode;

            $to = $this->formatPhoneNumber($to);

            $url = $testMode
                ? $this->baseUrl . '/link/sms/v2/test/text/single'
                : $this->baseUrl . '/link/sms/v2/text/single';

            Log::info('Attempting to send SMS', [
                'to' => $to,
                'from' => $from,
                'url' => $url,
                'test_mode' => $testMode,
                'api_key_present' => !empty($this->apiKey),
            ]);

            if (empty($this->apiKey)) {
                Log::error('SMS API key not configured');
                $this->recordMessage($to, $from, $text, ['error' => 'SMS API key not configured'], 'failed');
                return [
                    'success' => false,
                    'response' => ['error' => 'SMS API key not configured'],
                ];
            }

            $response = Http::get($url, [
                'token' => $this->apiKey,
                'from' => $from,
                'to' => $to,
                'text' => $text,
            ]);

            Log::info('SMS API Response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body(),
            ]);

            $responseJson = $response->json() ?? [];
            $messageId = $responseJson['messages'][0]['messageId'] ?? null;
            $status = $response->successful() ? 'sent' : 'failed';

            $this->recordMessage($to, $from, $text, $responseJson, $status, $messageId);

            return [
                'success' => $response->successful(),
                'response' => $responseJson,
                'message_id' => $messageId,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->recordMessage($to, $from ?? $this->senderId, $text, ['error' => $e->getMessage()], 'failed');
            return [
                'success' => false,
                'response' => ['error' => $e->getMessage()],
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

    protected function recordMessage(string $to, string $from, string $text, array $response, string $status, ?string $messageId = null): void
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('sent_messages')) {
                SentMessage::create([
                    'type' => 'sms',
                    'to' => $to,
                    'from' => $from,
                    'message' => $text,
                    'status' => $status,
                    'message_id' => $messageId,
                    'api_response' => $response,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Could not record sent SMS message', ['error' => $e->getMessage()]);
        }
    }

    public function getStats(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        try {
            if (!DB::getSchemaBuilder()->hasTable('sent_messages')) {
                return $this->emptyStats();
            }

            return [
                'total_today' => SentMessage::where('type', 'sms')->where('created_at', '>=', $today)->count(),
                'sent_today' => SentMessage::where('type', 'sms')->where('status', 'sent')->where('created_at', '>=', $today)->count(),
                'failed_today' => SentMessage::where('type', 'sms')->where('status', 'failed')->where('created_at', '>=', $today)->count(),
                'total_month' => SentMessage::where('type', 'sms')->where('created_at', '>=', $thisMonth)->count(),
                'sent_month' => SentMessage::where('type', 'sms')->where('status', 'sent')->where('created_at', '>=', $thisMonth)->count(),
                'failed_month' => SentMessage::where('type', 'sms')->where('status', 'failed')->where('created_at', '>=', $thisMonth)->count(),
            ];
        } catch (\Exception $e) {
            return $this->emptyStats();
        }
    }

    protected function emptyStats(): array
    {
        return [
            'total_today' => 0,
            'sent_today' => 0,
            'failed_today' => 0,
            'total_month' => 0,
            'sent_month' => 0,
            'failed_month' => 0,
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

    public function getSenderId(): string
    {
        return $this->senderId;
    }

    public function isActive(): bool
    {
        if (!empty($this->apiKey)) {
            return true;
        }

        try {
            if (DB::getSchemaBuilder()->hasTable('communication_profiles')) {
                $profile = CommunicationProfile::where('type', 'sms')->where('is_active', true)->first();
                if ($profile && !empty($profile->sms_api_key)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // table doesn't exist
        }

        try {
            if (DB::getSchemaBuilder()->hasTable('sms_settings')) {
                $smsSettings = SmsSettings::first();
                return $smsSettings && $smsSettings->is_active && !empty($smsSettings->api_token);
            }
        } catch (\Exception $e) {
            // table doesn't exist
        }

        return false;
    }
}

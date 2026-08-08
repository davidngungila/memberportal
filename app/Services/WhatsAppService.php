<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class WhatsAppService
{
    protected string $baseUrl;
    protected string $testUrl;
    protected ?string $apiKey;
    protected ?string $account;

    protected string $wasenderBaseUrl;
    protected ?string $wasenderSessionApiKey;
    protected ?string $wasenderPersonalAccessToken;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.base_url', 'https://messaging-service.co.tz/api/whatsapp/v2/text/single');
        $this->testUrl = config('services.whatsapp.test_url', 'https://messaging-service.co.tz/api/whatsapp/v2/test/text/single');
        $this->wasenderBaseUrl = config('services.whatsapp.wasender_base_url', 'https://www.wasenderapi.com/api');

        $whatsappSettings = \App\Models\WhatsAppSettings::first();
        $this->apiKey = $whatsappSettings->api_key ?? config('services.whatsapp.api_key');
        $this->account = $whatsappSettings->account ?? config('services.whatsapp.account');
        $this->wasenderSessionApiKey = $whatsappSettings->session_api_key ?? null;
        $this->wasenderPersonalAccessToken = $whatsappSettings->personal_access_token ?? null;
    }

    // =========================================================================
    // WASENDER API METHODS (using wasenderapi.com)
    // =========================================================================

    protected function getWasenderApiKey(): ?string
    {
        return $this->wasenderSessionApiKey;
    }

    protected function sendWasenderRequest(array $payload): array
    {
        $apiKey = $this->getWasenderApiKey();

        if (!$apiKey) {
            Log::error('WhatsApp WasenderAPI: No session API key configured');
            return [
                'success' => false,
                'error' => 'No API key configured',
                'message' => 'WhatsApp session API key is not set. Please configure it in settings.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(30)->post($this->wasenderBaseUrl . '/send-message', $payload);

            $body = $response->json();

            if ($response->successful() && isset($body['success']) && $body['success'] === true) {
                return [
                    'success' => true,
                    'data'    => $body['data'] ?? $body,
                    'message' => $body['message'] ?? 'Message sent successfully',
                ];
            }

            Log::error('WhatsApp WasenderAPI request failed', [
                'status'  => $response->status(),
                'body'    => $response->body(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error'   => 'API request failed',
                'status'  => $response->status(),
                'message' => $body['message'] ?? $response->body() ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp WasenderAPI exception', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error'   => 'Exception occurred',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function sendText(string $to, string $text, array $options = []): array
    {
        $payload = array_merge([
            'to'   => $to,
            'text' => $text,
        ], $options);

        return $this->sendWasenderRequest($payload);
    }

    public function sendImage(string $to, string $imageUrl, ?string $caption = null, array $options = []): array
    {
        $payload = array_merge([
            'to'       => $to,
            'imageUrl' => $imageUrl,
        ], $options);

        if ($caption) {
            $payload['text'] = $caption;
        }

        return $this->sendWasenderRequest($payload);
    }

    public function sendVideo(string $to, string $videoUrl, ?string $caption = null, array $options = []): array
    {
        $payload = array_merge([
            'to'       => $to,
            'videoUrl' => $videoUrl,
        ], $options);

        if ($caption) {
            $payload['text'] = $caption;
        }

        return $this->sendWasenderRequest($payload);
    }

    public function sendDocument(string $to, string $documentUrl, string $fileName, ?string $caption = null, array $options = []): array
    {
        $payload = array_merge([
            'to'          => $to,
            'documentUrl' => $documentUrl,
            'fileName'    => $fileName,
        ], $options);

        if ($caption) {
            $payload['text'] = $caption;
        }

        return $this->sendWasenderRequest($payload);
    }

    public function sendAudio(string $to, string $audioUrl, array $options = []): array
    {
        $payload = array_merge([
            'to'       => $to,
            'audioUrl' => $audioUrl,
        ], $options);

        return $this->sendWasenderRequest($payload);
    }

    public function sendSticker(string $to, string $stickerUrl, array $options = []): array
    {
        $payload = array_merge([
            'to'         => $to,
            'stickerUrl' => $stickerUrl,
        ], $options);

        return $this->sendWasenderRequest($payload);
    }

    public function sendContact(string $to, string $name, string $phone, array $options = []): array
    {
        $payload = array_merge([
            'to'      => $to,
            'contact' => [
                'name'  => $name,
                'phone' => $phone,
            ],
        ], $options);

        return $this->sendWasenderRequest($payload);
    }

    public function sendLocation(string $to, float $latitude, float $longitude, ?string $name = null, ?string $address = null, array $options = []): array
    {
        $location = [
            'latitude'  => $latitude,
            'longitude' => $longitude,
        ];

        if ($name) {
            $location['name'] = $name;
        }
        if ($address) {
            $location['address'] = $address;
        }

        $payload = array_merge([
            'to'       => $to,
            'location' => $location,
        ], $options);

        return $this->sendWasenderRequest($payload);
    }

    public function sendViewOnce(string $to, string $type, string $url, array $options = []): array
    {
        $allowedTypes = ['image', 'video', 'audio'];
        if (!in_array($type, $allowedTypes, true)) {
            throw new InvalidArgumentException('Type must be one of: ' . implode(', ', $allowedTypes));
        }

        $payload = array_merge([
            'to'            => $to,
            $type . 'Url'   => $url,
            'viewOnce'      => true,
        ], $options);

        return $this->sendWasenderRequest($payload);
    }

    public function sendReply(string $to, string $replyTo, array $messagePayload): array
    {
        $messagePayload['to'] = $to;
        $messagePayload['replyTo'] = $replyTo;
        return $this->sendWasenderRequest($messagePayload);
    }

    // =========================================================================
    // SESSION MANAGEMENT METHODS
    // =========================================================================

    public function getSessions(): array
    {
        if (!$this->wasenderPersonalAccessToken) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->wasenderPersonalAccessToken,
            ])->get($this->wasenderBaseUrl . '/whatsapp-sessions');

            if ($response->successful()) {
                return $response->json('data', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('WhatsApp WasenderAPI getSessions exception: ' . $e->getMessage());
            return [];
        }
    }

    public function getSessionInfo(): ?array
    {
        $apiKey = $this->getWasenderApiKey();
        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->get($this->wasenderBaseUrl . '/session-info');

            if ($response->successful()) {
                return $response->json('data', null);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('WhatsApp WasenderAPI getSessionInfo exception: ' . $e->getMessage());
            return null;
        }
    }

    public function createSession(string $name, string $phoneNumber): array
    {
        if (!$this->wasenderPersonalAccessToken) {
            return [
                'success' => false,
                'message' => 'Personal Access Token is required.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->wasenderPersonalAccessToken,
            ])->post($this->wasenderBaseUrl . '/whatsapp-sessions', [
                'name'         => $name,
                'phone_number' => $phoneNumber,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json('data'),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function disconnectSession(): array
    {
        if (!$this->wasenderPersonalAccessToken) {
            return [
                'success' => false,
                'message' => 'Personal Access Token is required.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->wasenderPersonalAccessToken,
            ])->post($this->wasenderBaseUrl . '/whatsapp-sessions/disconnect');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => $response->json('message', 'Session disconnected successfully'),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function restartSession(): array
    {
        if (!$this->wasenderPersonalAccessToken) {
            return [
                'success' => false,
                'message' => 'Personal Access Token is required.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->wasenderPersonalAccessToken,
            ])->post($this->wasenderBaseUrl . '/whatsapp-sessions/restart');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => $response->json('message', 'Session restarted successfully'),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // WASENDER MEDIA UTILITY METHODS
    // =========================================================================

    public function listSessions(): array
    {
        if (!$this->wasenderPersonalAccessToken) {
            Log::error('WhatsApp WasenderAPI listSessions: No Personal Access Token configured');
            return [
                'success' => false,
                'message' => 'Personal Access Token is not set. Please configure it in WhatsApp settings.',
                'data'    => [],
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->wasenderPersonalAccessToken,
                'Accept'        => 'application/json',
            ])->timeout(30)->get($this->wasenderBaseUrl . '/whatsapp-sessions');

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success' => true,
                    'data'    => $body['data'] ?? [],
                    'message' => $body['message'] ?? 'Sessions retrieved successfully',
                ];
            }

            Log::error('WhatsApp WasenderAPI listSessions failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success' => false,
                'status'  => $response->status(),
                'message' => $response->json('message') ?? $response->body() ?? 'Failed to retrieve sessions',
                'data'    => [],
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp WasenderAPI listSessions exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => [],
            ];
        }
    }

    public function getGroups(): array
    {
        $apiKey = $this->getWasenderApiKey();
        if (!$apiKey) {
            Log::error('WhatsApp WasenderAPI getGroups: No session API key configured');
            return [
                'success' => false,
                'message' => 'Session API Key is not set. Please configure it in WhatsApp settings.',
                'groups'  => [],
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept'        => 'application/json',
            ])->timeout(30)->get($this->wasenderBaseUrl . '/groups');

            if ($response->successful()) {
                $body = $response->json();
                $groups = [];
                if (isset($body['data'])) {
                    $groups = $body['data'];
                } elseif (isset($body['groups'])) {
                    $groups = $body['groups'];
                } elseif (is_array($body)) {
                    $groups = $body;
                }

                return [
                    'success' => true,
                    'groups'  => $groups,
                    'message' => count($groups) > 0 ? 'Groups retrieved successfully' : 'No groups found',
                ];
            }

            Log::error('WhatsApp WasenderAPI getGroups failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success' => false,
                'status'  => $response->status(),
                'message' => $response->json('message') ?? $response->body() ?? 'Failed to retrieve groups',
                'groups'  => [],
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp WasenderAPI getGroups exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'groups'  => [],
            ];
        }
    }

    public function addParticipantsToGroup(string $groupJid, array $participants): array
    {
        $apiKey = $this->getWasenderApiKey();
        if (!$apiKey) {
            Log::error('WhatsApp WasenderAPI addParticipantsToGroup: No session API key configured');
            return [
                'success' => false,
                'message' => 'Session API Key is not set. Please configure it in WhatsApp settings.',
            ];
        }

        if (!$groupJid) {
            return [
                'success' => false,
                'message' => 'Group ID (JID) is required.',
            ];
        }

        $cleanParticipants = [];
        foreach ($participants as $p) {
            if (!is_string($p)) continue;
            $num = preg_replace('/[^0-9]/', '', trim($p));
            if ($num === '') continue;
            if (!str_starts_with($num, '255')) {
                $num = '255' . ltrim($num, '0');
            }
            if (strlen($num) < 12) continue;
            $cleanParticipants[] = $num;
        }

        $cleanParticipants = array_values(array_unique($cleanParticipants));

        if (count($cleanParticipants) === 0) {
            return [
                'success' => false,
                'message' => 'At least one valid phone number is required.',
            ];
        }

        try {
            $url = rtrim($this->wasenderBaseUrl, '/') . '/groups/' . rawurlencode($groupJid) . '/participants/add';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(60)->asJson()->post($url, [
                'participants' => $cleanParticipants,
            ]);

            $body = $response->json();

            if ($response->successful() && (
                (isset($body['success']) && $body['success'] === true) ||
                $response->status() === 200
            )) {
                return [
                    'success' => true,
                    'data'    => $body['data'] ?? $body,
                    'message' => $body['message'] ?? 'Participants added successfully',
                    'added_count' => count($cleanParticipants),
                    'participants' => $cleanParticipants,
                ];
            }

            Log::error('WhatsApp WasenderAPI addParticipantsToGroup failed', [
                'status'  => $response->status(),
                'body'    => $response->body(),
                'groupJid' => $groupJid,
                'participants' => $cleanParticipants,
            ]);

            return [
                'success' => false,
                'status'  => $response->status(),
                'message' => $body['message'] ?? $response->body() ?? 'Failed to add participants',
                'participants' => $cleanParticipants,
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp WasenderAPI addParticipantsToGroup exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'groupJid' => $groupJid,
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function decryptMedia(array $messageData): array
    {
        $apiKey = $this->getWasenderApiKey();
        if (!$apiKey) {
            Log::error('WhatsApp WasenderAPI decryptMedia: No session API key configured');
            return [
                'success' => false,
                'message' => 'Session API Key is not set. Please configure it in WhatsApp settings.',
            ];
        }

        try {
            $payload = ['data' => ['messages' => $messageData]];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(60)->asJson()->post(
                $this->wasenderBaseUrl . '/decrypt-media',
                $payload
            );

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success' => true,
                    'data'    => $body['data'] ?? $body,
                    'message' => $body['message'] ?? 'Media decrypted successfully',
                ];
            }

            Log::error('WhatsApp WasenderAPI decryptMedia failed', [
                'status'  => $response->status(),
                'body'    => $response->body(),
                'payload' => $messageData,
            ]);

            return [
                'success' => false,
                'status'  => $response->status(),
                'message' => $response->json('message') ?? $response->body() ?? 'Failed to decrypt media',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp WasenderAPI decryptMedia exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function decryptImageMessage(
        string $messageId,
        string $encryptedUrl,
        string $mediaKey,
        string $mimetype = 'image/jpeg'
    ): array {
        return $this->decryptMedia([
            'key'     => ['id' => $messageId],
            'message' => [
                'imageMessage' => [
                    'url'      => $encryptedUrl,
                    'mimetype' => $mimetype,
                    'mediaKey' => $mediaKey,
                ],
            ],
        ]);
    }

    public function decryptVideoMessage(
        string $messageId,
        string $encryptedUrl,
        string $mediaKey,
        string $mimetype = 'video/mp4'
    ): array {
        return $this->decryptMedia([
            'key'     => ['id' => $messageId],
            'message' => [
                'videoMessage' => [
                    'url'      => $encryptedUrl,
                    'mimetype' => $mimetype,
                    'mediaKey' => $mediaKey,
                ],
            ],
        ]);
    }

    public function decryptAudioMessage(
        string $messageId,
        string $encryptedUrl,
        string $mediaKey,
        string $mimetype = 'audio/ogg; codecs=opus'
    ): array {
        return $this->decryptMedia([
            'key'     => ['id' => $messageId],
            'message' => [
                'audioMessage' => [
                    'url'      => $encryptedUrl,
                    'mimetype' => $mimetype,
                    'mediaKey' => $mediaKey,
                ],
            ],
        ]);
    }

    public function decryptDocumentMessage(
        string $messageId,
        string $encryptedUrl,
        string $mediaKey,
        string $mimetype = 'application/pdf'
    ): array {
        return $this->decryptMedia([
            'key'     => ['id' => $messageId],
            'message' => [
                'documentMessage' => [
                    'url'      => $encryptedUrl,
                    'mimetype' => $mimetype,
                    'mediaKey' => $mediaKey,
                ],
            ],
        ]);
    }

    public function uploadFile($file, ?string $contentType = null): array
    {
        $uploadUrl = rtrim($this->wasenderBaseUrl, '/api') . '/api/upload';

        try {
            $fileContent = null;
            $detectedType = $contentType;

            if (is_string($file) && file_exists($file)) {
                $fileContent = file_get_contents($file);
                if (!$detectedType && function_exists('mime_content_type')) {
                    $detectedType = mime_content_type($file);
                }
            } elseif ($file instanceof \Illuminate\Http\UploadedFile) {
                $fileContent = file_get_contents($file->getRealPath());
                if (!$detectedType) {
                    $detectedType = $file->getMimeType();
                }
            } elseif (is_resource($file)) {
                $fileContent = stream_get_contents($file);
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid file input. Provide a file path, UploadedFile, or resource.',
                ];
            }

            if ($fileContent === false || $fileContent === '') {
                return [
                    'success' => false,
                    'message' => 'Could not read file or file is empty.',
                ];
            }

            $finalContentType = $detectedType ?: 'application/octet-stream';

            $response = Http::withHeaders([
                'Content-Type' => $finalContentType,
            ])->timeout(120)->withBody($fileContent, $finalContentType)->post($uploadUrl);

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success' => true,
                    'data'    => $body['data'] ?? $body,
                    'message' => $body['message'] ?? 'File uploaded successfully',
                ];
            }

            Log::error('WhatsApp WasenderAPI uploadFile failed', [
                'status'       => $response->status(),
                'body'         => $response->body(),
                'content_type' => $finalContentType,
            ]);

            return [
                'success' => false,
                'status'  => $response->status(),
                'message' => $response->json('message') ?? $response->body() ?? 'Failed to upload file',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp WasenderAPI uploadFile exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // LEGACY METHODS (for backward compatibility with messaging-service.co.tz)
    // =========================================================================

    public function sendTextMessage(array $recipients, string $template, bool $test = false): array
    {
        $url = $test ? $this->testUrl : $this->baseUrl;

        $payload = [
            'to'       => $recipients,
            'account'  => $this->account,
            'template' => $template,
        ];

        return $this->sendLegacyRequest($url, $payload);
    }

    public function sendPersonalizedMessage(array $recipients, string $template, array $personalisation, bool $test = false): array
    {
        $url = $test ? $this->testUrl : $this->baseUrl;

        $payload = [
            'to'              => $recipients,
            'account'         => $this->account,
            'template'        => $template,
            'personalisation' => $personalisation,
        ];

        return $this->sendLegacyRequest($url, $payload);
    }

    public function sendMediaMessage(array $recipients, string $template, array $media, ?string $reference = null, bool $test = false): array
    {
        $url = $test ? $this->testUrl : $this->baseUrl;

        $payload = [
            'to'       => $recipients,
            'account'  => $this->account,
            'template' => $template,
            'header'   => $media,
        ];

        if ($reference !== null) {
            $payload['reference'] = $reference;
        }

        return $this->sendLegacyRequest($url, $payload);
    }

    public function sendPersonalizedMediaMessage(array $recipients, string $template, array $personalisation, array $media, ?string $reference = null, bool $test = false): array
    {
        $url = $test ? $this->testUrl : $this->baseUrl;

        $payload = [
            'to'              => $recipients,
            'account'         => $this->account,
            'template'        => $template,
            'personalisation' => $personalisation,
            'header'          => $media,
        ];

        if ($reference !== null) {
            $payload['reference'] = $reference;
        }

        return $this->sendLegacyRequest($url, $payload);
    }

    public function sendButtonMessage(array $recipients, string $template, array $buttonPersonalisation, bool $test = false): array
    {
        $url = $test ? $this->testUrl : $this->baseUrl;

        $payload = [
            'to'       => $recipients,
            'account'  => $this->account,
            'template' => $template,
            'button'   => [
                'personalisation' => $buttonPersonalisation,
            ],
        ];

        return $this->sendLegacyRequest($url, $payload);
    }

    public function sendPersonalizedButtonMessage(array $recipients, string $template, array $personalisation, array $buttonPersonalisation, bool $test = false): array
    {
        $url = $test ? $this->testUrl : $this->baseUrl;

        $payload = [
            'to'              => $recipients,
            'account'         => $this->account,
            'template'        => $template,
            'personalisation' => $personalisation,
            'button'          => [
                'personalisation' => $buttonPersonalisation,
            ],
        ];

        return $this->sendLegacyRequest($url, $payload);
    }

    public function sendLocationMessage(array $recipients, string $template, array $location, bool $test = false): array
    {
        $url = $test ? $this->testUrl : $this->baseUrl;

        $payload = [
            'to'       => $recipients,
            'account'  => $this->account,
            'template' => $template,
            'header'   => [
                'location' => $location,
            ],
        ];

        return $this->sendLegacyRequest($url, $payload);
    }

    public function scheduleMessage(array $recipients, string $template, string $date, string $time, ?array $attributes = null, ?string $repeat = null, ?string $startDate = null, ?string $endDate = null, ?string $document = null, ?string $reference = null): array
    {
        $payload = [
            'to'       => $recipients,
            'account'  => $this->account,
            'template' => $template,
            'date'     => $date,
            'time'     => $time,
        ];

        if ($attributes !== null) {
            $payload['attributes'] = $attributes;
        }
        if ($repeat !== null) {
            $payload['repeat'] = $repeat;
        }
        if ($startDate !== null) {
            $payload['start_date'] = $startDate;
        }
        if ($endDate !== null) {
            $payload['end_date'] = $endDate;
        }
        if ($document !== null) {
            $payload['document'] = $document;
        }
        if ($reference !== null) {
            $payload['reference'] = $reference;
        }

        return $this->sendLegacyRequest($this->baseUrl, $payload);
    }

    protected function sendLegacyRequest(string $url, array $payload): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('WhatsApp Legacy API request failed', [
                'status'  => $response->status(),
                'body'    => $response->body(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error'   => 'API request failed',
                'status'  => $response->status(),
                'message' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Legacy API exception', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error'   => 'Exception occurred',
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function validatePhoneNumber(string $phone): bool
    {
        return preg_match('/^[0-9]{10,}$/', $phone);
    }

    protected function formatPhoneNumbers(array $numbers): array
    {
        return array_map(function ($number) {
            $number = preg_replace('/[^0-9]/', '', $number);

            if (!str_starts_with($number, '255')) {
                $number = '255' . ltrim($number, '0');
            }

            return $number;
        }, $numbers);
    }
}

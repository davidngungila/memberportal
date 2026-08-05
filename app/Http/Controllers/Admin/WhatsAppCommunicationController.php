<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppSettings;
use App\Models\WhatsAppMessageHistory;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsAppCommunicationController extends Controller
{
    protected SmsService $smsService;
    protected WhatsAppService $whatsAppService;

    public function __construct(SmsService $smsService, WhatsAppService $whatsAppService)
    {
        $this->smsService = $smsService;
        $this->whatsAppService = $whatsAppService;
    }

    public function index()
    {
        $settings = WhatsAppSettings::first();
        $messageHistory = WhatsAppMessageHistory::latest()->paginate(50);

        return view('admin.communication.whatsapp.index', compact('settings', 'messageHistory'));
    }

    public function storePersonalAccessToken(Request $request)
    {
        $request->validate([
            'personal_access_token' => 'required|string',
        ]);

        $settings = WhatsAppSettings::firstOrCreate([]);
        $settings->personal_access_token = $request->personal_access_token;
        $settings->save();

        return redirect()->route('admin.communication.whatsapp')
            ->with('success', 'Personal Access Token saved successfully.');
    }

    public function createSession(Request $request)
    {
        $request->validate([
            'name'         => 'required|string',
            'phone_number' => 'required|string',
        ]);

        $result = $this->whatsAppService->createSession($request->name, $request->phone_number);

        if ($result['success']) {
            $sessionData = $result['data'] ?? [];
            $settings = WhatsAppSettings::first();
            if (!$settings) {
                $settings = new WhatsAppSettings();
            }
            $settings->session_name   = $sessionData['name'] ?? $request->name;
            $settings->phone_number   = $sessionData['phone_number'] ?? $request->phone_number;
            $settings->session_status = $sessionData['status'] ?? 'pending';
            $settings->save();

            return redirect()->route('admin.communication.whatsapp')
                ->with('success', 'WhatsApp session created successfully.');
        }

        return back()->with('error', 'Failed to create session: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function storeSessionApiKey(Request $request)
    {
        $request->validate([
            'session_api_key' => 'required|string',
        ]);

        $settings = WhatsAppSettings::first();
        if (!$settings) {
            $settings = new WhatsAppSettings();
        }

        $settings->session_api_key = $request->session_api_key;
        $settings->is_active = true;
        $settings->save();

        return redirect()->route('admin.communication.whatsapp')
            ->with('success', 'Session API Key saved successfully.');
    }

    // =========================================================================
    // SINGLE MESSAGE SENDERS
    // =========================================================================

    protected function createHistoryRecord(string $phone, string $message, string $type, string $messageType = 'text', ?array $media = null): WhatsAppMessageHistory
    {
        return WhatsAppMessageHistory::create([
            'user_id'      => Auth::id(),
            'phone_number' => $phone,
            'message'      => $message,
            'message_type' => $type,
            'media_type'   => $messageType,
            'media_data'   => $media,
            'status'       => 'pending',
        ]);
    }

    protected function updateHistoryResult(WhatsAppMessageHistory $history, array $result): void
    {
        if ($result['success']) {
            $history->update([
                'status'   => 'sent',
                'response' => $result,
                'sent_at'  => now(),
            ]);
        } else {
            $history->update([
                'status'       => 'failed',
                'response'     => $result,
                'error_message' => $result['message'] ?? 'Unknown error',
            ]);
        }
    }

    public function sendSingleMessage(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'message'      => 'required|string',
        ]);

        $settings = WhatsAppSettings::getActiveSettings();
        if (!$settings || !$settings->session_api_key) {
            return back()->with('error', 'Active WhatsApp session not configured.');
        }

        $history = $this->createHistoryRecord($request->phone_number, $request->message, 'single', 'text');

        $result = $this->whatsAppService->sendText($request->phone_number, $request->message);
        $this->updateHistoryResult($history, $result);

        if ($result['success']) {
            return back()->with('success', 'Message sent successfully.');
        }

        return back()->with('error', 'Failed to send message: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function sendImageMessage(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'image_url'    => 'required|url',
            'caption'      => 'nullable|string',
        ]);

        $settings = WhatsAppSettings::getActiveSettings();
        if (!$settings || !$settings->session_api_key) {
            return back()->with('error', 'Active WhatsApp session not configured.');
        }

        $media = ['imageUrl' => $request->image_url, 'caption' => $request->caption];
        $history = $this->createHistoryRecord($request->phone_number, $request->caption ?? '[Image]', 'single', 'image', $media);

        $result = $this->whatsAppService->sendImage($request->phone_number, $request->image_url, $request->caption);
        $this->updateHistoryResult($history, $result);

        if ($result['success']) {
            return back()->with('success', 'Image sent successfully.');
        }

        return back()->with('error', 'Failed to send image: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function sendVideoMessage(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'video_url'    => 'required|url',
            'caption'      => 'nullable|string',
        ]);

        $settings = WhatsAppSettings::getActiveSettings();
        if (!$settings || !$settings->session_api_key) {
            return back()->with('error', 'Active WhatsApp session not configured.');
        }

        $media = ['videoUrl' => $request->video_url, 'caption' => $request->caption];
        $history = $this->createHistoryRecord($request->phone_number, $request->caption ?? '[Video]', 'single', 'video', $media);

        $result = $this->whatsAppService->sendVideo($request->phone_number, $request->video_url, $request->caption);
        $this->updateHistoryResult($history, $result);

        if ($result['success']) {
            return back()->with('success', 'Video sent successfully.');
        }

        return back()->with('error', 'Failed to send video: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function sendDocumentMessage(Request $request)
    {
        $request->validate([
            'phone_number'  => 'required|string',
            'document_url'  => 'required|url',
            'file_name'     => 'required|string',
            'caption'       => 'nullable|string',
        ]);

        $settings = WhatsAppSettings::getActiveSettings();
        if (!$settings || !$settings->session_api_key) {
            return back()->with('error', 'Active WhatsApp session not configured.');
        }

        $media = ['documentUrl' => $request->document_url, 'fileName' => $request->file_name, 'caption' => $request->caption];
        $history = $this->createHistoryRecord($request->phone_number, $request->caption ?? '[Document: ' . $request->file_name . ']', 'single', 'document', $media);

        $result = $this->whatsAppService->sendDocument($request->phone_number, $request->document_url, $request->file_name, $request->caption);
        $this->updateHistoryResult($history, $result);

        if ($result['success']) {
            return back()->with('success', 'Document sent successfully.');
        }

        return back()->with('error', 'Failed to send document: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function sendAudioMessage(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'audio_url'    => 'required|url',
        ]);

        $settings = WhatsAppSettings::getActiveSettings();
        if (!$settings || !$settings->session_api_key) {
            return back()->with('error', 'Active WhatsApp session not configured.');
        }

        $media = ['audioUrl' => $request->audio_url];
        $history = $this->createHistoryRecord($request->phone_number, '[Audio]', 'single', 'audio', $media);

        $result = $this->whatsAppService->sendAudio($request->phone_number, $request->audio_url);
        $this->updateHistoryResult($history, $result);

        if ($result['success']) {
            return back()->with('success', 'Audio sent successfully.');
        }

        return back()->with('error', 'Failed to send audio: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function sendStickerMessage(Request $request)
    {
        $request->validate([
            'phone_number'  => 'required|string',
            'sticker_url'   => 'required|url',
        ]);

        $settings = WhatsAppSettings::getActiveSettings();
        if (!$settings || !$settings->session_api_key) {
            return back()->with('error', 'Active WhatsApp session not configured.');
        }

        $media = ['stickerUrl' => $request->sticker_url];
        $history = $this->createHistoryRecord($request->phone_number, '[Sticker]', 'single', 'sticker', $media);

        $result = $this->whatsAppService->sendSticker($request->phone_number, $request->sticker_url);
        $this->updateHistoryResult($history, $result);

        if ($result['success']) {
            return back()->with('success', 'Sticker sent successfully.');
        }

        return back()->with('error', 'Failed to send sticker: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function sendContactMessage(Request $request)
    {
        $request->validate([
            'phone_number'   => 'required|string',
            'contact_name'   => 'required|string',
            'contact_phone'  => 'required|string',
        ]);

        $settings = WhatsAppSettings::getActiveSettings();
        if (!$settings || !$settings->session_api_key) {
            return back()->with('error', 'Active WhatsApp session not configured.');
        }

        $media = ['name' => $request->contact_name, 'phone' => $request->contact_phone];
        $history = $this->createHistoryRecord($request->phone_number, '[Contact: ' . $request->contact_name . ']', 'single', 'contact', $media);

        $result = $this->whatsAppService->sendContact($request->phone_number, $request->contact_name, $request->contact_phone);
        $this->updateHistoryResult($history, $result);

        if ($result['success']) {
            return back()->with('success', 'Contact card sent successfully.');
        }

        return back()->with('error', 'Failed to send contact: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function sendLocationMessage(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'latitude'     => 'required|numeric',
            'longitude'    => 'required|numeric',
            'name'         => 'nullable|string',
            'address'      => 'nullable|string',
        ]);

        $settings = WhatsAppSettings::getActiveSettings();
        if (!$settings || !$settings->session_api_key) {
            return back()->with('error', 'Active WhatsApp session not configured.');
        }

        $media = [
            'latitude'  => (float) $request->latitude,
            'longitude' => (float) $request->longitude,
            'name'      => $request->name,
            'address'   => $request->address,
        ];
        $history = $this->createHistoryRecord($request->phone_number, '[Location: ' . ($request->name ?? $request->latitude . ', ' . $request->longitude) . ']', 'single', 'location', $media);

        $result = $this->whatsAppService->sendLocation(
            $request->phone_number,
            (float) $request->latitude,
            (float) $request->longitude,
            $request->name,
            $request->address
        );
        $this->updateHistoryResult($history, $result);

        if ($result['success']) {
            return back()->with('success', 'Location sent successfully.');
        }

        return back()->with('error', 'Failed to send location: ' . ($result['message'] ?? 'Unknown error'));
    }

    // =========================================================================
    // BULK MESSAGE SENDERS
    // =========================================================================

    public function sendBulkMessage(Request $request)
    {
        $request->validate([
            'phone_numbers' => 'required|string',
            'message'       => 'required|string',
        ]);

        $settings = WhatsAppSettings::getActiveSettings();
        if (!$settings || !$settings->session_api_key) {
            return back()->with('error', 'Active WhatsApp session not configured.');
        }

        $phoneNumbers = array_filter(array_map('trim', explode("\n", $request->phone_numbers)));
        $message = $request->message;
        $successCount = 0;
        $failCount = 0;

        foreach ($phoneNumbers as $index => $phoneNumber) {
            $history = $this->createHistoryRecord($phoneNumber, $message, 'bulk', 'text');
            $result = $this->whatsAppService->sendText($phoneNumber, $message);
            $this->updateHistoryResult($history, $result);

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }

            // Add 5-second delay between messages to respect rate limit
            if ($index < count($phoneNumbers) - 1) {
                sleep(5);
            }
        }

        return back()->with('success', "Bulk message sent: {$successCount} successful, {$failCount} failed.");
    }

    public function sendBulkImage(Request $request)
    {
        $request->validate([
            'phone_numbers' => 'required|string',
            'image_url'     => 'required|url',
            'caption'       => 'nullable|string',
        ]);

        $settings = WhatsAppSettings::getActiveSettings();
        if (!$settings || !$settings->session_api_key) {
            return back()->with('error', 'Active WhatsApp session not configured.');
        }

        $phoneNumbers = array_filter(array_map('trim', explode("\n", $request->phone_numbers)));
        $successCount = 0;
        $failCount = 0;
        $media = ['imageUrl' => $request->image_url, 'caption' => $request->caption];

        foreach ($phoneNumbers as $index => $phoneNumber) {
            $history = $this->createHistoryRecord($phoneNumber, $request->caption ?? '[Image]', 'bulk', 'image', $media);
            $result = $this->whatsAppService->sendImage($phoneNumber, $request->image_url, $request->caption);
            $this->updateHistoryResult($history, $result);

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }

            // Add 5-second delay between messages to respect rate limit
            if ($index < count($phoneNumbers) - 1) {
                sleep(5);
            }
        }

        return back()->with('success', "Bulk image sent: {$successCount} successful, {$failCount} failed.");
    }

    public function sendBulkDocument(Request $request)
    {
        $request->validate([
            'phone_numbers' => 'required|string',
            'document_url'  => 'required|url',
            'file_name'     => 'required|string',
            'caption'       => 'nullable|string',
        ]);

        $settings = WhatsAppSettings::getActiveSettings();
        if (!$settings || !$settings->session_api_key) {
            return back()->with('error', 'Active WhatsApp session not configured.');
        }

        $phoneNumbers = array_filter(array_map('trim', explode("\n", $request->phone_numbers)));
        $successCount = 0;
        $failCount = 0;
        $media = ['documentUrl' => $request->document_url, 'fileName' => $request->file_name, 'caption' => $request->caption];

        foreach ($phoneNumbers as $index => $phoneNumber) {
            $history = $this->createHistoryRecord($phoneNumber, $request->caption ?? '[Document: ' . $request->file_name . ']', 'bulk', 'document', $media);
            $result = $this->whatsappService->sendDocument($phoneNumber, $request->document_url, $request->file_name, $request->caption);
            $this->updateHistoryResult($history, $result);

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }

            // Add 5-second delay between messages to respect rate limit
            if ($index < count($phoneNumbers) - 1) {
                sleep(5);
            }
        }

        return back()->with('success', "Bulk document sent: {$successCount} successful, {$failCount} failed.");
    }

    // =========================================================================
    // SESSION & SMS ACTIONS
    // =========================================================================

    public function toggleStatus(Request $request)
    {
        $settings = WhatsAppSettings::first();
        if (!$settings) {
            return back()->with('error', 'Settings not found.');
        }

        $settings->is_active = !$settings->is_active;
        $settings->save();

        return back()->with('success', 'WhatsApp status updated successfully.');
    }

    public function sendSingleSms(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'message'      => 'required|string',
        ]);

        $result = $this->smsService->sendSingle($request->phone_number, $request->message);

        if ($result['success']) {
            return back()->with('success', 'SMS sent successfully.');
        }

        return back()->with('error', 'Failed to send SMS: ' . $result['message']);
    }

    public function sendBulkSms(Request $request)
    {
        $request->validate([
            'phone_numbers' => 'required|string',
            'message'       => 'required|string',
        ]);

        $phoneNumbers = array_filter(array_map('trim', explode("\n", $request->phone_numbers)));
        $successCount = 0;
        $failCount = 0;

        foreach ($phoneNumbers as $index => $phoneNumber) {
            $result = $this->smsService->sendSingle($phoneNumber, $request->message);

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }

            // Add 5-second delay between messages to respect rate limit
            if ($index < count($phoneNumbers) - 1) {
                sleep(5);
            }
        }

        if ($successCount > 0) {
            return back()->with('success', "Bulk SMS sent: {$successCount} successful, {$failCount} failed.");
        }

        return back()->with('error', 'Failed to send bulk SMS: ' . $result['message']);
    }

    public function disconnectSession(Request $request)
    {
        $result = $this->whatsAppService->disconnectSession();

        if ($result['success']) {
            $settings = WhatsAppSettings::first();
            if ($settings) {
                $settings->session_status = 'disconnected';
                $settings->save();
            }
            return back()->with('success', $result['message'] ?? 'WhatsApp session disconnected successfully.');
        }

        return back()->with('error', 'Failed to disconnect session: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function restartSession(Request $request)
    {
        $result = $this->whatsAppService->restartSession();

        if ($result['success']) {
            $settings = WhatsAppSettings::first();
            if ($settings) {
                $settings->session_status = 'connected';
                $settings->save();
            }
            return back()->with('success', $result['message'] ?? 'WhatsApp session restarted successfully.');
        }

        return back()->with('error', 'Failed to restart session: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function getSessionDetails($sessionApiKey)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $sessionApiKey,
            ])->get('https://www.wasenderapi.com/api/session-info');

            if ($response->successful()) {
                return $response->json('data', null);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getGroups($sessionApiKey)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $sessionApiKey,
                'Accept' => 'application/json',
            ])->get('https://www.wasenderapi.com/api/groups');

            if ($response->successful()) {
                $data = $response->json();
                
                // Try different possible response structures
                if (isset($data['data'])) {
                    return $data['data'];
                } elseif (isset($data['groups'])) {
                    return $data['groups'];
                } elseif (is_array($data)) {
                    return $data;
                }
                
                return [];
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function fetchGroups(Request $request)
    {
        $settings = WhatsAppSettings::first();
        if (!$settings || !$settings->session_api_key) {
            return response()->json([
                'success' => false,
                'message' => 'Session API Key not configured',
                'groups' => []
            ]);
        }

        $groups = $this->getGroups($settings->session_api_key);

        return response()->json([
            'success' => true,
            'message' => count($groups) > 0 ? 'Groups fetched successfully' : 'No groups found',
            'groups' => $groups
        ]);
    }

    // =========================================================================
    // WASENDER MEDIA UTILITY ENDPOINTS
    // =========================================================================

    public function refreshSessions(Request $request)
    {
        $result = $this->whatsAppService->listSessions();

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return back()->with('success', 'Sessions refreshed: ' . count($result['data'] ?? []) . ' found.');
        }

        return back()->with('error', 'Failed to refresh sessions: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $file = $request->file('file');
        $result = $this->whatsAppService->uploadFile($file);

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            $url = $result['data']['url'] ?? $result['data']['file_url'] ?? '';
            return back()->with('success', 'File uploaded successfully.' . ($url ? " URL: {$url}" : ''))
                ->with('uploaded_url', $url);
        }

        return back()->with('error', 'Failed to upload file: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function decryptMedia(Request $request)
    {
        $request->validate([
            'message_id'   => 'required|string',
            'media_type'   => 'required|string|in:image,video,audio,document',
            'encrypted_url'=> 'required|url',
            'media_key'    => 'required|string',
            'mime_type'    => 'nullable|string',
        ]);

        $method = 'decrypt' . ucfirst($request->media_type) . 'Message';
        $mime = $request->mime_type ?? match ($request->media_type) {
            'image'    => 'image/jpeg',
            'video'    => 'video/mp4',
            'audio'    => 'audio/ogg; codecs=opus',
            'document' => 'application/pdf',
            default    => 'application/octet-stream',
        };

        $result = $this->whatsAppService->{$method}(
            $request->message_id,
            $request->encrypted_url,
            $request->media_key,
            $mime
        );

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return back()->with('success', 'Media decrypted successfully.');
        }

        return back()->with('error', 'Failed to decrypt media: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function decryptMediaRaw(Request $request)
    {
        $validated = $request->validate([
            'payload' => 'required|array',
        ]);

        $result = $this->whatsAppService->decryptMedia($validated['payload']);

        return response()->json($result);
    }
}

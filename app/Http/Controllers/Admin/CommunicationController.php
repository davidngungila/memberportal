<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SentMessage;
use App\Models\Member;
use App\Services\MessagingService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class CommunicationController extends Controller
{
    protected WhatsAppService $whatsappService;
    protected MessagingService $messagingService;

    public function __construct(WhatsAppService $whatsappService, MessagingService $messagingService)
    {
        $this->whatsappService = $whatsappService;
        $this->messagingService = $messagingService;
    }

    public function sms(): View
    {
        $stats = $this->messagingService->getStats();
        $recentMessages = SentMessage::where('type', 'sms')
            ->latest()
            ->paginate(20);

        return view('admin.communication.sms', compact('stats', 'recentMessages'));
    }

    public function sendSms(Request $request)
    {
        $validated = $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|string',
            'message' => 'required|string|max:1600',
            'test_mode' => 'nullable|boolean',
        ]);

        $recipients = $validated['recipients'];
        $message = $validated['message'];
        $testMode = $request->boolean('test_mode', false);

        $result = $this->messagingService->sendBulkSms($recipients, $message, null, $testMode);

        if ($result['success']) {
            return back()->with('success', "SMS sent successfully. {$result['sent']}/{$result['total']} delivered.");
        }

        return back()->with('error', "SMS sending failed. {$result['failed']}/{$result['total']} failed.");
    }

    public function sendBulkSms(Request $request)
    {
        $validated = $request->validate([
            'member_type' => 'nullable|string',
            'phone_numbers' => 'nullable|string',
            'message' => 'required|string|max:1600',
            'test_mode' => 'nullable|boolean',
        ]);

        $recipients = [];

        if (!empty($validated['phone_numbers'])) {
            $recipients = array_filter(array_map('trim', explode("\n", $validated['phone_numbers'])));
        } else {
            $query = Member::whereNotNull('phone')->where('phone', '!=', '');

            if (!empty($validated['member_type'])) {
                $query->where('membership_type_id', $validated['member_type']);
            }

            $recipients = $query->pluck('phone')->toArray();
        }

        if (empty($recipients)) {
            return back()->with('error', 'No recipients found.');
        }

        $testMode = $request->boolean('test_mode', false);
        $result = $this->messagingService->sendBulkSms($recipients, $validated['message'], null, $testMode);

        if ($result['success']) {
            return back()->with('success', "Bulk SMS sent. {$result['sent']}/{$result['total']} delivered.");
        }

        return back()->with('error', "Bulk SMS failed. {$result['failed']}/{$result['total']} failed.");
    }

    public function smsHistory(Request $request)
    {
        $query = SentMessage::where('type', 'sms');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('to', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $messages = $query->latest()->paginate(25);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function email(): View
    {
        return view('admin.communication.email');
    }

    public function whatsapp(): View
    {
        return view('admin.communication.whatsapp');
    }

    public function sendWhatsApp(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array',
            'recipients.*' => 'required|string',
            'template' => 'required|string',
            'message_type' => 'required|in:plain,personalized,media,button,scheduled',
        ]);

        $recipients = $request->input('recipients');
        $template = $request->input('template');
        $messageType = $request->input('message_type');
        $test = $request->input('test', false);

        try {
            $result = match ($messageType) {
                'plain' => $this->whatsappService->sendTextMessage($recipients, $template, $test),
                'personalized' => $this->whatsappService->sendPersonalizedMessage(
                    $recipients,
                    $template,
                    $request->input('personalisation', []),
                    $test
                ),
                'media' => $this->whatsappService->sendMediaMessage(
                    $recipients,
                    $template,
                    $request->input('media', []),
                    $request->input('reference'),
                    $test
                ),
                'button' => $this->whatsappService->sendButtonMessage(
                    $recipients,
                    $template,
                    $request->input('button_personalisation', []),
                    $test
                ),
                'scheduled' => $this->whatsappService->scheduleMessage(
                    $recipients,
                    $template,
                    $request->input('date'),
                    $request->input('time'),
                    $request->input('attributes'),
                    $request->input('repeat'),
                    $request->input('start_date'),
                    $request->input('end_date'),
                    $request->input('document'),
                    $request->input('reference')
                ),
                default => ['success' => false, 'error' => 'Invalid message type'],
            };

            return back()->with('success', 'Message sent successfully!')->with('result', $result);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send message: ' . $e->getMessage());
        }
    }

    public function testWhatsAppPage(): View
    {
        $whatsappSettings = \App\Models\WhatsAppSettings::first() ?? new \App\Models\WhatsAppSettings();
        $templates = \App\Models\WhatsAppTemplate::where('is_active', true)->get()->map(function($tmpl) {
            return [
                'name' => $tmpl->name,
                'label' => $tmpl->description ?: ucfirst(str_replace('_', ' ', $tmpl->name)),
                'parameters' => $tmpl->parameters ?? [],
            ];
        })->toArray();

        return view('admin.communication.test-whatsapp', [
            'whatsappSettings' => $whatsappSettings,
            'templates' => $templates,
        ]);
    }

    public function testWhatsApp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'template' => 'required|string',
            'personalisation' => 'nullable|array',
            'test' => 'nullable|boolean',
        ]);

        try {
            $phone = $request->input('phone');
            $template = $request->input('template');
            $personalisation = $request->input('personalisation');
            $test = $request->input('test', false);

            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (!str_starts_with($phone, '255')) {
                $phone = '255' . ltrim($phone, '0');
            }

            if ($personalisation && is_array($personalisation)) {
                $result = $this->whatsappService->sendPersonalizedMessage([$phone], $template, $personalisation, $test);
            } else {
                $result = $this->whatsappService->sendTextMessage([$phone], $template, $test);
            }

            if (isset($result['success']) && $result['success'] === false) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Failed to send test message',
                    'result' => $result,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Test message sent successfully',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test message: ' . $e->getMessage(),
            ], 500);
        }
    }
}

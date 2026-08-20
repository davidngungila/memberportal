<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Traits\FlashMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class NotificationController extends Controller
{
    use FlashMessages;

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('member-only');

        $user = Auth::user();
        $memberNumber = $user->membercode;

        $readNotifications = Session::get('read_notifications', []);
        if (! is_array($readNotifications)) {
            $readNotifications = [];
        }

        $filter = strtolower($request->input('filter', 'all'));

        // Fetch notifications from database
        $notificationsQuery = Notification::orderBy('created_at', 'desc');

        $dbNotifications = $notificationsQuery->get()->map(function ($n) {
            return [
                'id' => (string) $n->id,
                'category' => $n->category,
                'title' => $n->title,
                'message' => $n->message,
                'date' => $n->created_at->format('Y-m-d H:i:s'),
                'priority' => $n->priority,
                'is_read' => $n->is_read,
                'is_unread' => !$n->is_read,
                'read_at' => $n->read_at ? $n->read_at->format('Y-m-d H:i:s') : null,
            ];
        })->toArray();

        $enriched = $dbNotifications;

        $allUnread = array_values(array_filter($enriched, static fn(array $n): bool => ! $n['is_read']));

        if ($filter === 'unread') {
            $displayNotifications = $allUnread;
        } else {
            $displayNotifications = $enriched;
        }

        $unreadCount = count($allUnread);
        $announcementCount = count(array_filter($enriched, static fn(array $n): bool => ($n['category'] ?? '') === 'announcement'));
        $loanReminderCount = count(array_filter($enriched, static fn(array $n): bool => ($n['category'] ?? '') === 'loan'));
        $generalCount = count(array_filter($enriched, static fn(array $n): bool => ($n['category'] ?? '') === 'general'));

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'notifications' => $enriched,
                'unread_count' => $unreadCount,
                'total_count' => count($enriched),
            ]);
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'notification',
            'subject_id' => null,
            'description' => 'Member viewed notifications',
            'properties' => [
                'member_number' => $memberNumber,
                'unread_count' => $unreadCount,
                'filter' => $filter,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return view('member.notifications.index', compact(
            'displayNotifications',
            'enriched',
            'unreadCount',
            'announcementCount',
            'loanReminderCount',
            'generalCount',
            'filter',
            'readNotifications'
        ));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        Gate::authorize('member-only');

        $user = Auth::user();
        $memberNumber = $user->membercode;

        // Mark all database notifications as read for this user
        Notification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        // Also clear session read notifications
        Session::put('read_notifications', []);

        $this->success('All notifications marked as read.');

        ActivityLog::create([
            'user_id' => $user->id,
            'subject_type' => 'notification',
            'subject_id' => null,
            'description' => 'Member marked all notifications as read',
            'properties' => ['member_number' => $memberNumber],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back();
    }

    public function markRead(Request $request, string $id): RedirectResponse|JsonResponse
    {
        Gate::authorize('member-only');

        $user = Auth::user();
        $memberNumber = $user->membercode;

        if ($id === 'all') {
            // Mark all database notifications as read
            Notification::where('is_read', false)->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            Session::put('read_notifications', []);
            $this->success('All notifications marked as read.');

            ActivityLog::create([
                'user_id' => $user->id,
                'subject_type' => 'notification',
                'subject_id' => null,
                'description' => 'Member marked all notifications as read',
                'properties' => ['member_number' => $memberNumber],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } else {
            // Mark specific notification as read in database
            $notification = Notification::find($id);
            if ($notification) {
                $notification->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            }

            // Also update session
            $readNotifications = Session::get('read_notifications', []);
            if (! is_array($readNotifications)) {
                $readNotifications = [];
            }
            if (! in_array($id, $readNotifications, true)) {
                $readNotifications[] = $id;
            }
            Session::put('read_notifications', $readNotifications);

            $this->success('Notification marked as read.');

            ActivityLog::create([
                'user_id' => $user->id,
                'subject_type' => 'notification',
                'subject_id' => $id,
                'description' => "Member marked notification as read: {$id}",
                'properties' => ['member_number' => $memberNumber],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
            ]);
        }

        return redirect()->back();
    }
}

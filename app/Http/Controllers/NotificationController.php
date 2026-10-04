<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List the latest notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (Notification $notification): array => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'body' => $notification->body,
                'data' => $notification->data,
                'read_at' => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $request->user()->notifications()->unread()->count(),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'unread_count' => $request->user()->notifications()->unread()->count(),
        ]);
    }

    /**
     * Mark all notifications of the authenticated user as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->notifications()->unread()->update(['read_at' => now()]);

        return response()->json(['unread_count' => 0]);
    }
}

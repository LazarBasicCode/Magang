<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Dipanggil lewat fetch() setiap beberapa detik (polling) oleh
     * public/js/notifications.js di semua halaman yang punya lonceng
     * notifikasi. Mengembalikan notifikasi terbaru + jumlah yang belum
     * dibaca, supaya bisa dirender ulang tanpa reload halaman.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = UserNotification::where('user_id', $user->id)
            ->latest('created_at')
            ->limit(20)
            ->get();

        $unreadCount = UserNotification::where('user_id', $user->id)->unread()->count();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications->map(fn (UserNotification $n) => $this->transform($n)),
        ]);
    }

    public function markRead(Request $request, UserNotification $userNotification)
    {
        abort_unless($userNotification->user_id === $request->user()->id, 403);

        if (!$userNotification->read_at) {
            $userNotification->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        UserNotification::where('user_id', $request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    private function transform(UserNotification $n): array
    {
        return [
            'id'          => $n->id,
            'type'        => $n->type,
            'color'       => $n->color,
            'icon'        => $n->icon,
            'title'       => $n->title,
            'description' => $n->description,
            'time_rel'    => $n->created_at->diffForHumans(),
            'group'       => $n->created_at->isToday() ? 'Hari Ini' : ($n->created_at->isYesterday() ? 'Kemarin' : $n->created_at->format('d M Y')),
            'is_unread'   => is_null($n->read_at),
        ];
    }
}

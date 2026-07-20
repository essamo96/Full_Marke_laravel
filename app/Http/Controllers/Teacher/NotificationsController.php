<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function markRead(Request $request, string $id)
    {
        $notification = Auth::guard('teacher')->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        Auth::guard('teacher')->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}

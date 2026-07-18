<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function markRead(Request $request, string $id)
    {
        $notification = Auth::guard('student')->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        Auth::guard('student')->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}

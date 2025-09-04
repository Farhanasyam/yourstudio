<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'approved']);
    }
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);
        
        return view('pages.notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->markAsRead();
        
        // Redirect ke URL yang ada di notification data
        $actionUrl = $notification->data['action_url'] ?? route('notifications.index');
        
        return redirect($actionUrl);
    }

    public function markAsReadAjax(Request $request, $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'action_url' => $notification->data['action_url'] ?? null
        ]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        
        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }

    public function getNotifications()
    {
        $user = Auth::user();
        $notifications = $user->unreadNotifications()->latest()->take(5)->get();
        
        return response()->json(['notifications' => $notifications]);
    }
}

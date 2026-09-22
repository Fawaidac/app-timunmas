<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $data = NotificationService::getForCurrentUser();
        return response()->json([
            'success'       => true,
            'notifications' => $data['notifications'],
            'unread_count'  => $data['unread_count'],
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $id = $request->input('id', 'all');
        NotificationService::markAsRead($id);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai dibaca',
        ]);
    }
}

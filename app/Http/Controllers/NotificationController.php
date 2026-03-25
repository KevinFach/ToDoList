<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(): JsonResponse
    {
        $perPage = request('per_page', 20);
        $unreadOnly = filter_var(request('unread_only', true), FILTER_VALIDATE_BOOLEAN);

        $notifications = $this->notificationService->getActiveNotifications(auth()->user(), $unreadOnly, $perPage);

        return response()->json(['data' => $notifications]);
    }

    public function markRead(int $id): JsonResponse
    {
        $this->notificationService->markRead(auth()->user(), $id);

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function dismiss(int $id): JsonResponse
    {
        $this->notificationService->dismiss(auth()->user(), $id);

        return response()->json(['message' => 'Notification dismissed']);
    }
}

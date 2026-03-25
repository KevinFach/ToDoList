<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;

class NotificationService
{
    public function generateOverdueNotifications(User $user): void
    {
        $tasks = Task::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->where('status', '<>', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        foreach ($tasks as $task) {
            $daysOverdue = now()->diffInDays($task->due_date);

            $exists = Notification::where('task_id', $task->id)
                ->where('type', 'overdue')
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            Notification::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'type' => 'overdue',
                'message' => sprintf("Task '%s' is overdue by %d days", $task->title, $daysOverdue),
                'data' => [
                    'days_overdue' => $daysOverdue,
                    'task_title' => $task->title,
                    'priority' => $task->priority,
                ],
            ]);
        }
    }

    public function getActiveNotifications(User $user, bool $unreadOnly = true, int $perPage = 20)
    {
        $query = Notification::where('user_id', $user->id);

        if ($unreadOnly) {
            $query->whereNull('read_at')->whereNull('dismissed_at');
        }

        return $query->orderByDesc('data->days_overdue')->orderByRaw("FIELD(priority, 'High', 'Medium', 'Low')")->paginate($perPage);
    }

    public function markRead(User $user, int $id): void
    {
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);
        $notification->update(['read_at' => now()]);
    }

    public function dismiss(User $user, int $id): void
    {
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);
        $notification->update(['dismissed_at' => now()]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $task = Task::factory()->create();

        return [
            'user_id' => $task->user_id,
            'task_id' => $task->id,
            'type' => 'overdue',
            'message' => "Task '{$task->title}' is overdue by 1 day",
            'data' => ['days_overdue' => 1, 'task_title' => $task->title, 'priority' => $task->priority],
            'read_at' => null,
            'dismissed_at' => null,
        ];
    }
}

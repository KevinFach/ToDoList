<?php

namespace App\Services;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;

class TaskService
{
    public function createTask(array $data): Task
    {
        $data['status'] = TaskStatus::Pending->value;
        $data['priority'] = $data['priority'] ?? TaskPriority::Medium->value;

        return Task::create($data);
    }

    public function updateTask(Task $task, array $data): Task
    {
        if (isset($data['status']) && ! TaskStatus::from($data['status'])->canTransitionTo(TaskStatus::from($task->status->value))) {
            // we keep update status through the explicit endpoint.
            unset($data['status']);
        }

        $task->fill($data);

        if ($task->isDirty('status') && $task->status === TaskStatus::Completed) {
            $task->completed_at = now();
        }

        $task->save();

        return $task;
    }
}

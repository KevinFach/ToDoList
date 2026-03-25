<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_notification_is_generated(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Overdue task',
            'priority' => 'High',
            'status' => 'pending',
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->artisan('tasks:generate-overdue-notifications')->assertExitCode(0);

        $this->assertDatabaseHas('notifications', ['task_id' => $task->id, 'user_id' => $user->id, 'type' => 'overdue']);
    }
}

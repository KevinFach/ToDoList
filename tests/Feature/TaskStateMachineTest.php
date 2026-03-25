<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskStateMachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_to_in_progress_transition(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => TaskStatus::Pending]);

        $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/tasks/{$task->id}/status", ['status' => TaskStatus::InProgress->value]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => TaskStatus::InProgress]);
    }
}

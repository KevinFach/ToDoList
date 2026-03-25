<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_task(): void
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Sample task',
            'description' => 'Description',
            'priority' => 'High',
            'due_date' => now()->addDays(3)->toDateString(),
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/tasks', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['title' => 'Sample task', 'user_id' => $user->id]);
    }
}

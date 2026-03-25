<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\TaskCreateRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function __construct(private TaskService $taskService) {}

    public function index(): JsonResponse
    {
        $tasks = Task::whereNull('deleted_at')
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('priority'), fn($q) => $q->where('priority', request('priority')))
            ->orderBy(request('sort_by', 'due_date'), request('sort_direction', 'asc'))
            ->paginate(request('per_page', 15));

        return response()->json($tasks);
    }

    public function store(TaskCreateRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return response()->json(['data' => $task, 'message' => 'Task created successfully'], 201);
    }

    public function show(int $id): JsonResponse
    {
        $task = Task::whereNull('deleted_at')->find($id);
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json(['data' => $task]);
    }

    public function update(TaskUpdateRequest $request, int $id): JsonResponse
    {
        $task = Task::whereNull('deleted_at')->find($id);
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        if ($task->isCompleted()) {
            return response()->json(['message' => 'Completed tasks cannot be modified'], 403);
        }

        $updated = $this->taskService->updateTask($task, $request->validated());

        return response()->json(['data' => $updated, 'message' => 'Task updated successfully']);
    }

    public function updateStatus(\Illuminate\Http\Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', TaskStatus::values())],
        ]);

        $task = Task::whereNull('deleted_at')->find($id);
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        if ($task->isCompleted()) {
            return response()->json(['message' => 'Completed tasks cannot be modified'], 403);
        }

        $nextStatus = TaskStatus::from($request->input('status'));

        if (! $task->status->canTransitionTo($nextStatus)) {
            return response()->json(['message' => 'Invalid state transition from ' . $task->status->value . ' to ' . $nextStatus->value], 400);
        }

        $task->transitionTo($nextStatus);

        return response()->json(['data' => $task, 'message' => 'Task status updated successfully']);
    }

    public function destroy(int $id): JsonResponse
    {
        $task = Task::whereNull('deleted_at')->find($id);
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(null, 204);
    }
}

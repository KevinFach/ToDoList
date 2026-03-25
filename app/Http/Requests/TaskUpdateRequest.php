<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;

class TaskUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date' => 'nullable|date|date_format:Y-m-d',
            'priority' => 'sometimes|required|in:' . implode(',', TaskPriority::values()),
            'status' => 'sometimes|required|in:' . implode(',', TaskStatus::values()),
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required',
            'title.min' => 'Title must be at least 3 characters',
            'title.max' => 'Title cannot exceed 255 characters',
            'priority.in' => 'Priority must be Low, Medium, or High',
            'status.in' => 'Status must be pending, in_progress, or completed',
            'due_date.date' => 'Due date must be a valid date',
            'due_date.date_format' => 'Due date must be in format YYYY-MM-DD',
        ];
    }
}

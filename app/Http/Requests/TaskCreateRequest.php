<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;

class TaskCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date' => 'nullable|date|date_format:Y-m-d',
            'priority' => 'required|in:' . implode(',', TaskPriority::values()),
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required',
            'title.min' => 'Title must be at least 3 characters',
            'title.max' => 'Title cannot exceed 255 characters',
            'priority.in' => 'Priority must be Low, Medium, or High',
            'due_date.date' => 'Due date must be a valid date',
            'due_date.date_format' => 'Due date must be in format YYYY-MM-DD',
        ];
    }
}

<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
        'priority' => TaskPriority::class,
        'status' => TaskStatus::class,
    ];

    protected $attributes = [
        'status' => TaskStatus::Pending,
        'priority' => TaskPriority::Medium,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::Completed;
    }

    public function transitionTo(TaskStatus $newStatus): bool
    {
        if (! $this->status->canTransitionTo($newStatus)) {
            return false;
        }

        $this->status = $newStatus;

        if ($newStatus === TaskStatus::Completed) {
            $this->completed_at = now();
        }

        return $this->save();
    }

    public static function statuses(): array
    {
        return TaskStatus::values();
    }

    public static function priorities(): array
    {
        return TaskPriority::values();
    }
}

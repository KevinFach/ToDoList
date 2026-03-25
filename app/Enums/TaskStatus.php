<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canTransitionTo(TaskStatus $next): bool
    {
        return match ($this) {
            self::Pending => $next === self::InProgress,
            self::InProgress => $next === self::Completed,
            self::Completed => false,
        };
    }
}

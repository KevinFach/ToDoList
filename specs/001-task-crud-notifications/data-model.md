# Data Model: Task CRUD & Notifications System

**Feature**: Task CRUD & Notifications System  
**Date**: 2026-03-25  
**Database**: MySQL 8.0+ with strict mode enabled  

## Entity Relationship Diagram

```
┌─────────────────┐       ┌─────────────────────┐
│     User        │       │       Task          │
├─────────────────┤       ├─────────────────────┤
│ id (PK)         │◄──────┤ id (PK)             │
│ name            │       │ user_id (FK)        │
│ email           │       │ title               │
│ ...             │       │ description         │
└─────────────────┘       │ priority            │
                         │ status              │
                         │ due_date            │
                         │ completed_at        │
                         │ created_at          │
                         │ updated_at          │
                         │ deleted_at          │
                         └─────────────────────┘
                                  │
                                  │ 1:N
                                  ▼
                         ┌─────────────────────┐
                         │   Notification      │
                         ├─────────────────────┤
                         │ id (PK)             │
                         │ user_id (FK)        │
                         │ task_id (FK)        │
                         │ type                │
                         │ message             │
                         │ data (JSON)         │
                         │ read_at             │
                         │ dismissed_at        │
                         │ created_at          │
                         └─────────────────────┘
```

## Entity Definitions

### Task Entity

**Purpose**: Core entity representing user tasks with full lifecycle management.

**Attributes**:

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | PRIMARY KEY, NOT NULL | Unique identifier |
| `user_id` | BIGINT UNSIGNED | FOREIGN KEY → users.id, NOT NULL | Task owner |
| `title` | VARCHAR(255) | NOT NULL | Task title (3-255 chars) |
| `description` | TEXT | NULL | Task description (max 1000 chars) |
| `priority` | ENUM('Low', 'Medium', 'High') | NOT NULL, DEFAULT 'Medium' | Task priority level |
| `status` | ENUM('pending', 'in_progress', 'completed') | NOT NULL, DEFAULT 'pending' | Task state |
| `due_date` | DATE | NULL | Task deadline (YYYY-MM-DD format) |
| `completed_at` | TIMESTAMP | NULL | Completion timestamp (auto-set) |
| `created_at` | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last update timestamp |
| `deleted_at` | TIMESTAMP | NULL | Soft delete timestamp |

**Business Rules**:
- `title`: Required, 3-255 characters, trimmed whitespace
- `description`: Optional, max 1000 characters
- `priority`: Required enum, no NULL values allowed
- `status`: Required enum, starts as 'pending', strict state transitions
- `due_date`: Optional, valid date format, no future validation on creation
- `completed_at`: Auto-set on status → 'completed', NULL otherwise
- Soft delete: `deleted_at` set on delete, record preserved

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `user_id` (frequent filtering)
- INDEX on `status` (state filtering)
- INDEX on `priority` (priority filtering)
- INDEX on `due_date` (overdue queries, sorting)
- INDEX on `deleted_at` (soft delete queries)
- COMPOSITE INDEX on `(user_id, status, priority)` (admin filtering)
- COMPOSITE INDEX on `(user_id, due_date, status)` (overdue notifications)

### Notification Entity

**Purpose**: Stores overdue task notifications with read/dismiss tracking.

**Attributes**:

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | PRIMARY KEY, NOT NULL | Unique identifier |
| `user_id` | BIGINT UNSIGNED | FOREIGN KEY → users.id, NOT NULL | Notification recipient |
| `task_id` | BIGINT UNSIGNED | FOREIGN KEY → tasks.id, NOT NULL | Related task |
| `type` | ENUM('overdue') | NOT NULL | Notification type |
| `message` | VARCHAR(500) | NOT NULL | Human-readable message |
| `data` | JSON | NULL | Additional metadata (days_overdue, etc.) |
| `read_at` | TIMESTAMP | NULL | Read timestamp |
| `dismissed_at` | TIMESTAMP | NULL | Dismiss timestamp |
| `created_at` | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Creation timestamp |

**Business Rules**:
- One notification per overdue task per day (deduplication)
- `read_at` and `dismissed_at` are mutually exclusive states
- Notifications for soft-deleted tasks are not created
- Notifications for completed tasks are not created

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `user_id` (user notification queries)
- INDEX on `task_id` (task-related notifications)
- INDEX on `type` (future notification types)
- INDEX on `read_at` (unread notifications)
- INDEX on `dismissed_at` (active notifications)
- UNIQUE INDEX on `(task_id, DATE(created_at))` (daily deduplication)
- COMPOSITE INDEX on `(user_id, read_at, dismissed_at)` (active notifications)

### User Entity (Existing)

**Assumed Structure** (Laravel standard):

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | PRIMARY KEY, NOT NULL | Unique identifier |
| `name` | VARCHAR(255) | NOT NULL | User display name |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | User email address |
| `email_verified_at` | TIMESTAMP | NULL | Email verification timestamp |
| `password` | VARCHAR(255) | NOT NULL | Hashed password |
| `remember_token` | VARCHAR(100) | NULL | Remember token |
| `created_at` | TIMESTAMP | NOT NULL | Creation timestamp |
| `updated_at` | TIMESTAMP | NOT NULL | Update timestamp |

## Relationships

### Task → User (Many-to-One)
- **Foreign Key**: `tasks.user_id` → `users.id`
- **Behavior**: CASCADE on update, RESTRICT on delete
- **Purpose**: Tasks belong to users, prevent orphaned tasks

### Notification → User (Many-to-One)
- **Foreign Key**: `notifications.user_id` → `users.id`
- **Behavior**: CASCADE on update, RESTRICT on delete
- **Purpose**: Notifications belong to users

### Notification → Task (Many-to-One)
- **Foreign Key**: `notifications.task_id` → `tasks.id`
- **Behavior**: CASCADE on update, SET NULL on delete
- **Purpose**: Notifications reference tasks, allow cleanup if task deleted

## Data Integrity Rules

### Task State Machine
```php
enum TaskStatus: string {
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function canTransitionTo(self $newStatus): bool {
        return match($this) {
            self::PENDING => in_array($newStatus, [self::IN_PROGRESS]),
            self::IN_PROGRESS => in_array($newStatus, [self::COMPLETED]),
            self::COMPLETED => false, // No transitions from completed
        };
    }
}
```

### Validation Rules
- **Task Creation**: `title` required, `priority` required, `status` defaults to 'pending'
- **Task Update**: All fields except `id`, `user_id`, `created_at` can be updated
- **State Transition**: Must follow state machine rules
- **Completed Tasks**: No updates allowed after `status = 'completed'`
- **Soft Delete**: Tasks marked with `deleted_at` excluded from normal queries

## Migration Strategy

### Phase 1: Core Tables
```sql
-- Create tasks table
CREATE TABLE tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    priority ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    due_date DATE NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_due_date (due_date),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_user_status_priority (user_id, status, priority),
    INDEX idx_user_due_status (user_id, due_date, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create notifications table
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    task_id BIGINT UNSIGNED NOT NULL,
    type ENUM('overdue') NOT NULL,
    message VARCHAR(500) NOT NULL,
    data JSON NULL,
    read_at TIMESTAMP NULL,
    dismissed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_task_id (task_id),
    INDEX idx_type (type),
    INDEX idx_read_at (read_at),
    INDEX idx_dismissed_at (dismissed_at),
    UNIQUE KEY unique_task_daily (task_id, DATE(created_at))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Phase 2: Data Seeding (Development)
- Create TaskFactory with realistic data
- Create NotificationFactory for testing
- Seed 100+ tasks with various states/priorities for testing

## Performance Considerations

### Query Optimization
- **Task Listing**: Use eager loading for user relationships
- **Filtering**: Leverage composite indexes for (user_id, status, priority)
- **Overdue Checks**: Indexed query on (user_id, due_date, status)
- **Pagination**: Filament handles pagination automatically

### Expected Load
- **Read Operations**: 1000+ task listings per user session
- **Write Operations**: 100+ task updates per user session
- **Background Jobs**: Daily overdue checks (low frequency)
- **Concurrent Users**: Single-user system (no multi-tenancy concerns)

## Backup & Recovery

### Soft Delete Strategy
- Tasks recoverable for 30 days via admin interface
- Hard delete only via explicit admin command
- Audit log maintained for compliance

### Notification Cleanup
- Read/dismissed notifications auto-cleanup after 90 days
- Overdue notifications retained for task history

## Testing Data Strategy

### Factory Definitions
```php
// TaskFactory
Task::factory()->create([
    'title' => $this->faker->sentence(),
    'priority' => $this->faker->randomElement(['Low', 'Medium', 'High']),
    'status' => 'pending',
    'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
]);

// NotificationFactory
Notification::factory()->create([
    'type' => 'overdue',
    'message' => "Task '{$task->title}' is overdue by {$days} days",
    'data' => ['days_overdue' => $days],
]);
```

### Test Scenarios
- Valid state transitions (all combinations)
- Invalid state transitions (rejection)
- Soft delete behavior (queries exclude deleted)
- Overdue notification generation (date-based logic)
- Index performance (large dataset queries)
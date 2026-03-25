# API Contract: Task Management REST Endpoints

**Version**: 1.0.0
**Base URL**: `/api/v1`
**Authentication**: Bearer token (Laravel Sanctum)
**Content-Type**: `application/json`

## Task Endpoints

### GET /api/v1/tasks
**Purpose**: Retrieve paginated list of user's tasks with optional filtering.

**Query Parameters**:
- `page` (integer, optional): Page number for pagination (default: 1)
- `per_page` (integer, optional): Items per page (default: 15, max: 100)
- `status` (string, optional): Filter by status (`pending`, `in_progress`, `completed`)
- `priority` (string, optional): Filter by priority (`Low`, `Medium`, `High`)
- `overdue_only` (boolean, optional): Show only overdue tasks (default: false)
- `sort_by` (string, optional): Sort field (`due_date`, `priority`, `created_at`, `title`) (default: `due_date`)
- `sort_direction` (string, optional): Sort direction (`asc`, `desc`) (default: `asc`)

**Response**: 200 OK
```json
{
  "data": [
    {
      "id": 1,
      "title": "Complete project proposal",
      "description": "Write and review the Q1 project proposal",
      "priority": "High",
      "status": "in_progress",
      "due_date": "2026-04-15",
      "completed_at": null,
      "created_at": "2026-03-25T10:00:00Z",
      "updated_at": "2026-03-25T14:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1,
    "from": 1,
    "to": 1
  },
  "links": {
    "first": "/api/v1/tasks?page=1",
    "last": "/api/v1/tasks?page=1",
    "prev": null,
    "next": null
  }
}
```

**Error Responses**:
- `401 Unauthorized`: Invalid or missing authentication token
- `422 Unprocessable Entity`: Invalid query parameters

---

### POST /api/v1/tasks
**Purpose**: Create a new task.

**Request Body**:
```json
{
  "title": "Complete project proposal",
  "description": "Write and review the Q1 project proposal",
  "priority": "High",
  "due_date": "2026-04-15"
}
```

**Validation Rules**:
- `title`: required, string, min:3, max:255
- `description`: optional, string, max:1000
- `priority`: required, enum: `Low`, `Medium`, `High`
- `due_date`: optional, date format: YYYY-MM-DD

**Response**: 201 Created
```json
{
  "data": {
    "id": 1,
    "title": "Complete project proposal",
    "description": "Write and review the Q1 project proposal",
    "priority": "High",
    "status": "pending",
    "due_date": "2026-04-15",
    "completed_at": null,
    "created_at": "2026-03-25T10:00:00Z",
    "updated_at": "2026-03-25T10:00:00Z"
  },
  "message": "Task created successfully"
}
```

**Error Responses**:
- `400 Bad Request`: Validation failed
- `401 Unauthorized`: Invalid authentication
- `422 Unprocessable Entity`: Invalid data format

---

### GET /api/v1/tasks/{id}
**Purpose**: Retrieve a specific task by ID.

**Path Parameters**:
- `id` (integer, required): Task ID

**Response**: 200 OK
```json
{
  "data": {
    "id": 1,
    "title": "Complete project proposal",
    "description": "Write and review the Q1 project proposal",
    "priority": "High",
    "status": "in_progress",
    "due_date": "2026-04-15",
    "completed_at": null,
    "created_at": "2026-03-25T10:00:00Z",
    "updated_at": "2026-03-25T14:30:00Z"
  }
}
```

**Error Responses**:
- `401 Unauthorized`: Invalid authentication
- `403 Forbidden`: Task belongs to different user
- `404 Not Found`: Task not found or soft-deleted

---

### PUT /api/v1/tasks/{id}
**Purpose**: Update an existing task.

**Path Parameters**:
- `id` (integer, required): Task ID

**Request Body**: Same as POST, all fields optional except validation rules apply.

**Response**: 200 OK
```json
{
  "data": {
    "id": 1,
    "title": "Complete project proposal v2",
    "description": "Write and review the Q1 project proposal",
    "priority": "High",
    "status": "in_progress",
    "due_date": "2026-04-15",
    "completed_at": null,
    "created_at": "2026-03-25T10:00:00Z",
    "updated_at": "2026-03-25T15:00:00Z"
  },
  "message": "Task updated successfully"
}
```

**Business Rules**:
- Completed tasks cannot be updated (status = 'completed')
- Status transitions must follow state machine rules

**Error Responses**:
- `400 Bad Request`: Validation failed or invalid state transition
- `401 Unauthorized`: Invalid authentication
- `403 Forbidden`: Task belongs to different user or is completed
- `404 Not Found`: Task not found
- `422 Unprocessable Entity`: Invalid data format

---

### PATCH /api/v1/tasks/{id}/status
**Purpose**: Update only the task status (state transition).

**Path Parameters**:
- `id` (integer, required): Task ID

**Request Body**:
```json
{
  "status": "completed"
}
```

**Validation Rules**:
- `status`: required, enum: `pending`, `in_progress`, `completed`
- Must follow state machine rules

**Response**: 200 OK
```json
{
  "data": {
    "id": 1,
    "status": "completed",
    "completed_at": "2026-03-25T15:30:00Z",
    "updated_at": "2026-03-25T15:30:00Z"
  },
  "message": "Task status updated successfully"
}
```

**Error Responses**:
- `400 Bad Request`: Invalid state transition
- `401 Unauthorized`: Invalid authentication
- `403 Forbidden`: Task belongs to different user
- `404 Not Found`: Task not found

---

### DELETE /api/v1/tasks/{id}
**Purpose**: Soft delete a task.

**Path Parameters**:
- `id` (integer, required): Task ID

**Response**: 204 No Content

**Business Rules**:
- Task is soft-deleted (marked with deleted_at timestamp)
- Task no longer appears in normal queries
- Task can be restored via admin interface

**Error Responses**:
- `401 Unauthorized`: Invalid authentication
- `403 Forbidden`: Task belongs to different user
- `404 Not Found`: Task not found

## Notification Endpoints

### GET /api/v1/notifications
**Purpose**: Retrieve user's active notifications.

**Query Parameters**:
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 20)
- `unread_only` (boolean, optional): Show only unread notifications (default: true)

**Response**: 200 OK
```json
{
  "data": [
    {
      "id": 1,
      "task_id": 5,
      "type": "overdue",
      "message": "Task 'Complete project proposal' is overdue by 3 days",
      "data": {
        "days_overdue": 3,
        "task_title": "Complete project proposal",
        "priority": "High"
      },
      "read_at": null,
      "dismissed_at": null,
      "created_at": "2026-03-25T09:00:00Z"
    }
  ],
  "meta": {
    "unread_count": 1,
    "total_count": 1
  }
}
```

---

### PATCH /api/v1/notifications/{id}/read
**Purpose**: Mark notification as read.

**Path Parameters**:
- `id` (integer, required): Notification ID

**Response**: 200 OK
```json
{
  "message": "Notification marked as read"
}
```

---

### PATCH /api/v1/notifications/{id}/dismiss
**Purpose**: Dismiss notification (hide from active list).

**Path Parameters**:
- `id` (integer, required): Notification ID

**Response**: 200 OK
```json
{
  "message": "Notification dismissed"
}
```

## Error Response Format

All error responses follow this format:

```json
{
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "priority": ["The priority must be one of: Low, Medium, High."]
  }
}
```

## Rate Limiting

- **Authenticated endpoints**: 1000 requests per hour per user
- **Task listing**: 500 requests per hour per user
- **Task creation/update**: 200 requests per hour per user

## Versioning

- API versioning via URL path (`/api/v1/`)
- Breaking changes will increment version number
- Deprecation notices provided 3 months before removal
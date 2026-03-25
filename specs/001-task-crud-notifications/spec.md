# Feature Specification: Task CRUD & Notifications System

**Feature Branch**: `001-task-crud-notifications`  
**Created**: 2026-03-25  
**Status**: Draft  
**Input**: User description: "Necesito el detalle técnico de las siguientes funcionalidades: CRUD de Tareas con Título, descripción, fecha de vencimiento y prioridad (Baja, Media, Alta); Gestión de Estados (Pendiente, En Progreso, Completada); Filtrado por prioridad y estado en tabla de Filament; Notificaciones cuando tarea esté vencida"

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Create & Manage Tasks (Priority: P1)

As a user, I want to create new tasks with all necessary details (title, description, due date, priority) and manage them throughout their lifecycle, so that I can track all my work items efficiently.

**Why this priority**: Core CRUD functionality is the foundation that enables all other features. Users cannot manage tasks without first creating them. This is the MVP baseline.

**Independent Test**: Creating a task with all attributes is independently testable—users can see the task in a list and verify all attributes are stored correctly without needing state transitions or notifications.

**Acceptance Scenarios**:

1. **Given** the user is in the create task form, **When** user submits a valid task (title, description, due date, priority), **Then** the task is created with `pending` status and displayed in the task list.
2. **Given** a task exists in the system, **When** user updates the task title, description, or metadata, **Then** changes persist and are reflected immediately.
3. **Given** a task exists, **When** user deletes it, **Then** the task is soft-deleted (recoverable) and no longer appears in normal task queries.
4. **Given** the user attempts to save a task without a title, **When** submitted, **Then** validation error "Title is required" is displayed.
5. **Given** a task due date is set, **When** user sets an invalid date (past date for immediate tasks), **Then** validation error is shown: "Due date must be in the future".

---

### User Story 2 - Task State Transitions (Priority: P1)

As a user, I want to move tasks through defined states (Pending → In Progress → Completed) with validation checks, so that task status accurately reflects current work.

**Why this priority**: State management is critical for task visibility and workflow. Equal priority to CRUD; both are part of MVP.

**Independent Test**: State transitions can be tested independently by transitioning a single task through valid and invalid state changes, verifying each transition succeeds/fails as expected without requiring filters or notifications.

**Acceptance Scenarios**:

1. **Given** a task in `pending` state, **When** user marks it `in_progress`, **Then** state changes and timestamp of state change is recorded.
2. **Given** a task in `in_progress` state, **When** user marks it `completed`, **Then** state transitions successfully and completion timestamp is recorded.
3. **Given** a task in `pending` state, **When** user attempts to mark it `completed` directly, **Then** transition fails with error: "Task must be in progress before completion".
4. **Given** a task in `completed` state, **When** user attempts any state change, **Then** system prevents transition with message: "Completed tasks cannot be modified".
5. **Given** a task transitions to `completed`, **When** the transition occurs, **Then** the `completed_at` timestamp is automatically set to current time.

---

### User Story 3 - Filament Admin Filtering (Priority: P2)

As an admin, I want to filter tasks in the Filament admin panel by priority (High/Medium/Low) and state (Pending/In Progress/Completed), so that I can quickly locate specific tasks for management.

**Why this priority**: Admin convenience feature; filtering enables bulk operations and monitoring. P2 because it's not critical for users to create/manage individual tasks.

**Independent Test**: Filtering can be independently demonstrated by applying a filter (e.g., "Priority = High AND Status = In Progress") and verifying only matching tasks display; no need for notifications or specific task counts.

**Acceptance Scenarios**:

1. **Given** the Filament task table with mixed priority/state tasks, **When** user applies filter "Priority = High", **Then** only high-priority tasks display.
2. **Given** the task table with mixed states, **When** user applies filter "Status = Completed", **Then** only completed tasks display.
3. **Given** both filters available, **When** user applies "Priority = Medium AND Status = In Progress", **Then** only tasks matching BOTH criteria display.
4. **Given** filters applied, **When** user clears filters, **Then** all tasks (except soft-deleted) are displayed again.
5. **Given** the Filament index page, **When** table loads, **Then** default sort is by `due_date` (ascending) so nearest deadlines appear first.

---

### User Story 4 - Overdue Task Notifications (Priority: P2)

As a user, I want to receive alerts when a task is overdue (current date > due date and status ≠ completed), so that I can prioritize urgent work.

**Why this priority**: Notifications enhance user experience and task awareness. P2 because users can manually check task status; notifications are a convenience feature.

**Independent Test**: Notification system can be tested independently by creating a task with a past due date and verifying the notification triggers correctly, independent of UI filtering or other task operations.

**Acceptance Scenarios**:

1. **Given** a task with `due_date` in the past and status `pending` or `in_progress`, **When** system checks notifications (scheduled daily or on-demand), **Then** a notification is generated: "Task '[title]' is overdue by [N] days".
2. **Given** a task overdue by 3 days, **When** notification is retrieved, **Then** notification includes task ID, title, priority, and days overdue.
3. **Given** an overdue task marked `completed`, **When** system checks notifications, **Then** no overdue notification is generated for that task.
4. **Given** a task with future `due_date`, **When** system checks notifications, **Then** no overdue notification is generated even if status is `pending`.
5. **Given** multiple overdue tasks exist, **When** user views notifications, **Then** notifications are sorted by days overdue (most recent first) and grouped by priority (High > Medium > Low).

---

### Edge Cases

- What happens if a task's due date is updated to a past date after creation? (Notification should trigger immediately if status ≠ completed)
- How does system handle timezone differences when checking overdue status? (Use UTC for all date comparisons; display dates in user's timezone)
- What if a user soft-deletes a task, then restores it? (Soft-deleted tasks can be restored; overdue notifications resume if applicable)
- What happens if due_date is NULL? (Tasks with no due date cannot be marked overdue; filtering/sorting handles NULL gracefully)

---

## Requirements *(mandatory)*

### Functional Requirements

#### CRUD Operations

- **FR-001**: System MUST allow users to create a new Task with required fields: `title` (string, non-empty, max 255 chars), `description` (text, optional, max 1000 chars), `due_date` (date, optional, must be valid date), `priority` (enum: Low, Medium, High, mandatory on creation).

- **FR-002**: System MUST validate task title on creation and update: non-empty string, minimum 3 characters, maximum 255 characters. Error: "Title must be between 3 and 255 characters".

- **FR-003**: System MUST validate task due_date: if provided, must be a valid date in proper format (YYYY-MM-DD). No additional constraint on past dates at creation (user may log past tasks). Error: "Invalid date format or invalid date".

- **FR-004**: System MUST validate task priority: must be one of enum values (Low, Medium, High). Cannot be NULL. Error: "Priority must be Low, Medium, or High".

- **FR-005**: System MUST retrieve all non-soft-deleted tasks with full attributes (id, title, description, due_date, priority, status, created_at, updated_at, completed_at).

- **FR-006**: System MUST allow users to update any task attribute (title, description, due_date, priority) with same validation rules as creation. Updates MUST NOT modify `created_at`; MUST update `updated_at` timestamp.

- **FR-007**: System MUST implement soft delete: deleting a task marks it with `deleted_at` timestamp but leaves record in database. Soft-deleted tasks MUST NOT appear in standard queries unless explicitly requested via `withTrashed()`.

#### State Management

- **FR-008**: System MUST enforce Task State Machine: valid states are `pending`, `in_progress`, `completed`. Valid transitions are: `pending` → `in_progress`, `in_progress` → `completed`. Invalid transitions MUST be rejected with error: "Invalid state transition from [current] to [target]".

- **FR-009**: System MUST set task `status` to `pending` on creation (immutable default).

- **FR-010**: System MUST set `completed_at` timestamp automatically when task transitions to `completed` state. `completed_at` MUST remain NULL if task is not completed.

- **FR-011**: System MUST prevent any attribute modifications (title, description, due_date, priority) after task reaches `completed` state. Error: "Completed tasks cannot be modified".

- **FR-012**: System MUST record state change history with timestamps for audit purposes (optional detailed logging, but timestamps for each transition MUST be accurate).

#### Priority & Status Attributes

- **FR-013**: System MUST represent priority as enum (Low = 3, Medium = 2, High = 1 for numeric sorting) or dedicated enum type. Database storage MUST be consistent (string or integer, not mixed).

- **FR-014**: System MUST represent status as enum (pending, in_progress, completed) with strict validation on assignment.

#### Filament Admin Panel Filtering

- **FR-015**: Filament Task resource index MUST display tasks in a data table with columns: ID, Title, Priority, Status, Due Date, Created At.

- **FR-016**: Filament index MUST provide filter widget for Priority with options: High, Medium, Low. Filter MUST be multi-select (user can select multiple priorities at once).

- **FR-017**: Filament index MUST provide filter widget for Status with options: Pending, In Progress, Completed. Filter MUST be multi-select.

- **FR-018**: Filament index MUST default sort by `due_date` ascending (nearest deadlines first). Users MAY change sort column/order.

- **FR-019**: Filament index MUST exclude soft-deleted tasks from default view. MUST provide toggle or separate view to show trashed tasks (admin only).

- **FR-020**: Filament form (create/edit modal or page) MUST include input fields: Title (text), Description (textarea), Due Date (date picker), Priority (select dropdown).

- **FR-021**: Filament form MUST prevent editing of completed tasks. Edit button MUST be hidden or disabled for tasks with status `completed`.

#### Overdue Notifications

- **FR-022**: System MUST identify overdue tasks as: current date > due_date AND status ≠ `completed` AND task not soft-deleted. Only tasks with explicitly set due_date can be overdue.

- **FR-023**: System MUST implement notification generation (scheduled or on-demand): scan all non-completed, non-soft-deleted tasks with past due_date and generate "overdue" type notification for each.

- **FR-024**: Notification MUST include: `task_id`, `task_title`, `priority`, `due_date`, `days_overdue` (calculated as current_date - due_date), `notification_type = "overdue"`.

- **FR-025**: System MUST prevent duplicate overdue notifications for the same task on the same day. If a task is already notified as overdue, subsequent checks same day MUST NOT create new notification (idempotency).

- **FR-026**: System MUST provide endpoint/method to retrieve user's active notifications filtered by type (e.g., get all overdue notifications). Response MUST be sorted by days_overdue DESC (oldest overdue first) then by priority (High > Medium > Low).

- **FR-027**: Notification MUST expire or be marked as read/dismissed by user. Dismissed notifications SHOULD NOT re-appear in active notifications list.

### Key Entities

- **Task**: 
  - `id` (UUID or int, primary key)
  - `title` (string, not null, max 255)
  - `description` (text, nullable)
  - `priority` (enum: Low, Medium, High)
  - `status` (enum: pending, in_progress, completed)
  - `due_date` (date, nullable)
  - `completed_at` (timestamp, nullable, set on completion)
  - `created_at` (timestamp, immutable)
  - `updated_at` (timestamp, on any update)
  - `deleted_at` (timestamp, nullable, soft delete flag)
  - `user_id` (FK to User, establishes ownership)

- **Notification**:
  - `id` (UUID or int, primary key)
  - `user_id` (FK to User)
  - `task_id` (FK to Task)
  - `type` (enum: overdue, [future types])
  - `message` (string, human-readable)
  - `data` (JSON, optional metadata e.g., `{"days_overdue": 3}`)
  - `read_at` (timestamp, nullable, null = unread)
  - `dismissed_at` (timestamp, nullable, null = not dismissed)
  - `created_at` (timestamp)

- **User** (assumed existing):
  - `id` (primary key)
  - `name` (string)
  - `email` (string, unique)
  - [other auth fields as per Laravel convention]

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can create a task with all required attributes in under 30 seconds on average (including form load time). Form validation provides immediate feedback (<100ms) for invalid inputs.

- **SC-002**: Task state transitions complete within <200ms from API submission to database confirmation. State validation errors return within <100ms with clear error messages.

- **SC-003**: Filament admin panel filters load results in under 1 second for dataset of 10,000 tasks. Multi-filter combinations (Priority + Status) remain under 1 second.

- **SC-004**: Overdue notifications are generated with <5 second latency (if on-demand trigger) or within scheduled job window (nightly or hourly). Notification data includes task details retrievable within <100ms.

- **SC-005**: 100% of soft-deleted tasks remain recoverable. No hard deletes occur outside of explicit audit/compliance procedures. Task recovery via `restore()` or admin interface succeeds in <200ms.

- **SC-006**: Test coverage for Task model and state machine logic exceeds 85% (Pest PHP). All state transitions, validations, and edge cases have dedicated test cases.

- **SC-007**: Filament resource index page loads (populated with 50 tasks) in under 2 seconds with filters available from load. Pagination (if used) supports per-page limits and maintains <500ms per page load.

- **SC-008**: API endpoints (create, read, update, delete, list, filter) return responses with proper HTTP status codes (201 Created, 200 OK, 400 Bad Request, 404 Not Found, 422 Unprocessable Entity for validation errors).

- **SC-009**: User satisfaction: 90% of users successfully complete task creation and state transitions on first attempt (measured via task success rate in logs or user feedback).

---

## Assumptions

- **User Authentication**: System assumes existing Laravel authentication system (e.g., Laravel Jetstream or customauth middleware). Tasks are scoped to authenticated user via `user_id` FK.

- **Database**: MySQL 8.0+ is available with strict mode enabled. Migrations use Eloquent migrations (Laravel standard).

- **UI/Admin Framework**: Filament PHP framework is pre-installed and configured. Resources follow Filament conventions (Resource, Forms, Tables).

- **Timezone Handling**: All dates/times stored in UTC in database. User timezone preference is managed separately; display dates are formatted per user locale. Overdue calculations use UTC comparison.

- **Notification Delivery**: Notifications are stored in database (not real-time WebSocket). Retrieval via API endpoint. UI polls for notifications or user checks manually. Notification delivery method (email, SMS, push) is out of scope for this feature; database notification log is the foundation.

- **Mobile Support**: Out of scope for v1. Focus is on web UI (Filament admin panel) + API for future mobile client.

- **Bulk Operations**: Bulk delete/state update operations are out of scope v1. Single task operations are focused.

- **Soft Delete Recovery**: Soft-deleted tasks visible only to admins via dedicated "Trash" view. Regular users cannot see/recover their own soft-deleted tasks (admin approves recovery).

- **Concurrency**: Optimistic locking (versioning) is not implemented v1. Last-write-wins on concurrent updates.

---

## Constitutional Alignment

This specification adheres to the **To-Do List App Constitution v1.0.0**:

✅ **Principle I (Strict Type Hinting & Static Analysis)**: All Task model properties MUST use PHP 8.4 type hints; Service classes use return types; Filament Resources use typed properties.

✅ **Principle II (Laravel Architecture)**: Task model encapsulates logic, TaskService abstracts operations, TaskController handles HTTP, Filament Resource manages admin UI.

✅ **Principle III (Test-Driven Development)**: All FR items MUST have corresponding Pest tests. State machine transitions tested exhaustively.

✅ **Principle IV (Soft Deletes & Data Integrity)**: Soft deletes via Eloquent `SoftDeletes` trait. All queries account for soft-deleted records.

✅ **Principle V (Task State Machine & Validation States)**: State enum enforces valid transitions. Task validation rules per FR-002, FR-003, FR-004 ensure data integrity.

---


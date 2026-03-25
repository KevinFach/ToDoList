 # Tasks: Task CRUD & Notifications System

**Input**: Design documents from `/specs/001-task-crud-notifications/`  
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/  
**Feature branch**: `001-task-crud-notifications`

## Phase 1: Setup (Shared Infrastructure)

- [ ] T001 Initialize Laravel 12 project skeleton in repository root if missing (`composer create-project laravel/laravel .`) 
- [ ] T002 [P] Configure PHP 8.4 strict typing in `php.ini`, add `declare(strict_types=1);` template in `app/Providers/AppServiceProvider.php` or base files
- [ ] T003 [P] Install required composer dependencies in `composer.json`: `filament/filament`, `pestphp/pest`, `pestphp/pest-plugin-laravel` and run `composer install`
- [ ] T004 [P] Setup MySQL connection in `.env` with strict mode and create initial migration database name configuration
- [ ] T005 [P] Initialize Filament scaffolding: `php artisan filament:install` and verify `app/Filament/Resources` directory
- [ ] T006 [P] Configure Pest PHP by running `./vendor/bin/pest --init`, ensure `tests/Pest.php` exists
- [ ] T007 Configure static analysis and formatting tools: `phpstan.neon`, `phpcs.xml`, and `php-cs-fixer` in root

---

## Phase 2: Foundational (Blocking Prerequisites)

- [ ] T008 Create task migration in `database/migrations/` with fields: title, description, due_date, priority enum (Low, Medium, High), status enum (pending,in_progress,completed), completed_at, timestamps, deleted_at
- [ ] T009 Create notifications migration in `database/migrations/` with fields: user_id, task_id, type enum(overdue), message, data JSON, read_at, dismissed_at, timestamps
- [ ] T010 [P] Create `app/Enums/TaskPriority.php` and `app/Enums/TaskStatus.php` with strict values and helper methods
- [ ] T011 [P] Create `app/Models/Task.php` with `SoftDeletes`, `fillable`, status transitions methods, and relation to `User` and `Notification`
- [ ] T012 [P] Create `app/Models/Notification.php` with `fillable`, `casts`, and relation to `User` and `Task`
- [ ] T013 [P] Add database indexes in migrations for `task` on (user_id,status,priority,due_date,deleted_at)
- [ ] T014 [P] Add model policies and `TaskPolicy` for ownership and update/delete safety (`completed` lock)
- [ ] T015 [P] Configure `app/Http/Controllers/TaskController.php` and registration route in `routes/api.php`

---

## Phase 3: User Story 1 - Create & Manage Tasks (Priority: P1) 🎯 MVP

**Goal**: Implement Task CRUD with validation and soft delete.

**Independent Test**: Create task, update task, delete task and verify bounds in API.

### Tests

- [ ] T016 [P] [US1] Create Pest feature test `tests/Feature/TaskCrudTest.php` for create/read/update/delete behavior and validation rules
- [ ] T017 [P] [US1] Create Pest unit test `tests/Unit/Models/TaskTest.php` for `Task` attribute casting and soft delete behavior

### Implementation

- [ ] T018 [P] [US1] Implement `TaskCreateRequest` in `app/Http/Requests/TaskCreateRequest.php` with title/min/max validation, priority enum, due_date date format
- [ ] T019 [P] [US1] Implement `TaskUpdateRequest` in `app/Http/Requests/TaskUpdateRequest.php` with same rules, and completed state guard
- [ ] T020 [US1] Implement `TaskController::store` with creating task status pending and returning JSON 201
- [ ] T021 [US1] Implement `TaskController::show` to return task by ID, 404 if not found or soft-deleted
- [ ] T022 [US1] Implement `TaskController::update` with guard for completed status and field updates
- [ ] T023 [US1] Implement `TaskController::destroy` as soft delete, returning 204
- [ ] T024 [US1] Add Eloquent scope `scopeActive` in `Task` model that removes soft-deleted tasks

---

## Phase 4: User Story 2 - Task State Transitions (Priority: P1)

**Goal**: Enforce the state machine and transition history for tasks.

**Independent Test**: Verify state transitions and disallowed paths.

### Tests

- [ ] T025 [P] [US2] Create Pest feature test `tests/Feature/TaskStateMachineTest.php` for transitions: pending→in_progress, in_progress→completed, invalid transitions rejected
- [ ] T026 [P] [US2] Create Pest unit test `tests/Unit/Services/TaskServiceTest.php` validating `TaskStatus` `canTransitionTo()` behavior

### Implementation

- [ ] T027 [P] [US2] Create `app/Services/TaskService.php` with methods `createTask`, `updateTask`, `changeStatus` enforcing transition rules
- [ ] T028 [US2] Implement task status endpoint `PATCH /api/v1/tasks/{id}/status` in `TaskController` with state validation and computed `completed_at`
- [ ] T029 [US2] In `Task` model, implement `isCompleted` helper and `setCompletedAt` logic for phase change
- [ ] T030 [US2] Implement event `TaskStatusChanged` and listener to write audit log to `storage/logs/task_status.log` or dedicated `task_history` table if chosen

---

## Phase 5: User Story 3 - Filament Admin Filtering (Priority: P2)

**Goal**: Add Filament table filters by priority and status, with default due_date ordering.

**Independent Test**: Apply filters in Filament and validate output.

### Tests

- [ ] T031 [P] [US3] Create Filament test `tests/Integration/FilamentResourceTest.php` to verify filters for priority and status and default sort order

### Implementation

- [ ] T032 [P] [US3] Implement `app/Filament/Resources/TaskResource.php` with `Table::make()->filters([...])` for priority and status
- [ ] T033 [US3] Implement `Table::make()->columns([...])` with fields ID, title, priority, status, due_date, created_at
- [ ] T034 [US3] In TaskResource table set default sort `due_date` ascending
- [ ] T035 [US3] Add `action` on each row to disable edit for completed tasks (and if status completed hide edit action)
- [ ] T036 [US3] Add ability to view trashed tasks requirement in TaskResource using `withTrashed()` toggle

---

## Phase 6: User Story 4 - Overdue Task Notifications (Priority: P2)

**Goal**: Generate overdue notifications for tasks past due and provide notification API.

**Independent Test**: Create overdue tasks and verify notification generation endpoint returns data.

### Tests

- [ ] T037 [P] [US4] Create Pest feature test `tests/Feature/NotificationTest.php` for overdue generation and endpoint responses
- [ ] T038 [P] [US4] Create Pest unit test `tests/Unit/Models/NotificationTest.php` for read/dismiss behavior and non-duplicate logic

### Implementation

- [ ] T039 [P] [US4] Implement `app/Services/NotificationService.php` with `generateOverdueNotifications(User $user)` and `getActiveNotifications(User $user)`
- [ ] T040 [US4] Create `app/Jobs/CheckOverdueTasks.php` scheduled daily by `app/Console/Kernel.php`
- [ ] T041 [US4] Add Artisan command `app/Console/Commands/GenerateOverdueNotifications.php` for manual trigger
- [ ] T042 [US4] Add API endpoints in `routes/api.php`: `GET /api/v1/notifications`, `PATCH /api/v1/notifications/{id}/read`, `PATCH /api/v1/notifications/{id}/dismiss`
- [ ] T043 [US4] Implement `NotificationController` in `app/Http/Controllers/` with read/dismiss handlers
- [ ] T044 [US4] Ensure notification deduplication using `task_id` + `created_at` date unique or dedup logic in service

---

## Final Phase: Polish & Cross-Cutting Concerns

- [ ] T045 [P] Implement API validation error formatting uniform response in `app/Exceptions/Handler.php`
- [ ] T046 Implement localization strings in `resources/lang/en/validation.php` for messages such as `Title is required`, `Invalid state transition`
- [ ] T047 [P] Add Swagger/OpenAPI docs for all task and notification endpoints via Scribe or manual docs
- [ ] T048 [P] Add GitHub CI workflow `.github/workflows/pest.yml` to run `composer install`, `php artisan migrate --env=testing`, and `vendor/bin/pest`
- [ ] T049 Run static analysis `./vendor/bin/phpstan analyse` and fix findings
- [ ] T050 Run formatting `./vendor/bin/php-cs-fixer fix` and ensure PSR-12 compliance

---

## Dependencies

1. **Foundation**: Phase 1 and Phase 2 must complete before story implementation begins.
2. **Story order**: US1 and US2 are MVP and should be done before US3 and US4, though US3 and US4 can run in parallel once US1 is stable.
3. US2 state machine depends on US1 task model and CRUD endpoints.
4. US4 notifications depend on task due_date + status logic from US1/US2.

## Dependency Graph

- Phase 1 → Phase 2 → (US1, US2) → (US3, US4) → Final Phase
- Critical path: Task model + controllers (US1) → state logic (US2) → notifications (US4)

## Parallel Execution Examples

- **P** tasks (no interdependency)
  - T003/T004/T005/T006/T007
  - T010/T011/T012/T013/T014
  - T016/T017, T025/T026, T031/T037/T038
  - T032/T033/T034 and T039/T040/T041

- **Within US phases**: Testing tasks before implementation tasks to align with TDD (e.g., T016/T017 precede T018-T024)

## Independent Test Criteria per Story

- **US1**: Able to create/read/update/delete tasks via API with proper validation and soft delete.
- **US2**: State transitions allowed for valid flows, prohibited for invalid ones; completed lock in place.
- **US3**: Filament status+priority filters produce correct subset and default sort.
- **US4**: Overdue tasks generate notifications and API endpoints return/mark them appropriately.

## Total Tasks

- 50 tasks total
- US1: 9 tasks
- US2: 6 tasks
- US3: 6 tasks
- US4: 8 tasks
- Setup: 7 tasks
- Foundational: 8 tasks
- Final Phase: 6 tasks

## Format Validation

All tasks follow checklist format with:
- `[ ]` prefix
- Sequential ID T001–T050
- `[P]` flag for parallelizable tasks
- `[US1]`–`[US4]` labels in story tasks
- File paths included in descriptions

---

## MVP Suggestion

Deliver US1 + US2 first as MVP (Task CRUD + State Machine).  
Once stable and tested, add US3 Filament filtering and US4 overdue notifications.

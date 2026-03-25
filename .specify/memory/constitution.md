<!--
╔════════════════════════════════════════════════════════════════════════════╗
║                 SYNC IMPACT REPORT - Constitution 1.0.0                   ║
╚════════════════════════════════════════════════════════════════════════════╝

VERSION CHANGE: Empty Template → 1.0.0 (MINOR)
RATIONALE: Initial project constitution with full principle framework and governance model

MODIFIED PRINCIPLES:
  • [NEW] I. Strict Type Hinting & Static Analysis (Added)
  • [NEW] II. Laravel Architecture & Design Patterns (Added)
  • [NEW] III. Test-Driven Development with Pest PHP (Added)
  • [NEW] IV. Soft Deletes & Data Integrity (Added) — Best Practice #1
  • [NEW] V. Task State Machine & Validation States (Added) — Best Practice #2 & #3

ADDED SECTIONS:
  ✓ Technology Stack & Standards (Stack: Laravel 12, PHP 8.4, Filament, MySQL, Pest)
  ✓ Development Workflow & Quality Gates (Pre-commit, review, deployment gates)
  ✓ Governance (Amendment procedures, versioning, compliance verification)

REMOVED SECTIONS: None

TEMPLATE DEPENDENCY STATUS:
  ⚠ plan-template.md: Generic template OK; consider adding PHP/Laravel context examples
  ✅ spec-template.md: No changes required (language-agnostic)
  ✅ tasks-template.md: No changes required (structure remains compatible)
  ✅ checklist-template.md: No changes required

FOLLOW-UP ACTIONS:
  → Create DEVELOPMENT.md with runtime guidance referenced in Governance
  → Review existing codebase (if any) for Constitution compliance
  → Configure CI/CD to enforce PHPStan + Pest before merge

DEFERRED PLACEHOLDERS: None remaining (all tokens replaced)

Sync Completed: 2026-03-25
═════════════════════════════════════════════════════════════════════════════
-->

# To-Do List App Constitution

## Core Principles

### I. Strict Type Hinting & Static Analysis
All PHP code MUST utilize strict type declarations (`declare(strict_types=1)`). Every function parameter, return type, and property MUST have explicit type hints using PHP 8.4 syntax. Union types and nullable types are encouraged for precision. Static analysis tools (PHPStan, Psalm) MUST pass at strict level before deployment. This eliminates runtime type ambiguity and ensures IDE support for better developer experience.

### II. Laravel Architecture & Design Patterns
All features MUST follow Laravel 12 architectural conventions: Models encapsulate business logic, Controllers handle HTTP requests, Services abstract complex operations, Repositories (optional) interface with persistence. Blade templates compose UI with components (no inline logic). Filament PHP components replace raw Blade forms. Design patterns applied: Repository, Service Locator, Dependency Injection (via Laravel Container). MVC separation is non-negotiable; domain logic lives in Models, not Controllers.

### III. Test-Driven Development with Pest PHP
All non-trivial functionality MUST be written test-first using Pest PHP. Red-Green-Refactor cycle strictly enforced: tests written → approved → tests fail → implementation → tests pass → refactor. Unit tests (Models, Services), Feature tests (API/HTTP endpoints), and Integration tests (multi-component flows) required. Coverage target: >80% on business logic. Every test MUST have descriptive naming (`it_validates_task_title_length()`). Passing full suite is a deployment gate.

### IV. Soft Deletes & Data Integrity
All persistent entities (Tasks, Categories, Users) MUST support soft deletes via Eloquent's `SoftDeletes` trait. Hard deletion (purge) allowed only for audit/compliance scenarios via explicit review. Queries MUST account for soft-deleted records: use `withTrashed()` or `onlyTrashed()` when semantically appropriate. Data integrity is preserved; deleted tasks remain recoverable for data recovery or audit trails. Cascading deletes via foreign key constraints must be tested explicitly.

### V. Task State Machine & Validation States
Every Task MUST transition through defined states: `pending` → `in_progress` → `completed`. Invalid transitions trigger validation exceptions. State changes MUST pass validation checks (e.g., cannot mark complete if unassigned). Use dedicated `TaskStatus` enum or value object to prevent invalid states. Task attributes (title, description, due_date) require individual validation rules (non-empty, length limits, future dates). Validation errors return structured responses with field-level clarity.

## Technology Stack & Standards

**Backend Framework**: Laravel 12 with PHP 8.4  
**Database**: MySQL 8.0+ with strict mode enabled  
**Admin Panel**: Filament PHP (resource/form-based)  
**Testing Framework**: Pest PHP with coverage reporting  
**Package Manager**: Composer  

**Mandatory Standards**:
- Strict type mode on all PHP files
- PSR-12 code style (enforced via PHP-CS-Fixer)
- Blade components for frontend (no raw HTML generation)
- Filament schema for admin CRUD operations
- Database migrations (never raw SQL in code)
- Environment configuration via `.env` (no hardcoded values)

## Development Workflow & Quality Gates

**Pre-Commit Gates**:
- PHPStan strict analysis passes
- Pest test suite with >80% coverage must pass
- PHP-CS-Fixer formats code to PSR-12 standard
- No debugging code (`dd()`, `var_dump()`) in commits

**Code Review Requirements**:
- All PRs must include test coverage evidence
- No external library addition without team discussion
- Architecture decisions (new Service, migration refactor) require design doc

**Deployment Approvals**:
- All tests green (unit + feature + integration)
- Type checking passes (PHPStan)
- Database migrations reviewed and reversible
- Soft delete compliance verified for data models

## Governance

This Constitution supersedes all informal development practices and style guides. Amendments require:
1. Written proposal identifying principle(s) affected and rationale
2. Team consensus (minimum 2 approvals for non-PATCH changes)
3. Documentation update and version bump (semver rules apply)
4. Migration plan for existing code (deadline set per MAJOR/MINOR change)

All PRs and code reviews MUST verify Constitution compliance at merge time. Violations should be flagged in review comments with reference to specific principle number. Constitution forms the baseline for task definition, prioritization, and acceptance criteria.

For runtime guidance on specific scenarios, refer to project documentation (`DEVELOPMENT.md`). This Constitution is the source of truth for architectural governance.

**Version**: 1.0.0 | **Ratified**: 2026-03-25 | **Last Amended**: 2026-03-25

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Contractly is an AI-powered contract analysis platform. Users upload PDF contracts, which are analyzed asynchronously using the Anthropic Claude API to extract clauses, identify risks, and track deadlines.

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12+ (PHP 8.3+) |
| Frontend | Vue 3 + TypeScript + Vite |
| Database | PostgreSQL 16 |
| Cache/Queue | Redis 7 |
| Auth | Laravel Sanctum |
| Styling | Tailwind CSS |
| Containers | Docker Compose |

## Commands

All commands use Docker via Makefile:

```bash
# Setup
make setup              # First-time: env, build, up, install, migrate

# Development
make up                 # Start containers
make npm-dev            # Vite dev server (port 5173)
make logs               # View all logs

# Testing
make test               # Run Pest tests (backend)
make npm-test           # Run Vitest tests (frontend)
make check              # PHPStan + all tests

# Code Quality
make pint               # Format PHP (Laravel Pint)
make phpstan            # Static analysis (level 5)
make npm-lint           # ESLint + Prettier

# Database
make migrate            # Run migrations
make fresh              # Fresh migrate + seed
make db-reset           # Drop, migrate, seed

# Utilities
make shell              # PHP container shell
make shell-node         # Node container shell
make tinker             # Laravel REPL
```

## Architecture

### Backend Structure
```
backend/
├── app/
│   ├── Enums/              # ContractStatus, RiskLevel, ClauseType, etc.
│   ├── Http/
│   │   ├── Controllers/    # Slim controllers, delegate to services
│   │   ├── Requests/       # Form request validation
│   │   └── Resources/      # JSON API resources (always use these)
│   ├── Jobs/               # AnalyzeContractJob (async AI processing)
│   ├── Models/             # Relationships, casts, accessors (no query logic)
│   ├── Repositories/       # Data access layer
│   │   ├── Contracts/      # Repository interfaces
│   │   └── *.php           # Repository implementations
│   └── Services/           # Business logic layer
├── routes/api.php          # API endpoints
└── tests/                  # Pest tests
```

### Frontend Structure
```
frontend/src/
├── components/         # Reusable Vue components
├── composables/        # Composition API helpers (use* prefix)
├── router/             # Vue Router config
├── services/           # API client (Axios)
├── stores/             # Pinia stores
├── types/              # TypeScript interfaces
└── views/              # Page components
```

### Key Models
- **Contract** - Uploaded PDF with status tracking (UUID primary key)
- **ContractAnalysis** - AI analysis results (1:1 with Contract)
- **ContractClause** - Extracted clauses with risk levels
- **ContractDeadline** - Dates extracted from contract
- **Reminder** - Scheduled notifications

### Repository Pattern
All data access uses the Repository pattern with interfaces for testability:
```php
// Inject interface, not implementation
public function __construct(
    private ContractRepositoryInterface $contracts,
) {}

// Query via repository
$contracts = $this->contracts->getAllForUser($user);
$pending = $this->contracts->getByStatusForUser(ContractStatus::PENDING, $user);
```
- **Models:** Relationships, casts, accessors, simple state checks
- **Repositories:** All query logic, CRUD operations, complex filters
- **Services:** Business logic, orchestration

## Coding Standards

### PHP/Laravel
```php
// REQUIRED: strict types
declare(strict_types=1);

// Use Facades, not helpers
use Illuminate\Support\Facades\Auth;
$userId = Auth::id();  // NOT auth()->id()

// Always use JSON Resources for API responses
return ContractResource::make($contract);

// Use match over switch
$label = match($status) {
    ContractStatus::PENDING => 'Waiting',
    ContractStatus::COMPLETED => 'Done',
};
```

### Vue/TypeScript
```vue
<script setup lang="ts">
// ALWAYS Composition API with <script setup>
// NEVER use 'any' type

// Define explicit interfaces
interface Props {
  contract: Contract
}

// Use storeToRefs for Pinia stores
const { contracts, isLoading } = storeToRefs(store)
</script>
```

### API Response Format
```json
// Single resource
{ "data": { "id": "...", "title": "..." } }

// Collection with pagination
{ "data": [...], "meta": { "current_page": 1, "total": 72 } }

// Error
{ "message": "...", "errors": { "field": [...] }, "code": "ERROR_CODE" }
```

### Naming Conventions
| Type | Convention | Example |
|------|------------|---------|
| PHP Class | PascalCase | `ContractAnalysis` |
| PHP Method | camelCase | `analyzeContract()` |
| Vue Component | PascalCase | `ContractCard.vue` |
| Composable | use prefix | `useContracts.ts` |
| Event handler | handle prefix | `handleSubmit` |
| DB column | snake_case | `created_at` |

## Documentation

```
/.ai/guidelines/          # AI coding guidelines (read before coding)
  ├── coding-standards.md # PHP + Vue standards
  ├── api-conventions.md  # Response format, TypeScript types
  ├── security.md         # Security practices
  └── ui-design.md        # UI patterns

/docs/                    # Human documentation
  ├── features.md         # Feature specs
  ├── architecture.md     # System design
  └── api-reference.md    # API endpoints
```

## Local URLs

- Frontend: http://localhost
- API: http://localhost/api
- Email testing (Mailpit): http://localhost:8025
- Database: postgres://contractly:secret@localhost:5432/contractly

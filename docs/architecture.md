# Contractly - System Architecture

## High-Level Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                            CLIENT                                    │
│                      Vue 3 SPA (Vite)                               │
│              TypeScript + Pinia + Vue Router                         │
└─────────────────────────────┬───────────────────────────────────────┘
                              │ HTTPS / REST API
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│                           NGINX                                      │
│                  Reverse Proxy + SSL Termination                     │
└─────────────────────────────┬───────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      LARAVEL APPLICATION                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │
│  │  Controllers │  │   Services   │  │    Models    │              │
│  └──────────────┘  └──────────────┘  └──────────────┘              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │
│  │     Jobs     │  │    Events    │  │  Resources   │              │
│  └──────────────┘  └──────────────┘  └──────────────┘              │
└────────┬────────────────────┬────────────────────┬──────────────────┘
         │                    │                    │
         ▼                    ▼                    ▼
┌──────────────┐      ┌──────────────┐      ┌──────────────┐
│  PostgreSQL  │      │    Redis     │      │  S3 Storage  │
│   Database   │      │ Cache/Queue  │      │    Files     │
└──────────────┘      └──────────────┘      └──────────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │  Queue Worker    │
                    │ (php artisan     │
                    │  queue:work)     │
                    └────────┬─────────┘
                             │
                             ▼
                    ┌──────────────────┐
                    │  Anthropic API   │
                    │   (Claude)       │
                    └──────────────────┘
```

---

## Data Model

### Entity Relationship Diagram

```
┌─────────────────┐
│      users      │
├─────────────────┤
│ id              │
│ name            │
│ email           │
│ password        │
│ email_verified  │
│ created_at      │
│ updated_at      │
└────────┬────────┘
         │
         │ 1:N
         ▼
┌─────────────────┐       1:1      ┌─────────────────────┐
│    contracts    │───────────────►│ contract_analyses   │
├─────────────────┤                ├─────────────────────┤
│ id (uuid)       │                │ id                  │
│ user_id (fk)    │                │ contract_id (fk)    │
│ title           │                │ summary             │
│ original_fname  │                │ overall_risk_level  │
│ storage_path    │                │ key_findings (json) │
│ file_size       │                │ ai_model            │
│ file_hash       │                │ tokens_used         │
│ page_count      │                │ processing_time_ms  │
│ status          │                │ raw_response (json) │
│ language        │                │ created_at          │
│ error_message   │                └─────────────────────┘
│ created_at      │
│ updated_at      │
└────────┬────────┘
         │
         │ 1:N
         ├──────────────────────────┐
         ▼                          ▼
┌─────────────────────┐    ┌─────────────────────┐
│  contract_clauses   │    │ contract_deadlines  │
├─────────────────────┤    ├─────────────────────┤
│ id                  │    │ id                  │
│ contract_id (fk)    │    │ contract_id (fk)    │
│ clause_type         │    │ deadline_type       │
│ original_text       │    │ deadline_date       │
│ plain_explanation   │    │ is_recurring        │
│ risk_level          │    │ recurrence_pattern  │
│ risk_reason         │    │ description         │
│ position_index      │    │ source_text         │
│ created_at          │    │ created_at          │
└─────────────────────┘    └──────────┬──────────┘
                                      │
                                      │ 1:N (optional)
         ┌────────────────────────────┘
         ▼
┌─────────────────────┐
│     reminders       │
├─────────────────────┤
│ id                  │
│ user_id (fk)        │
│ contract_id (fk)    │
│ deadline_id (fk)    │◄── nullable
│ title               │
│ remind_at           │
│ days_before         │
│ channel             │
│ status              │
│ sent_at             │
│ error_message       │
│ created_at          │
│ updated_at          │
└─────────────────────┘
```

### Enums

**ContractStatus**
```
PENDING     - Uploaded, waiting for processing
PROCESSING  - Currently being analyzed
COMPLETED   - Analysis finished successfully
FAILED      - Analysis failed
```

**ClauseType**
```
PAYMENT           - Payment terms, pricing
TERMINATION       - Contract end conditions
LIABILITY         - Liability limitations
PENALTY           - Penalties for breach
AUTO_RENEWAL      - Automatic renewal clauses
NON_COMPETE       - Non-competition restrictions
CONFIDENTIALITY   - NDA, confidentiality terms
INDEMNIFICATION   - Indemnity clauses
DISPUTE_RESOLUTION- Arbitration, jurisdiction
OTHER             - Uncategorized clauses
```

**RiskLevel**
```
NONE    - Standard clause, no concerns
LOW     - Minor consideration needed
MEDIUM  - Notable clause, review recommended
HIGH    - Significant risk, careful review required
```

**DeadlineType**
```
PAYMENT            - Payment due date
RENEWAL            - Contract renewal date
TERMINATION_NOTICE - Notice period deadline
DELIVERY           - Delivery deadline
REVIEW_PERIOD      - Review/approval period
OTHER              - Other deadlines
```

**ReminderStatus**
```
PENDING   - Scheduled, not yet sent
SENT      - Successfully delivered
FAILED    - Delivery failed
CANCELLED - User cancelled
```

---

## Request Flows

### Contract Upload Flow

```
User                 Frontend              Backend                Queue Worker         Claude API
 │                      │                     │                        │                   │
 │  Select PDF file     │                     │                        │                   │
 ├─────────────────────►│                     │                        │                   │
 │                      │                     │                        │                   │
 │                      │  POST /api/contracts│                        │                   │
 │                      │  (multipart form)   │                        │                   │
 │                      ├────────────────────►│                        │                   │
 │                      │                     │                        │                   │
 │                      │                     │ Validate file          │                   │
 │                      │                     │ Store to S3            │                   │
 │                      │                     │ Create Contract        │                   │
 │                      │                     │ (status: pending)      │                   │
 │                      │                     │                        │                   │
 │                      │                     │ Dispatch               │                   │
 │                      │                     │ AnalyzeContractJob ───►│                   │
 │                      │                     │                        │                   │
 │                      │  201 Created        │                        │                   │
 │                      │  {id, status}       │                        │                   │
 │                      │◄────────────────────│                        │                   │
 │                      │                     │                        │                   │
 │  Show "Processing"   │                     │                        │ Update status     │
 │◄─────────────────────│                     │                        │ to PROCESSING     │
 │                      │                     │                        │                   │
 │                      │                     │                        │ Extract PDF text  │
 │                      │                     │                        │                   │
 │                      │                     │                        │  Send prompt      │
 │                      │                     │                        ├──────────────────►│
 │                      │                     │                        │                   │
 │                      │                     │                        │  Analysis result  │
 │                      │                     │                        │◄──────────────────│
 │                      │                     │                        │                   │
 │                      │                     │                        │ Parse response    │
 │                      │                     │                        │ Save Analysis     │
 │                      │                     │                        │ Save Clauses      │
 │                      │                     │                        │ Save Deadlines    │
 │                      │                     │                        │ status: COMPLETED │
 │                      │                     │                        │                   │
 │                      │ Poll: GET           │                        │                   │
 │                      │ /contracts/{id}/status                       │                   │
 │                      ├────────────────────►│                        │                   │
 │                      │                     │                        │                   │
 │                      │  {status: completed}│                        │                   │
 │                      │◄────────────────────│                        │                   │
 │                      │                     │                        │                   │
 │                      │ GET /contracts/{id} │                        │                   │
 │                      ├────────────────────►│                        │                   │
 │                      │                     │                        │                   │
 │                      │  Full analysis data │                        │                   │
 │                      │◄────────────────────│                        │                   │
 │                      │                     │                        │                   │
 │  Display results     │                     │                        │                   │
 │◄─────────────────────│                     │                        │                   │
```

### Reminder Flow

```
Scheduler                    Backend                    Mail Service
    │                           │                            │
    │  Every minute:            │                            │
    │  schedule:run             │                            │
    ├──────────────────────────►│                            │
    │                           │                            │
    │                           │ Find reminders where       │
    │                           │ remind_at <= now           │
    │                           │ AND status = PENDING       │
    │                           │                            │
    │                           │ For each reminder:         │
    │                           │ Dispatch SendReminderJob   │
    │                           │                            │
    │                           │        ┌───────────────────┤
    │                           │        │                   │
    │                           │        │  Send email       │
    │                           │        ├──────────────────►│
    │                           │        │                   │
    │                           │        │  Success/Failure  │
    │                           │        │◄──────────────────│
    │                           │        │                   │
    │                           │        │  Update status    │
    │                           │        │  SENT or FAILED   │
    │                           │        │                   │
```

---

## Backend Architecture

### Directory Structure

```
backend/
├── app/
│   ├── Console/
│   │   └── Kernel.php              # Scheduler configuration
│   ├── Enums/
│   │   ├── ContractStatus.php
│   │   ├── ClauseType.php
│   │   ├── RiskLevel.php
│   │   ├── DeadlineType.php
│   │   └── ReminderStatus.php
│   ├── Events/
│   │   └── ContractAnalysisCompleted.php
│   ├── Repositories/
│   │   ├── Contracts/              # Repository interfaces
│   │   │   ├── ContractRepositoryInterface.php
│   │   │   ├── ContractAnalysisRepositoryInterface.php
│   │   │   ├── ContractClauseRepositoryInterface.php
│   │   │   ├── ContractDeadlineRepositoryInterface.php
│   │   │   └── ReminderRepositoryInterface.php
│   │   ├── BaseRepository.php      # Generic base repository
│   │   ├── ContractRepository.php
│   │   ├── ContractAnalysisRepository.php
│   │   ├── ContractClauseRepository.php
│   │   ├── ContractDeadlineRepository.php
│   │   └── ReminderRepository.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   └── PasswordResetController.php
│   │   │   ├── ContractController.php
│   │   │   ├── ReminderController.php
│   │   │   └── DashboardController.php
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginRequest.php
│   │   │   │   └── RegisterRequest.php
│   │   │   ├── StoreContractRequest.php
│   │   │   └── StoreReminderRequest.php
│   │   └── Resources/
│   │       ├── ContractResource.php
│   │       ├── ContractAnalysisResource.php
│   │       ├── ClauseResource.php
│   │       ├── DeadlineResource.php
│   │       └── ReminderResource.php
│   ├── Jobs/
│   │   ├── AnalyzeContractJob.php
│   │   └── SendReminderJob.php
│   ├── Listeners/
│   │   └── NotifyUserAnalysisComplete.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Contract.php
│   │   ├── ContractAnalysis.php
│   │   ├── ContractClause.php
│   │   ├── ContractDeadline.php
│   │   └── Reminder.php
│   ├── Observers/
│   │   └── ContractObserver.php
│   └── Services/
│       ├── ContractAnalysisService.php
│       ├── PdfParserService.php
│       ├── ClaudeAiService.php
│       └── ReminderService.php
├── config/
│   └── services.php                # Anthropic API config
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── web.php
└── tests/
    ├── Feature/
    │   ├── Auth/
    │   ├── Contract/
    │   └── Reminder/
    └── Unit/
        └── Services/
```

### Repository Layer

Repositories encapsulate all data access logic, providing a clean abstraction between services and the database. All repositories implement interfaces for dependency injection and easy mocking in tests.

**BaseRepository**
- Generic CRUD operations (find, all, create, update, delete)
- Query builder access via `query()` method
- Helper methods for resolving model IDs

**ContractRepository**
- User-scoped contract queries
- Filtering by status
- Search functionality
- Pagination support

**ContractAnalysisRepository**
- Find analysis by contract
- Load with clauses/deadlines
- Filter by risk level

**ContractClauseRepository**
- Filter clauses by type and risk level
- Get high-risk clauses
- Count clauses by risk level

**ContractDeadlineRepository**
- Get upcoming/past/overdue deadlines
- User-scoped deadline queries
- Filter by deadline type

**ReminderRepository**
- Get due reminders for sending
- User-scoped queries
- Status management (mark as sent/failed/cancelled)

### Service Layer

Services contain business logic and orchestrate operations across repositories.

**ContractAnalysisService**
- Orchestrates the full analysis pipeline
- Coordinates PDF parsing and AI analysis
- Uses repositories to save results

**PdfParserService**
- Extracts text from PDF files
- Handles multi-page documents
- Returns clean, structured text

**ClaudeAiService**
- Manages Anthropic API communication
- Constructs analysis prompts
- Parses structured responses
- Handles rate limiting and errors

**ReminderService**
- Creates and manages reminders
- Finds due reminders via repository
- Handles reminder delivery

---

## Frontend Architecture

### Directory Structure

```
frontend/
├── src/
│   ├── assets/
│   │   └── styles/
│   │       └── main.css
│   ├── components/
│   │   ├── common/
│   │   │   ├── AppHeader.vue
│   │   │   ├── AppFooter.vue
│   │   │   ├── LoadingSpinner.vue
│   │   │   ├── ErrorMessage.vue
│   │   │   └── Modal.vue
│   │   ├── contracts/
│   │   │   ├── ContractUploader.vue
│   │   │   ├── ContractList.vue
│   │   │   ├── ContractCard.vue
│   │   │   └── UploadProgress.vue
│   │   ├── analysis/
│   │   │   ├── AnalysisProgress.vue
│   │   │   ├── AnalysisSummary.vue
│   │   │   ├── ClauseList.vue
│   │   │   ├── ClauseCard.vue
│   │   │   ├── RiskBadge.vue
│   │   │   ├── RiskOverview.vue
│   │   │   └── DeadlineList.vue
│   │   └── reminders/
│   │       ├── ReminderForm.vue
│   │       ├── ReminderList.vue
│   │       └── ReminderCard.vue
│   ├── composables/
│   │   ├── useAuth.ts
│   │   ├── useContracts.ts
│   │   └── useReminders.ts
│   ├── router/
│   │   └── index.ts
│   ├── services/
│   │   └── api.ts
│   ├── stores/
│   │   ├── auth.ts
│   │   ├── contracts.ts
│   │   └── reminders.ts
│   ├── types/
│   │   ├── auth.ts
│   │   ├── contract.ts
│   │   └── reminder.ts
│   ├── views/
│   │   ├── auth/
│   │   │   ├── LoginView.vue
│   │   │   ├── RegisterView.vue
│   │   │   └── ForgotPasswordView.vue
│   │   ├── DashboardView.vue
│   │   ├── ContractUploadView.vue
│   │   ├── ContractDetailView.vue
│   │   └── RemindersView.vue
│   ├── App.vue
│   └── main.ts
├── tests/
│   ├── unit/
│   └── e2e/
├── index.html
├── vite.config.ts
├── tsconfig.json
└── tailwind.config.js
```

### State Management

```
┌─────────────────────────────────────────────────────────┐
│                      Vue Application                     │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │
│  │  authStore  │  │contractStore│  │reminderStore│    │
│  ├─────────────┤  ├─────────────┤  ├─────────────┤    │
│  │ user        │  │ contracts   │  │ reminders   │    │
│  │ token       │  │ current     │  │ upcoming    │    │
│  │ isAuth      │  │ isLoading   │  │ isLoading   │    │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘    │
│         │                │                │            │
│         └────────────────┼────────────────┘            │
│                          │                             │
│                          ▼                             │
│                   ┌─────────────┐                      │
│                   │  API Layer  │                      │
│                   │  (axios)    │                      │
│                   └──────┬──────┘                      │
│                          │                             │
└──────────────────────────┼─────────────────────────────┘
                           │
                           ▼
                    ┌─────────────┐
                    │ Backend API │
                    └─────────────┘
```

---

## Docker Architecture

### Container Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        Docker Network                            │
│                         (contractly)                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐                                               │
│  │    nginx     │ :80 ──────────────────────────────► Host :80  │
│  │              │ Routes /api/* to app:9000                     │
│  │              │ Routes /* to node:5173 (dev)                  │
│  └──────┬───────┘                                               │
│         │                                                        │
│         ├────────────────┐                                       │
│         ▼                ▼                                       │
│  ┌──────────────┐ ┌──────────────┐                              │
│  │     app      │ │     node     │ :5173 (HMR)                  │
│  │  (php-fpm)   │ │   (vite)     │                              │
│  │   :9000      │ │              │                              │
│  └──────┬───────┘ └──────────────┘                              │
│         │                                                        │
│         ├──────────────────┬────────────────┐                    │
│         ▼                  ▼                ▼                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐           │
│  │   postgres   │  │    redis     │  │   mailhog    │           │
│  │    :5432     │  │    :6379     │  │ :1025 (SMTP) │           │
│  │              │  │              │  │ :8025 (Web)  │           │
│  └──────────────┘  └──────────────┘  └──────────────┘           │
│         │                                                        │
│         ▼                                                        │
│  ┌──────────────┐                                               │
│  │    queue     │ (php artisan queue:work)                      │
│  │  (php-fpm)   │                                               │
│  └──────────────┘                                               │
│                                                                  │
│  ┌──────────────┐                                               │
│  │  scheduler   │ (cron + php artisan schedule:run)             │
│  │  (php-fpm)   │                                               │
│  └──────────────┘                                               │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘

Volumes:
  - postgres_data: PostgreSQL persistent data
  - redis_data: Redis persistent data (optional)
  - ./backend: Laravel source (bind mount)
  - ./frontend: Vue source (bind mount)
```

---

## Security Considerations

### Authentication
- Sanctum SPA authentication with CSRF tokens
- HTTP-only cookies for session
- Rate limiting on login attempts

### File Handling
- Validate MIME type and extension
- Limit file size (10MB)
- Store outside web root
- Generate random storage paths

### API Security
- Rate limiting per user/IP
- Input validation on all endpoints
- SQL injection prevention via Eloquent
- XSS prevention via Vue's default escaping

### Data Protection
- Encrypt contracts at rest (optional)
- Soft delete for audit trail
- User can only access own data

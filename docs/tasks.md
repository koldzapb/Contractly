# Contractly - Development Tasks

## Status Legend

| Status | Icon | Description |
|--------|------|-------------|
| BACKLOG | 📋 | Identified, not yet planned for current work |
| TODO | ⬜ | Planned, ready to start |
| IN PROGRESS | 🔄 | Currently being worked on |
| BLOCKED | ⏸️ | Waiting on dependency or issue |
| DONE | ✅ | Completed |

---

## Phase 0: Project Setup

### Documentation
| Status | Task |
|--------|------|
| ✅ | Create project directory structure |
| ✅ | Create AI guidelines (`.ai/guidelines/`) |
| ✅ | Create documentation (`docs/`) |
| ✅ | Create Claude Code skills (`.claude/skills/`) |
| ✅ | Create Cursor rules (`.cursor/rules/`) |
| ✅ | Create `.gitignore` |
| ✅ | Initialize Git repository |

### Docker Setup
| Status | Task |
|--------|------|
| ✅ | Create `docker-compose.yml` |
| ✅ | Create PHP-FPM Dockerfile |
| ✅ | Create Nginx configuration |
| ✅ | Create Node Dockerfile |
| ✅ | Create environment files (`.env.example`) |
| ✅ | Create `Makefile` with commands |
| ✅ | Verify all containers start correctly |

### Backend Setup (Laravel)
| Status | Task |
|--------|------|
| ✅ | Initialize Laravel project |
| ✅ | Install and configure Laravel Boost |
| ✅ | Configure PostgreSQL connection |
| ✅ | Configure Redis (cache/queue/sessions) |
| ✅ | Configure Mailpit for local email |
| ✅ | Install Laravel Sanctum |
| ✅ | Install PDF parsing package |
| ✅ | Configure Anthropic HTTP client |
| ✅ | Configure Laravel Pint |
| ✅ | Configure Pest for testing |
| ✅ | Create base test configuration |

### Frontend Setup (Vue)
| Status | Task |
|--------|------|
| ✅ | Initialize Vue 3 project with Vite |
| ✅ | Configure TypeScript (strict mode) |
| ✅ | Install Vue Router |
| ✅ | Install Pinia |
| ✅ | Install Axios |
| ✅ | Install and configure Tailwind CSS |
| ✅ | Install Heroicons |
| ✅ | Configure Vitest |
| ✅ | Configure Playwright |
| ✅ | Create base component structure |
| ✅ | Set up API service layer |
| ✅ | Create TypeScript types from API contract |
| ✅ | Implement dark theme support |

---

## Phase 1: Authentication

### Backend
| Status | Task |
|--------|------|
| ✅ | Verify User model exists |
| ✅ | Create RegisterController |
| ✅ | Create LoginController |
| ✅ | Create LogoutController |
| ✅ | Create PasswordResetController |
| ✅ | Create RegisterRequest |
| ✅ | Create LoginRequest |
| ✅ | Configure Sanctum for SPA |
| ✅ | Create API routes |
| ✅ | Write registration test |
| ✅ | Write login test |
| ✅ | Write logout test |
| ✅ | Write password reset test |

### Frontend
| Status | Task |
|--------|------|
| ✅ | Create auth store (Pinia) |
| ✅ | Create LoginView |
| ✅ | Create RegisterView |
| ✅ | Create ForgotPasswordView |
| ✅ | Create LoginForm component |
| ✅ | Create RegisterForm component |
| ✅ | Configure route guards |
| ✅ | Handle auth state persistence |
| ✅ | Write auth store tests |
| ✅ | Write auth component tests |

---

## Phase 2: Contract Upload & Storage

### Backend
| Status | Task |
|--------|------|
| ✅ | Create contracts migration |
| ✅ | Create contract_analyses migration |
| ✅ | Create contract_clauses migration |
| ✅ | Create contract_deadlines migration |
| ✅ | Create reminders migration |
| ✅ | Create ContractStatus enum |
| ✅ | Create ClauseType enum |
| ✅ | Create RiskLevel enum |
| ✅ | Create DeadlineType enum |
| ✅ | Create ReminderStatus enum |
| ✅ | Create Contract model |
| ✅ | Create ContractAnalysis model |
| ✅ | Create ContractClause model |
| ✅ | Create ContractDeadline model |
| ✅ | Create Reminder model |
| ✅ | Create Repository interfaces |
| ✅ | Create Repository implementations |
| ✅ | Create RepositoryServiceProvider |
| ✅ | Create ContractController |
| ✅ | Create ContractUploadService |
| ✅ | Create StoreContractRequest |
| ✅ | Create ContractResource |
| ✅ | Configure file storage (S3/local) |
| ✅ | Write upload contract test |
| ✅ | Write list contracts test |
| ✅ | Write view contract test |
| ✅ | Write delete contract test |

### Frontend
| Status | Task |
|--------|------|
| ✅ | Create contracts store |
| ✅ | Create ContractUploadView |
| ✅ | Create ContractUploader component |
| ✅ | Create UploadProgress component |
| ✅ | Create ContractList component |
| ✅ | Create ContractCard component |
| ✅ | Implement file validation |
| ✅ | Handle upload errors |
| ✅ | Write contracts store tests |
| ✅ | Write upload component tests |

---

## Phase 3: AI Analysis

### Backend
| Status | Task |
|--------|------|
| ✅ | Create PdfParserService |
| ✅ | Implement PDF text extraction |
| ✅ | Handle multi-page documents |
| ✅ | Add error handling for corrupt files |
| ✅ | Create ClaudeAiService |
| ✅ | Configure Anthropic API client |
| ✅ | Create analysis prompt template |
| ✅ | Parse structured AI response |
| ✅ | Handle API errors and rate limits |
| ✅ | Create ContractAnalysisService |
| ✅ | Orchestrate parsing + AI analysis |
| ✅ | Save analysis results |
| ✅ | Save extracted clauses |
| ✅ | Save extracted deadlines |
| ✅ | Create AnalyzeContractJob |
| ✅ | Create ContractAnalysisCompleted event |
| ✅ | Configure queue worker |
| ✅ | Write PDF parsing tests |
| ✅ | Write AI service tests (mocked) |
| ✅ | Write analysis job tests |
| ✅ | Write integration tests |

### Frontend
| Status | Task |
|--------|------|
| ✅ | Create ContractDetailView |
| ✅ | Create AnalysisProgress component |
| ✅ | Create AnalysisSummary component |
| ✅ | Create ClauseList component |
| ✅ | Create ClauseCard component |
| ✅ | Create RiskBadge component |
| ✅ | Create DeadlineList component |
| ✅ | Implement status polling |
| ✅ | Display analysis results |
| ✅ | Write analysis component tests |

---

## Phase 4: Reminders

### Backend
| Status | Task |
|--------|------|
| 📋 | Create ReminderController |
| 📋 | Create ReminderService |
| 📋 | Create StoreReminderRequest |
| 📋 | Create ReminderResource |
| 📋 | Create SendReminderJob |
| 📋 | Create reminder email template |
| 📋 | Configure scheduler for daily check |
| 📋 | Write create reminder test |
| 📋 | Write update reminder test |
| 📋 | Write delete reminder test |
| 📋 | Write send reminder test |

### Frontend
| Status | Task |
|--------|------|
| 📋 | Create reminders store |
| 📋 | Create RemindersView |
| 📋 | Create ReminderForm component |
| 📋 | Create ReminderList component |
| 📋 | Create ReminderCard component |
| 📋 | Integrate reminders with deadlines |
| 📋 | Write reminders store tests |
| 📋 | Write reminder component tests |

---

## Phase 5: Dashboard & Polish

### Backend
| Status | Task |
|--------|------|
| 📋 | Create DashboardController |
| 📋 | Implement statistics endpoint |
| 📋 | Implement upcoming deadlines endpoint |
| 📋 | Optimize queries (eager loading) |
| 📋 | Add API rate limiting |
| 📋 | Security audit |

### Frontend
| Status | Task |
|--------|------|
| 📋 | Create DashboardView |
| 📋 | Create StatsOverview component |
| 📋 | Create UpcomingDeadlines component |
| 📋 | Create RecentContracts component |
| 📋 | Responsive design review |
| 📋 | Accessibility audit (WCAG 2.1 AA) |
| 📋 | Performance optimization |
| 📋 | Write E2E tests |

---

## Phase 6: Deployment Preparation

| Status | Task |
|--------|------|
| 📋 | Create production Docker configuration |
| 📋 | Document environment variables |
| 📋 | Set up S3 storage |
| 📋 | Set up production email (Resend) |
| 📋 | Configure error tracking (Sentry) |
| 📋 | Create deployment scripts |
| 📋 | Write deployment documentation |
| 📋 | Security checklist review |
| 📋 | Performance testing |

---

## Phase 7: Document Intelligence

> **Goal:** Enhance document processing with validation (detect non-legal documents) and privacy protection (PII redaction before AI analysis).

### Phase 7A: Document Validation - Backend
| Status | Task |
|--------|------|
| 📋 | Create `DocumentType` enum with legal/non-legal classification |
| 📋 | Create migration to add validation fields to `contract_analyses` |
| 📋 | Update `AiAnalysisResult` DTO with classification fields |
| 📋 | Update `ClaudeAiService` prompt for document classification |
| 📋 | Handle classification-first response parsing |
| 📋 | Update `ContractAnalysisService` for validation flow |
| 📋 | Add "Analyze Anyway" override endpoint |
| 📋 | Update `ContractAnalysisResource` with new fields |
| 📋 | Write document classification tests (various doc types) |
| 📋 | Write integration tests for rejection flow |

### Phase 7B: Document Validation - Frontend
| Status | Task |
|--------|------|
| 📋 | Add TypeScript types for document classification |
| 📋 | Create `InvalidDocumentNotice` component |
| 📋 | Create `DocumentTypeBadge` component |
| 📋 | Create `DocumentWarningBanner` component |
| 📋 | Update `ContractDetailView` for rejection state |
| 📋 | Implement "Analyze Anyway" button flow |
| 📋 | Update contracts store for new response fields |
| 📋 | Write validation component tests |
| 📋 | Write integration tests for full flow |

### Phase 7C: PII Redaction - Backend
| Status | Task |
|--------|------|
| 📋 | Create `PiiType` enum (ssn, email, phone, credit_card, etc.) |
| 📋 | Create `PiiDetectorService` with regex patterns |
| 📋 | Create migration for redaction fields on `contracts` |
| 📋 | Create `DetectedPii` DTO for API response |
| 📋 | Create endpoint: `POST /contracts/{id}/detect-pii` |
| 📋 | Create endpoint: `POST /contracts/{id}/apply-redactions` |
| 📋 | Store extracted text separately from redacted text |
| 📋 | Update `ContractAnalysisService` to use redacted text |
| 📋 | Update `ContractResource` with redaction metadata |
| 📋 | Write PII detection tests (all pattern types) |
| 📋 | Write redaction application tests |

### Phase 7D: PII Redaction - Frontend
| Status | Task |
|--------|------|
| 📋 | Add TypeScript types for PII detection/redaction |
| 📋 | Create `RedactionEditor` component (main interface) |
| 📋 | Create `PiiHighlight` component (highlighted text spans) |
| 📋 | Create `RedactionControls` component (toggle/bulk actions) |
| 📋 | Create `RedactionPreview` component (before/after view) |
| 📋 | Create `RedactionReviewView` page (upload → review flow) |
| 📋 | Update upload flow to include optional redaction step |
| 📋 | Handle "Skip Redaction" vs "Review & Redact" paths |
| 📋 | Write redaction editor component tests |
| 📋 | Write E2E tests for redaction flow |

---

### Phase 7 Design Decisions

#### Document Classification

**Document Types:**
| Category | Types | Behavior |
|----------|-------|----------|
| **Legal (Full Analysis)** | `contract`, `amendment`, `nda` | Full clause/deadline extraction |
| **Pre-contractual (Analyze + Warn)** | `mou`, `loi`, `term_sheet` | Analyze but warn about non-binding nature |
| **Non-legal (Reject)** | `invoice`, `receipt`, `letter`, `policy`, `manual`, `form`, `report`, `unknown` | Show rejection notice, offer override |

**Edge Cases Handled:**
- Mixed documents (contract + invoice attachment) → Classify by primary intent
- Templates with placeholders → Detect and warn
- Amendments/addendums → Classify as `amendment`, note parent context needed
- Partial documents (signature page only) → Warn about incompleteness
- Non-English → Still analyze, note language in response

**AI Response Schema Addition:**
```json
{
  "document_classification": {
    "is_legal_document": true,
    "document_type": "contract",
    "confidence": 0.95,
    "rejection_reason": null,
    "warnings": []
  },
  // ... existing fields only if is_legal_document=true
}
```

#### PII Redaction

**Detection Patterns (High Confidence):**
| Type | Pattern | Example |
|------|---------|---------|
| SSN | `\d{3}-\d{2}-\d{4}` | 123-45-6789 |
| Credit Card | `\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}` | 4111-1111-1111-1111 |
| Email | Standard email regex | john@example.com |
| Phone (US) | Multiple formats | (555) 123-4567 |
| Bank Routing | 9 digits in context | 021000021 |

**Redaction Format (Type-Preserving):**
- `$150,000` → `[REDACTED_AMOUNT]` (AI knows there was a monetary value)
- `john@example.com` → `[REDACTED_EMAIL]`
- `123-45-6789` → `[REDACTED_SSN]`

**Privacy Flow:**
1. PDF uploaded → stored as-is (user's file)
2. Text extracted → stored temporarily
3. User reviews PII → selects redactions
4. Redacted text → stored and sent to AI
5. Original text → discarded (only PDF retained)

**Database Schema:**
```sql
-- contracts table additions
extracted_text TEXT NULL,
redacted_text TEXT NULL,
has_redactions BOOLEAN DEFAULT FALSE,
redaction_metadata JSONB NULL

-- contract_analyses table additions
is_legal_document BOOLEAN DEFAULT TRUE,
document_type VARCHAR(50) DEFAULT 'contract',
document_type_confidence DECIMAL(3,2) NULL,
rejection_reason TEXT NULL,
classification_warnings JSONB NULL,
analyzed_with_override BOOLEAN DEFAULT FALSE
```

---

## Current Sprint

**Sprint:** Phase 3 - AI Analysis
**Goal:** Implement PDF parsing, Claude AI integration, and contract analysis

### Active Tasks

| Status | Task | Notes |
|--------|------|-------|
| ✅ | Create PdfParserService | Text extraction using smalot/pdfparser |
| ✅ | Create ClaudeAiService | Anthropic API client with structured prompts |
| ✅ | Create ContractAnalysisService | Orchestrates parsing + AI + saving |
| ✅ | Create AnalyzeContractJob | Background job with retry logic |
| ✅ | Create Events | ContractAnalysisCompleted, ContractAnalysisFailed |
| ✅ | Add retry endpoint | POST /api/contracts/{id}/retry |
| ✅ | Write service tests | PDF, AI, Analysis service tests |
| ✅ | Write frontend analysis component tests | 7 test files, 97 tests |

### Completed Phases

| Phase | Status | Notes |
|-------|--------|-------|
| Phase 0 | ✅ Complete | Project setup, Docker, Laravel, Vue |
| Phase 1 | ✅ Complete | Authentication (Sanctum, login, register, password reset) |
| Phase 2 | ✅ Complete | Contract upload, storage, CRUD, frontend UI with tests |
| Phase 3 | ✅ Complete | AI Analysis backend complete, frontend UI complete |

### Upcoming Phases

| Phase | Status | Notes |
|-------|--------|-------|
| Phase 4 | 📋 Backlog | Reminders - email notifications for deadlines |
| Phase 5 | 📋 Backlog | Dashboard & Polish - stats, UX improvements |
| Phase 6 | 📋 Backlog | Deployment Preparation - production setup |
| Phase 7 | 📋 Backlog | Document Intelligence - validation & PII redaction |

### Blocked

| Task | Blocked By | Notes |
|------|------------|-------|
| - | - | No blockers |

---

## Progress Summary

| Phase | Total | Done | In Progress | Blocked | Todo | Backlog |
|-------|-------|------|-------------|---------|------|---------|
| Phase 0 | 32 | 32 | 0 | 0 | 0 | 0 |
| Phase 1 | 23 | 23 | 0 | 0 | 0 | 0 |
| Phase 2 | 37 | 37 | 0 | 0 | 0 | 0 |
| Phase 3 | 30 | 30 | 0 | 0 | 0 | 0 |
| Phase 4 | 19 | 0 | 0 | 0 | 0 | 19 |
| Phase 5 | 14 | 0 | 0 | 0 | 0 | 14 |
| Phase 6 | 9 | 0 | 0 | 0 | 0 | 9 |
| Phase 7 | 40 | 0 | 0 | 0 | 0 | 40 |
| **Total** | **204** | **122** | **0** | **0** | **0** | **82** |

---

## Notes

- Update status as tasks progress
- Add new tasks discovered during development
- Move BACKLOG → TODO when ready to start phase
- Document blockers with reasons
- Keep progress summary updated

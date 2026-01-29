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
| ✅ | Create ReminderController |
| ✅ | Create ReminderService |
| ✅ | Create StoreReminderRequest |
| ✅ | Create ReminderResource |
| ✅ | Create SendReminderJob |
| ✅ | Create reminder email template |
| ✅ | Configure scheduler for daily check |
| ✅ | Write create reminder test |
| ✅ | Write update reminder test |
| ✅ | Write delete reminder test |
| ✅ | Write send reminder test |

### Frontend
| Status | Task |
|--------|------|
| ✅ | Create reminders store |
| ✅ | Create RemindersView |
| ✅ | Create ReminderForm component |
| ✅ | Create ReminderList component |
| ✅ | Create ReminderCard component |
| ✅ | Integrate reminders with deadlines |
| ✅ | Write reminders store tests |
| ✅ | Write reminder component tests |

---

## Phase 5: Dashboard & Polish

### Backend
| Status | Task |
|--------|------|
| ✅ | Create DashboardController |
| ✅ | Create DashboardService |
| ✅ | Create DashboardResource |
| ✅ | Implement statistics endpoint |
| ✅ | Implement upcoming deadlines endpoint |
| ✅ | Add repository methods for dashboard data |
| ✅ | Write dashboard feature tests |
| ✅ | Add API rate limiting |
| ✅ | Security audit |

### Frontend
| Status | Task |
|--------|------|
| ✅ | Create DashboardView with real data |
| ✅ | Create StatsOverview component |
| ✅ | Create UpcomingDeadlines component |
| ✅ | Create RecentContracts component |
| ✅ | Create dashboard store |
| ✅ | Write dashboard store tests |
| ✅ | Unify header styling across views |
| ✅ | Responsive design review |
| ✅ | Accessibility audit (WCAG 2.1 AA) |
| 📋 | Performance optimization |
| ✅ | Write E2E tests |

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

## Phase 8: AI Contract Chat

> **Goal:** Enable users to have contextual conversations about their specific contracts. The AI assistant answers questions ONLY about the uploaded contract and related legal concepts—nothing else.

### Phase 8A: Backend - Data Layer
| Status | Task |
|--------|------|
| ✅ | Create `chat_messages` migration |
| ✅ | Create `ChatMessage` model with contract relationship |
| ✅ | Create `ChatMessageRepositoryInterface` |
| ✅ | Create `ChatMessageRepository` implementation |
| ✅ | Register repository in `RepositoryServiceProvider` |

### Phase 8B: Backend - Service Layer
| Status | Task |
|--------|------|
| ✅ | Create `ContractChatService` |
| ✅ | Build system prompt with contract context injection |
| ✅ | Implement conversation history management |
| ✅ | Add topic guardrails (contract-only + legal context) |
| ✅ | Handle off-topic question detection and rejection |
| ✅ | Implement token limit management for long contracts |
| 📋 | Add response streaming support (optional) |

### Phase 8C: Backend - API Layer
| Status | Task |
|--------|------|
| ✅ | Create `ContractChatController` |
| ✅ | Create `POST /contracts/{id}/chat` endpoint |
| ✅ | Create `GET /contracts/{id}/chat` endpoint (history) |
| ✅ | Create `DELETE /contracts/{id}/chat` endpoint (clear) |
| ✅ | Create `SendMessageRequest` validator |
| ✅ | Create `ChatMessageResource` |
| ✅ | Add routes to `api.php` |

### Phase 8D: Backend - Tests
| Status | Task |
|--------|------|
| ✅ | Write chat message repository tests |
| ✅ | Write contract chat service tests (mocked AI) |
| ✅ | Write guardrail tests (off-topic rejection) |
| ✅ | Write controller integration tests |
| ✅ | Write conversation context tests |

### Phase 8E: Frontend - Types & Service
| Status | Task |
|--------|------|
| ✅ | Add `ChatMessage` TypeScript interface |
| ✅ | Add `SendMessageData` interface |
| ✅ | Create `chat.ts` API service |
| ✅ | Create `useContractChat` composable |

### Phase 8F: Frontend - Components
| Status | Task |
|--------|------|
| ✅ | Create `ContractChat` container component |
| ✅ | Create `ChatMessage` component (user/assistant bubbles) |
| ✅ | Create `ChatInput` component with send button |
| ✅ | Create `ChatHistory` component (scrollable list) |
| ✅ | Create `ChatTypingIndicator` component |
| ✅ | Create `ChatEmptyState` component (suggested questions) |
| ✅ | Create `ChatErrorMessage` component |
| ✅ | Create `ChatDisclaimer` component (legal notice) |

### Phase 8G: Frontend - Integration
| Status | Task |
|--------|------|
| ✅ | Add chat panel/drawer to `ContractDetailView` |
| ✅ | Implement chat toggle button |
| ✅ | Handle loading states |
| ✅ | Implement auto-scroll on new messages |
| ✅ | Add keyboard shortcuts (Enter to send) |
| ✅ | Persist chat open/closed state |

### Phase 8H: Frontend - Tests
| Status | Task |
|--------|------|
| ✅ | Write chat composable tests |
| ✅ | Write ChatMessage component tests |
| ✅ | Write ChatInput component tests |
| ✅ | Write ContractChat integration tests |

---

### Phase 8 Design Decisions

#### User Experience Flow

```
┌─────────────────────────────────────────────────────────────┐
│  Contract Detail View                                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────────┐  ┌─────────────────────────────┐  │
│  │  Analysis Summary    │  │  💬 Ask about this contract │  │
│  │  Clauses List        │  ├─────────────────────────────┤  │
│  │  Deadlines List      │  │  ┌─────────────────────────┐│  │
│  │                      │  │  │ What are my termination ││  │
│  │                      │  │  │ options?                ││  │
│  │                      │  │  └─────────────────────────┘│  │
│  │                      │  │  ┌─────────────────────────┐│  │
│  │                      │  │  │ Based on Section 5.2... ││  │
│  │                      │  │  └─────────────────────────┘│  │
│  │                      │  │                             │  │
│  │                      │  │  [Type your question...]    │  │
│  └──────────────────────┘  └─────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

#### Allowed Topics (Guardrails)

| Category | Examples | Allowed |
|----------|----------|---------|
| **Contract-Specific** | "What's the payment schedule?", "When can I terminate?" | ✅ Yes |
| **Clause Explanation** | "Explain the indemnification clause", "What does section 3 mean?" | ✅ Yes |
| **Legal Context** | "Is this liability cap standard?", "What's typical for NDAs?" | ✅ Yes |
| **Risk Assessment** | "What are the biggest risks here?", "Should I be concerned about X?" | ✅ Yes |
| **Comparison** | "How does this compare to standard contracts?" | ✅ Yes |
| **Off-Topic** | "What's the weather?", "Write me a poem", "Help with my code" | ❌ Rejected |
| **Other Contracts** | "What about my other contract?" | ❌ Rejected |
| **Legal Advice** | "Should I sign this?" | ⚠️ Disclaimer + factual response |

#### Off-Topic Response

When user asks something unrelated:
```
"I can only help with questions about this specific contract or related
legal concepts. Here are some things I can help with:

• Explain specific clauses or terms
• Identify potential risks or concerns
• Clarify your rights and obligations
• Compare terms to industry standards

What would you like to know about your contract?"
```

#### System Prompt Structure

```
You are a contract analysis assistant for Contractly. Your role is to help
users understand their specific contract.

STRICT RULES:
1. ONLY answer questions about the contract provided below
2. ONLY answer questions about legal concepts relevant to this contract
3. NEVER provide legal advice - you explain, you don't advise
4. NEVER discuss topics unrelated to contracts or law
5. If asked about anything else, politely redirect to contract topics
6. Always cite specific sections when referencing the contract
7. Include the disclaimer when discussing risk or recommendations

DISCLAIMER (include when giving opinions):
"This is informational only, not legal advice. Consult a lawyer for
decisions about this contract."

CONTRACT TITLE: {title}
CONTRACT ANALYSIS:
- Overall Risk: {risk_level}
- Key Findings: {key_findings}

EXTRACTED CLAUSES:
{clauses_formatted}

EXTRACTED DEADLINES:
{deadlines_formatted}

FULL CONTRACT TEXT:
{contract_text}

---
Conversation History:
{history}

User: {question}
```

#### Database Schema

```sql
CREATE TABLE chat_messages (
    id UUID PRIMARY KEY,
    contract_id UUID NOT NULL REFERENCES contracts(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(20) NOT NULL, -- 'user' or 'assistant'
    content TEXT NOT NULL,
    tokens_used INTEGER NULL, -- for assistant messages
    is_off_topic BOOLEAN DEFAULT FALSE, -- track rejected questions
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX idx_chat_contract_user (contract_id, user_id),
    INDEX idx_chat_created (created_at)
);
```

#### API Design

**Send Message:**
```
POST /api/contracts/{id}/chat
{
    "message": "What are my termination options?"
}

Response 200:
{
    "data": {
        "id": "uuid",
        "role": "assistant",
        "content": "Based on Section 5.2 of your contract...",
        "created_at": "2024-01-28T12:00:00Z"
    }
}

Response 422 (validation):
{
    "message": "Message is required",
    "errors": { "message": ["The message field is required."] }
}
```

**Get History:**
```
GET /api/contracts/{id}/chat

Response 200:
{
    "data": [
        {
            "id": "uuid",
            "role": "user",
            "content": "What are my termination options?",
            "created_at": "2024-01-28T12:00:00Z"
        },
        {
            "id": "uuid",
            "role": "assistant",
            "content": "Based on Section 5.2...",
            "created_at": "2024-01-28T12:00:05Z"
        }
    ]
}
```

**Clear History:**
```
DELETE /api/contracts/{id}/chat

Response 200:
{
    "message": "Chat history cleared."
}
```

#### Suggested First Questions

Display when chat is empty to guide users:
- "What are the key obligations I need to fulfill?"
- "What happens if I want to terminate early?"
- "Are there any automatic renewal clauses?"
- "What are the payment terms?"
- "What are the highest risk clauses?"

#### Token Management

For long contracts that exceed context limits:
1. Always include: analysis summary, clauses, deadlines
2. Include relevant contract sections based on question
3. Truncate full text if needed, prioritizing beginning/end
4. Track token usage per message for monitoring

---

## Current Sprint

**Sprint:** Planning Next Phase
**Goal:** Decide next feature to implement

### Active Tasks

| Status | Task | Notes |
|--------|------|-------|
| ⬜ | Choose next feature | Contract Comparison, Export/Reports, or Search/Filters |

### Completed Phases

| Phase | Status | Notes |
|-------|--------|-------|
| Phase 0 | ✅ Complete | Project setup, Docker, Laravel, Vue |
| Phase 1 | ✅ Complete | Authentication (Sanctum, login, register, password reset) |
| Phase 2 | ✅ Complete | Contract upload, storage, CRUD, frontend UI with tests |
| Phase 3 | ✅ Complete | AI Analysis backend complete, frontend UI complete |
| Phase 4 | ✅ Complete | Reminders - email notifications for deadlines |
| Phase 5 | ✅ Complete | Dashboard with real-time stats, UI polish |
| Phase 8 | ✅ Complete | AI Contract Chat - contextual Q&A about contracts |

### Upcoming Phases

| Phase | Status | Notes |
|-------|--------|-------|
| Phase 7 | 📋 Backlog | Document Intelligence - validation & PII redaction |
| Phase 6 | 📋 Backlog | Deployment Preparation - production setup (lowest priority) |

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
| Phase 4 | 19 | 19 | 0 | 0 | 0 | 0 |
| Phase 5 | 20 | 19 | 0 | 0 | 0 | 1 |
| Phase 6 | 9 | 0 | 0 | 0 | 0 | 9 |
| Phase 7 | 40 | 0 | 0 | 0 | 0 | 40 |
| Phase 8 | 37 | 36 | 0 | 0 | 0 | 1 |
| **Total** | **247** | **196** | **0** | **0** | **0** | **51** |

---

## Notes

- Update status as tasks progress
- Add new tasks discovered during development
- Move BACKLOG → TODO when ready to start phase
- Document blockers with reasons
- Keep progress summary updated

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
| ✅ | Create `DocumentType` enum with legal/non-legal classification |
| ✅ | Create migration to add validation fields to `contracts` |
| ✅ | Create `DocumentClassificationResult` DTO with classification fields |
| ✅ | Create `DocumentValidationService` for document classification |
| ✅ | Handle classification-first response parsing |
| ✅ | Update `ContractAnalysisService` for validation flow |
| ✅ | Add "Analyze Anyway" override endpoint |
| ✅ | Update `ContractResource` with new fields |
| ✅ | Write document classification tests (various doc types) |
| ✅ | Write integration tests for rejection flow |

### Phase 7B: Document Validation - Frontend
| Status | Task |
|--------|------|
| ✅ | Add TypeScript types for document classification |
| ✅ | Create `InvalidDocumentNotice` component |
| ✅ | Create `DocumentTypeBadge` component |
| ✅ | Create `DocumentWarningBanner` component |
| ✅ | Update `ContractDetailView` for rejection state |
| ✅ | Implement "Analyze Anyway" button flow |
| ✅ | Update contracts store for new response fields |
| ✅ | Write validation component tests |
| ✅ | Write integration tests for full flow |

### Phase 7C: PII Redaction - Backend
| Status | Task |
|--------|------|
| ✅ | Create `PiiType` enum (ssn, email, phone, credit_card, etc.) |
| ✅ | Create `PiiDetectionService` with regex patterns |
| ✅ | Create migration for redaction fields on `contracts` |
| ✅ | Create `DetectedPii` DTO for API response |
| ✅ | Create endpoint: `GET /contracts/{id}/pii` |
| ✅ | Create endpoint: `POST /contracts/{id}/redact` |
| ✅ | Create `PiiDetectionResult` DTO with extraction support |
| ✅ | Update `ContractAnalysisService` to use redacted text |
| ✅ | Update `ContractResource` with redaction metadata |
| ✅ | Write PII detection tests (all pattern types) |
| ✅ | Write redaction application tests |

### Phase 7D: PII Redaction - Frontend
| Status | Task |
|--------|------|
| ✅ | Add TypeScript types for PII detection/redaction |
| ✅ | Create `RedactionEditor` component (main interface) |
| ✅ | Create `PiiHighlight` component (highlighted text spans) |
| ✅ | Create `RedactionControls` component (toggle/bulk actions) |
| ✅ | Create `RedactionPreview` component (before/after view) |
| ✅ | Create `PiiTypeBadge` component |
| ✅ | Update `ContractDetailView` to include redaction step |
| ✅ | Handle "Skip Redaction" vs "Review & Redact" paths |
| ✅ | Write redaction editor component tests |
| ✅ | Write E2E tests for redaction flow |

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

## Phase 9: Multi-Format File Support

> **Goal:** Enable users to upload contracts in additional formats beyond PDF - specifically images (JPG, PNG, WEBP) and plain text (TXT) files.

### Phase 9A: Backend - Foundation
| Status | Task |
|--------|------|
| ✅ | Create `FileType` enum (`pdf`, `image`, `text` values with labels) |
| ✅ | Create migration to add `file_type`, `mime_type` to contracts |
| ✅ | Update Contract model with new fields and casts |
| ✅ | Create `TextExtractionResult` DTO (unified result object) |
| ✅ | Create `TextExtractionInterface` (common interface for extractors) |
| ✅ | Create `UnsupportedFileTypeException` |
| ✅ | Create `TextExtractionException` |

### Phase 9B: Backend - Text Extractors
| Status | Task |
|--------|------|
| ✅ | Create `PdfTextExtractor` (wraps existing PdfParserService) |
| ✅ | Create `PlainTextExtractor` (direct file read with encoding detection) |
| ✅ | Create `ClaudeVisionService` (Vision API integration for images) |
| ✅ | Create `ImageTextExtractor` (uses ClaudeVisionService) |
| ✅ | Create `TextExtractorFactory` (selects extractor by extension) |

### Phase 9C: Backend - Integration
| Status | Task |
|--------|------|
| ✅ | Update `StoreContractRequest` to accept new file types with size limits |
| ✅ | Update `ContractUploadService` to detect and store file_type/mime_type |
| ✅ | Update `ContractAnalysisService` to use TextExtractorFactory |
| ✅ | Update `ContractResource` with file_type fields |
| ✅ | Update `ContractFactory` with file_type states |
| ✅ | Add `vision_max_tokens` config option |

### Phase 9D: Backend - Tests
| Status | Task |
|--------|------|
| ✅ | Write `PlainTextExtractor` unit tests |
| ✅ | Write `TextExtractorFactory` unit tests |
| ✅ | Write `FileType` enum tests |
| ✅ | Update `UploadContractTest` for new file types |
| ✅ | Update `ContractAnalysisServiceTest` for TextExtractorFactory |

### Phase 9E: Frontend - Updates
| Status | Task |
|--------|------|
| ✅ | Add `FileType` TypeScript type |
| ✅ | Update `Contract` interface with file_type fields |
| ✅ | Update `ContractUploader.vue` accept attribute and messaging |
| ✅ | Add file type icons to `ContractCard.vue` |
| ✅ | Update `ContractUploader.spec.ts` tests |
| ✅ | Update `contracts.spec.ts` mock objects |

---

### Phase 9 Design Decisions

#### Supported File Formats

| Format | Extension | Max Size | Extraction Method |
|--------|-----------|----------|-------------------|
| PDF | `.pdf` | 10MB | PdfParserService (existing) |
| JPEG | `.jpg`, `.jpeg` | 20MB | Claude Vision API |
| PNG | `.png` | 20MB | Claude Vision API |
| WebP | `.webp` | 20MB | Claude Vision API |
| GIF | `.gif` | 20MB | Claude Vision API |
| Plain Text | `.txt` | 5MB | Direct file read |

#### Why Claude Vision over OCR (Tesseract)
- Higher accuracy for complex contract layouts
- Same AI model for extraction + analysis = coherent understanding
- No additional server dependencies
- Incremental API cost (already using Claude)

#### Architecture

```
backend/app/Services/
├── TextExtraction/
│   ├── TextExtractionInterface.php    # Common interface
│   ├── TextExtractionResult.php       # Unified DTO
│   ├── PdfTextExtractor.php           # Wraps existing PdfParserService
│   ├── ImageTextExtractor.php         # Uses ClaudeVisionService
│   ├── PlainTextExtractor.php         # file_get_contents()
│   └── TextExtractorFactory.php       # Selects extractor by extension
└── ClaudeVisionService.php            # Claude Vision API integration
```

#### Database Schema Addition

```sql
ALTER TABLE contracts ADD COLUMN file_type VARCHAR(20) DEFAULT 'pdf';
ALTER TABLE contracts ADD COLUMN mime_type VARCHAR(100) NULL;
```

---

## Phase 10: Quick Text Analysis (Paste & Analyze)

> **Goal:** Allow users to paste contract text directly for quick analysis without file upload.

### Phase 10A: Backend
| Status | Task |
|--------|------|
| ✅ | Create `POST /contracts/text` endpoint |
| ✅ | Create `StoreTextContractRequest` validator |
| ✅ | Update `ContractUploadService` for text-only contracts |
| ✅ | Handle text contracts in analysis flow |
| ✅ | Write text contract tests |

### Phase 10B: Frontend
| Status | Task |
|--------|------|
| ✅ | Create `TextContractForm` component |
| ✅ | Add text input tab to upload view |
| ✅ | Handle text submission flow |
| ✅ | Write text form component tests |

---

## Phase 11: Export & Reports

> **Goal:** Enable users to export contract analysis results in shareable formats (PDF reports, CSV data exports).

### Why This Feature
- Completes the core workflow (analyze → share results)
- Users need to share findings with lawyers, stakeholders
- Professional PDF reports add credibility
- CSV exports enable data analysis in spreadsheets

### Phase 11A: Backend - PDF Report Generation
| Status | Task |
|--------|------|
| 📋 | Install PDF generation package (barryvdh/laravel-dompdf or similar) |
| 📋 | Create `ReportController` with `generatePdf` method |
| 📋 | Create `ReportService` for report generation logic |
| 📋 | Create Blade template for PDF report layout |
| 📋 | Include: summary, risk badge, key findings, clauses, deadlines |
| 📋 | Add contract metadata (title, dates, file info) |
| 📋 | Style report for professional appearance |
| 📋 | Create `GET /contracts/{id}/report/pdf` endpoint |
| 📋 | Write report generation tests |

### Phase 11B: Backend - CSV Export
| Status | Task |
|--------|------|
| 📋 | Create `ExportService` for data export logic |
| 📋 | Create `GET /contracts/{id}/export/clauses` endpoint (CSV) |
| 📋 | Create `GET /contracts/{id}/export/deadlines` endpoint (CSV) |
| 📋 | Create `GET /contracts/{id}/export/all` endpoint (ZIP with all CSVs) |
| 📋 | Include proper CSV headers and formatting |
| 📋 | Handle special characters and encoding |
| 📋 | Write CSV export tests |

### Phase 11C: Frontend - Export UI
| Status | Task |
|--------|------|
| 📋 | Create `ExportMenu` dropdown component |
| 📋 | Add export options to `ContractDetailView` |
| 📋 | Handle download triggers for PDF and CSV |
| 📋 | Show loading state during generation |
| 📋 | Add success/error toast notifications |
| 📋 | Write export component tests |

---

### Phase 11 Design Decisions

#### Export Formats

| Format | Content | Use Case |
|--------|---------|----------|
| **PDF Report** | Full analysis with formatting | Share with lawyers, stakeholders |
| **CSV Clauses** | All extracted clauses | Import to spreadsheet, database |
| **CSV Deadlines** | All deadlines with dates | Calendar import, tracking |
| **ZIP Bundle** | All CSVs + summary | Complete data export |

#### PDF Report Structure

```
┌────────────────────────────────────────┐
│  CONTRACTLY ANALYSIS REPORT            │
│  ─────────────────────────────────────│
│  Contract: Service Agreement v2.pdf    │
│  Analyzed: January 29, 2026            │
│  Overall Risk: ⚠️ MEDIUM               │
├────────────────────────────────────────┤
│  EXECUTIVE SUMMARY                     │
│  This contract contains 12 clauses...  │
├────────────────────────────────────────┤
│  KEY FINDINGS                          │
│  • Auto-renewal clause (Section 4.2)   │
│  • Unlimited liability (Section 7.1)   │
├────────────────────────────────────────┤
│  EXTRACTED CLAUSES                     │
│  [Table with risk levels]              │
├────────────────────────────────────────┤
│  IMPORTANT DEADLINES                   │
│  [Table with dates]                    │
├────────────────────────────────────────┤
│  Generated by Contractly               │
│  This is not legal advice.             │
└────────────────────────────────────────┘
```

#### API Endpoints

```
GET /contracts/{id}/report/pdf
  → Returns: PDF file download (Content-Disposition: attachment)

GET /contracts/{id}/export/clauses
  → Returns: CSV file with clause data

GET /contracts/{id}/export/deadlines
  → Returns: CSV file with deadline data

GET /contracts/{id}/export/all
  → Returns: ZIP file containing all exports
```

---

## Phase 12: Search & Filtering

> **Goal:** Enable users to search across contracts and filter by various criteria for efficient contract management.

### Why This Feature
- Essential for users with many contracts
- Quick access to specific clauses or terms
- Filter by risk level, status, date ranges
- Power user productivity feature

### Phase 12A: Backend - Search Infrastructure
| Status | Task |
|--------|------|
| 📋 | Add full-text search index to contracts table |
| 📋 | Create `SearchController` |
| 📋 | Create `ContractSearchService` |
| 📋 | Implement full-text search across title, summary, clauses |
| 📋 | Create `GET /contracts/search` endpoint |
| 📋 | Support query parameters: `q`, `risk_level`, `status`, `date_from`, `date_to` |
| 📋 | Add pagination to search results |
| 📋 | Write search service tests |

### Phase 12B: Backend - Filter Enhancements
| Status | Task |
|--------|------|
| 📋 | Update `ContractRepositoryInterface` with filter methods |
| 📋 | Add `filterByRiskLevel(RiskLevel $level)` method |
| 📋 | Add `filterByStatus(ContractStatus $status)` method |
| 📋 | Add `filterByDateRange(Carbon $from, Carbon $to)` method |
| 📋 | Add `filterByClauseType(ClauseType $type)` method |
| 📋 | Update `GET /contracts` to accept filter parameters |
| 📋 | Write filter tests |

### Phase 12C: Frontend - Search UI
| Status | Task |
|--------|------|
| 📋 | Create `SearchBar` component with debounced input |
| 📋 | Create `FilterPanel` component (collapsible) |
| 📋 | Create `RiskLevelFilter` checkbox/pill component |
| 📋 | Create `StatusFilter` component |
| 📋 | Create `DateRangeFilter` component |
| 📋 | Update `ContractsView` to include search and filters |
| 📋 | Persist filter state in URL query params |
| 📋 | Write search/filter component tests |

### Phase 12D: Frontend - Search Results
| Status | Task |
|--------|------|
| 📋 | Create `SearchResults` component |
| 📋 | Highlight matching text in results |
| 📋 | Show "no results" empty state |
| 📋 | Add clear filters button |
| 📋 | Update contracts store with search actions |
| 📋 | Write integration tests |

---

### Phase 12 Design Decisions

#### Search Scope

| Field | Searchable | Notes |
|-------|------------|-------|
| Contract title | ✅ | Primary search target |
| Analysis summary | ✅ | Full-text search |
| Clause text | ✅ | Search within clauses |
| Clause titles | ✅ | Quick lookup |
| Original filename | ✅ | Find by filename |

#### Filter Options

| Filter | Type | Values |
|--------|------|--------|
| Risk Level | Multi-select | Low, Medium, High, Critical |
| Status | Multi-select | Pending, Analyzing, Completed, Failed |
| Date Range | Date picker | From date, To date |
| Clause Type | Multi-select | Payment, Termination, Liability, etc. |
| Has Deadlines | Toggle | Yes/No |

#### URL Query Structure

```
/contracts?q=termination&risk=high,medium&status=completed&from=2026-01-01&to=2026-12-31
```

#### API Response

```json
{
  "data": [...contracts],
  "meta": {
    "current_page": 1,
    "total": 42,
    "per_page": 20,
    "query": "termination",
    "filters": {
      "risk_level": ["high", "medium"],
      "status": ["completed"]
    }
  }
}
```

---

## Phase 13: Email Notifications

> **Goal:** Send email notifications for key events (analysis complete, deadline reminders, weekly digests).

### Why This Feature
- Reminders are useless without actual notifications
- Users don't need to check app constantly
- Proactive communication improves engagement
- Weekly digest keeps users informed

### Phase 13A: Backend - Notification System
| Status | Task |
|--------|------|
| 📋 | Create `notification_preferences` migration |
| 📋 | Create `NotificationPreference` model |
| 📋 | Create `NotificationPreferenceRepository` |
| 📋 | Create `NotificationService` |
| 📋 | Create notification preference types enum |

### Phase 13B: Backend - Email Templates
| Status | Task |
|--------|------|
| 📋 | Create `AnalysisCompleteNotification` mailable |
| 📋 | Create `DeadlineReminderNotification` mailable |
| 📋 | Create `WeeklyDigestNotification` mailable |
| 📋 | Design email templates (Blade) with branding |
| 📋 | Include unsubscribe links in all emails |
| 📋 | Write notification tests |

### Phase 13C: Backend - Triggers & Scheduling
| Status | Task |
|--------|------|
| 📋 | Dispatch notification when analysis completes |
| 📋 | Update `SendReminderJob` to send emails |
| 📋 | Create `SendWeeklyDigestJob` |
| 📋 | Schedule weekly digest (Monday 9 AM) |
| 📋 | Create `NotificationController` for preferences |
| 📋 | Create `GET /notifications/preferences` endpoint |
| 📋 | Create `PUT /notifications/preferences` endpoint |

### Phase 13D: Frontend - Notification Preferences
| Status | Task |
|--------|------|
| 📋 | Add TypeScript types for notification preferences |
| 📋 | Create `NotificationSettings` component |
| 📋 | Create notifications settings page/section |
| 📋 | Add toggle switches for each notification type |
| 📋 | Integrate with user settings |
| 📋 | Write preference component tests |

---

### Phase 13 Design Decisions

#### Notification Types

| Type | Trigger | Default |
|------|---------|---------|
| Analysis Complete | Contract analysis finishes | ✅ On |
| Deadline Reminder | X days before deadline | ✅ On |
| Weekly Digest | Monday 9 AM | ❌ Off |
| Contract Expiring | 30/14/7 days before expiry | ✅ On |

#### Email Templates

**Analysis Complete:**
```
Subject: Your contract analysis is ready - {contract_title}

Hi {user_name},

Great news! Your contract "{contract_title}" has been analyzed.

Risk Level: {risk_level}
Key Findings: {count} items identified
Deadlines: {deadline_count} dates extracted

[View Full Analysis →]

---
Contractly
```

**Weekly Digest:**
```
Subject: Your Contractly Weekly Summary

Hi {user_name},

Here's what happened this week:

📄 Contracts Analyzed: {count}
⚠️ High-Risk Items: {high_risk_count}
📅 Upcoming Deadlines: {deadline_count}

[Top 3 urgent items listed]

[View Dashboard →]
```

#### Database Schema

```sql
CREATE TABLE notification_preferences (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    analysis_complete BOOLEAN DEFAULT TRUE,
    deadline_reminder BOOLEAN DEFAULT TRUE,
    deadline_days_before INTEGER DEFAULT 7,
    weekly_digest BOOLEAN DEFAULT FALSE,
    contract_expiring BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    UNIQUE(user_id)
);
```

---

## Phase 14: Contract Comparison

> **Goal:** Compare two contracts side-by-side to identify differences in terms, clauses, and risk levels.

### Why This Feature
- Key use case for contract negotiations
- Compare vendor proposals
- Track changes between contract versions
- Identify missing or added clauses

### Phase 14A: Backend - Comparison Engine
| Status | Task |
|--------|------|
| 📋 | Create `ContractComparisonService` |
| 📋 | Create `ComparisonResult` DTO |
| 📋 | Implement clause-by-clause comparison algorithm |
| 📋 | Identify: added, removed, modified clauses |
| 📋 | Compare risk levels between contracts |
| 📋 | Compare deadline differences |
| 📋 | Calculate similarity score |

### Phase 14B: Backend - API
| Status | Task |
|--------|------|
| 📋 | Create `ComparisonController` |
| 📋 | Create `POST /contracts/compare` endpoint |
| 📋 | Accept `contract_id_a` and `contract_id_b` parameters |
| 📋 | Validate both contracts belong to user |
| 📋 | Validate both contracts are analyzed |
| 📋 | Create `ComparisonResource` |
| 📋 | Write comparison endpoint tests |

### Phase 14C: Frontend - Comparison UI
| Status | Task |
|--------|------|
| 📋 | Create `CompareContractsView` |
| 📋 | Create `ContractSelector` component (dropdown) |
| 📋 | Create `ComparisonResult` container component |
| 📋 | Create `SideBySideView` component |
| 📋 | Create `ClauseDiff` component (highlight differences) |
| 📋 | Create `RiskComparison` component |
| 📋 | Add comparison link to contract list/detail |

### Phase 14D: Frontend - Diff Visualization
| Status | Task |
|--------|------|
| 📋 | Implement text diff highlighting (added=green, removed=red) |
| 📋 | Create `DeadlineComparison` component |
| 📋 | Create `SummaryComparison` component |
| 📋 | Add "swap contracts" button |
| 📋 | Create comparison empty state |
| 📋 | Write comparison component tests |

---

### Phase 14 Design Decisions

#### Comparison Algorithm

1. **Clause Matching**: Match clauses by type and similarity score
2. **Diff Detection**: Use text diff algorithm for content comparison
3. **Risk Comparison**: Side-by-side risk level comparison
4. **Deadline Matching**: Match by type and compare dates

#### Comparison Result Structure

```json
{
  "contract_a": { "id": "...", "title": "..." },
  "contract_b": { "id": "...", "title": "..." },
  "similarity_score": 0.78,
  "risk_comparison": {
    "contract_a": "medium",
    "contract_b": "high",
    "changed": true
  },
  "clauses": {
    "matched": [
      {
        "type": "termination",
        "a": { "text": "...", "risk": "low" },
        "b": { "text": "...", "risk": "medium" },
        "similarity": 0.85,
        "diff_highlights": [...]
      }
    ],
    "only_in_a": [...],
    "only_in_b": [...]
  },
  "deadlines": {
    "matched": [...],
    "only_in_a": [...],
    "only_in_b": [...]
  }
}
```

#### UI Layout

```
┌─────────────────────────────────────────────────────────────┐
│  Compare Contracts                            [Swap ⇄]      │
├─────────────────────────────────────────────────────────────┤
│  [Select Contract A ▼]     vs     [Select Contract B ▼]     │
├─────────────────────────────────────────────────────────────┤
│  Similarity: 78%    Risk: Medium → High ⚠️                  │
├──────────────────────────┬──────────────────────────────────┤
│  Contract A              │  Contract B                      │
├──────────────────────────┼──────────────────────────────────┤
│  Termination Clause      │  Termination Clause              │
│  "Party may terminate    │  "Party may terminate            │
│   with [-30-]{+60+} days │   with 60 days notice..."        │
│   notice..."             │                                  │
├──────────────────────────┼──────────────────────────────────┤
│  ❌ Limitation of        │  ✅ Limitation of                │
│     Liability (MISSING)  │     Liability                    │
└──────────────────────────┴──────────────────────────────────┘
```

---

## Phase 15: Onboarding Experience

> **Goal:** Guide new users through the product with a welcome flow, sample contract, and helpful tooltips.

### Why This Feature
- Reduce time-to-value for new users
- Demonstrate product capabilities immediately
- Reduce support questions
- Improve activation and retention

### Phase 15A: Backend - Sample Contract
| Status | Task |
|--------|------|
| 📋 | Create `SampleContractService` |
| 📋 | Store sample contract content (service agreement) |
| 📋 | Create `POST /contracts/sample` endpoint |
| 📋 | Generate pre-analyzed sample for user |
| 📋 | Skip queue - create with instant analysis results |
| 📋 | Write sample contract tests |

### Phase 15B: Frontend - Welcome Flow
| Status | Task |
|--------|------|
| 📋 | Create `WelcomeModal` component |
| 📋 | Show on first login (track in localStorage) |
| 📋 | Create welcome content (3-4 slides) |
| 📋 | Include "Try with sample contract" CTA |
| 📋 | Include "Upload your own" CTA |
| 📋 | Add "Don't show again" option |

### Phase 15C: Frontend - Empty States
| Status | Task |
|--------|------|
| 📋 | Enhance `DashboardView` empty state |
| 📋 | Enhance `ContractsView` empty state |
| 📋 | Add guided actions (upload, try sample) |
| 📋 | Create helpful illustrations |
| 📋 | Add feature highlights |

### Phase 15D: Frontend - Tooltips & Guidance
| Status | Task |
|--------|------|
| 📋 | Create `Tooltip` component |
| 📋 | Create `FeatureHighlight` component (pulsing dot) |
| 📋 | Add tooltips to key features |
| 📋 | Create `OnboardingChecklist` component (optional) |
| 📋 | Track onboarding completion |
| 📋 | Write onboarding component tests |

---

### Phase 15 Design Decisions

#### Welcome Flow Slides

1. **Welcome**: "Contractly analyzes your contracts with AI"
2. **Upload**: "Upload PDF, image, or paste text"
3. **Analyze**: "AI extracts clauses, risks, and deadlines"
4. **Action**: "Try with a sample or upload your own"

#### Sample Contract

Pre-built "Sample Service Agreement" with:
- Multiple clause types (termination, payment, liability)
- Mix of risk levels (low, medium, high)
- Several deadlines
- Pre-generated analysis (no API call needed)

#### Empty State Design

```
┌─────────────────────────────────────────────────────────────┐
│                                                              │
│                     📄                                       │
│                                                              │
│           No contracts yet                                   │
│                                                              │
│     Upload your first contract to see AI-powered            │
│     analysis of clauses, risks, and deadlines.              │
│                                                              │
│     [Upload Contract]    [Try Sample Contract]              │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

#### Onboarding Checklist (Optional)

```
Getting Started ✓
├── ✅ Create account
├── ⬜ Upload first contract
├── ⬜ Review analysis results
├── ⬜ Set a deadline reminder
└── ⬜ Ask a question about your contract
```

---

## Current Sprint

**Sprint:** Planning Next Phase
**Goal:** Select and begin next feature implementation

### Active Tasks

| Status | Task | Notes |
|--------|------|-------|
| ⬜ | Choose next feature | Export/Reports, Search/Filtering, Email Notifications, Contract Comparison, or Onboarding |

### Completed Phases

| Phase | Status | Notes |
|-------|--------|-------|
| Phase 0 | ✅ Complete | Project setup, Docker, Laravel, Vue |
| Phase 1 | ✅ Complete | Authentication (Sanctum, login, register, password reset) |
| Phase 2 | ✅ Complete | Contract upload, storage, CRUD, frontend UI with tests |
| Phase 3 | ✅ Complete | AI Analysis backend complete, frontend UI complete |
| Phase 4 | ✅ Complete | Reminders - email notifications for deadlines |
| Phase 5 | ✅ Complete | Dashboard with real-time stats, UI polish |
| Phase 7 | ✅ Complete | Document Intelligence - validation & PII redaction |
| Phase 8 | ✅ Complete | AI Contract Chat - contextual Q&A about contracts |
| Phase 9 | ✅ Complete | Multi-Format File Support - images and text files |
| Phase 10 | ✅ Complete | Quick Text Analysis - paste & analyze text directly |

### Upcoming Phases

| Phase | Status | Priority | Notes |
|-------|--------|----------|-------|
| Phase 11 | 📋 Backlog | High | Export & Reports - PDF reports, CSV exports |
| Phase 12 | 📋 Backlog | High | Search & Filtering - full-text search, filters |
| Phase 13 | 📋 Backlog | Medium | Email Notifications - analysis complete, digests |
| Phase 14 | 📋 Backlog | High | Contract Comparison - side-by-side diff |
| Phase 15 | 📋 Backlog | Medium | Onboarding Experience - welcome flow, sample contract |
| Phase 6 | 📋 Backlog | Low | Deployment Preparation - production setup |

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
| Phase 7 | 40 | 40 | 0 | 0 | 0 | 0 |
| Phase 8 | 37 | 36 | 0 | 0 | 0 | 1 |
| Phase 9 | 18 | 18 | 0 | 0 | 0 | 0 |
| Phase 10 | 9 | 9 | 0 | 0 | 0 | 0 |
| Phase 11 | 22 | 0 | 0 | 0 | 0 | 22 |
| Phase 12 | 22 | 0 | 0 | 0 | 0 | 22 |
| Phase 13 | 18 | 0 | 0 | 0 | 0 | 18 |
| Phase 14 | 24 | 0 | 0 | 0 | 0 | 24 |
| Phase 15 | 18 | 0 | 0 | 0 | 0 | 18 |
| **Total** | **378** | **263** | **0** | **0** | **0** | **115** |

---

## Notes

- Update status as tasks progress
- Add new tasks discovered during development
- Move BACKLOG → TODO when ready to start phase
- Document blockers with reasons
- Keep progress summary updated

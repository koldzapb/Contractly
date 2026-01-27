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
| ⬜ | Install and configure Laravel Boost |
| ✅ | Configure PostgreSQL connection |
| ✅ | Configure Redis (cache/queue/sessions) |
| ✅ | Configure Mailpit for local email |
| ✅ | Install Laravel Sanctum |
| ⬜ | Install PDF parsing package |
| ⬜ | Configure Anthropic HTTP client |
| ⬜ | Configure Laravel Pint |
| ⬜ | Configure Pest for testing |
| ⬜ | Create base test configuration |

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
| 📋 | Create contracts migration |
| 📋 | Create contract_analyses migration |
| 📋 | Create contract_clauses migration |
| 📋 | Create contract_deadlines migration |
| 📋 | Create reminders migration |
| 📋 | Create ContractStatus enum |
| 📋 | Create ClauseType enum |
| 📋 | Create RiskLevel enum |
| 📋 | Create DeadlineType enum |
| 📋 | Create ReminderStatus enum |
| 📋 | Create Contract model |
| 📋 | Create ContractAnalysis model |
| 📋 | Create ContractClause model |
| 📋 | Create ContractDeadline model |
| 📋 | Create Reminder model |
| 📋 | Create ContractController |
| 📋 | Create ContractUploadService |
| 📋 | Create StoreContractRequest |
| 📋 | Create ContractResource |
| 📋 | Configure file storage (S3/local) |
| 📋 | Write upload contract test |
| 📋 | Write list contracts test |
| 📋 | Write view contract test |
| 📋 | Write delete contract test |

### Frontend
| Status | Task |
|--------|------|
| 📋 | Create contracts store |
| 📋 | Create ContractUploadView |
| 📋 | Create ContractUploader component |
| 📋 | Create UploadProgress component |
| 📋 | Create ContractList component |
| 📋 | Create ContractCard component |
| 📋 | Implement file validation |
| 📋 | Handle upload errors |
| 📋 | Write contracts store tests |
| 📋 | Write upload component tests |

---

## Phase 3: AI Analysis

### Backend
| Status | Task |
|--------|------|
| 📋 | Create PdfParserService |
| 📋 | Implement PDF text extraction |
| 📋 | Handle multi-page documents |
| 📋 | Add error handling for corrupt files |
| 📋 | Create ClaudeAiService |
| 📋 | Configure Anthropic API client |
| 📋 | Create analysis prompt template |
| 📋 | Parse structured AI response |
| 📋 | Handle API errors and rate limits |
| 📋 | Create ContractAnalysisService |
| 📋 | Orchestrate parsing + AI analysis |
| 📋 | Save analysis results |
| 📋 | Save extracted clauses |
| 📋 | Save extracted deadlines |
| 📋 | Create AnalyzeContractJob |
| 📋 | Create ContractAnalysisCompleted event |
| 📋 | Configure queue worker |
| 📋 | Write PDF parsing tests |
| 📋 | Write AI service tests (mocked) |
| 📋 | Write analysis job tests |
| 📋 | Write integration tests |

### Frontend
| Status | Task |
|--------|------|
| 📋 | Create ContractDetailView |
| 📋 | Create AnalysisProgress component |
| 📋 | Create AnalysisSummary component |
| 📋 | Create ClauseList component |
| 📋 | Create ClauseCard component |
| 📋 | Create RiskBadge component |
| 📋 | Create DeadlineList component |
| 📋 | Implement status polling |
| 📋 | Display analysis results |
| 📋 | Write analysis component tests |

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

## Current Sprint

**Sprint:** Phase 0 - Project Setup
**Goal:** Complete Docker setup and initialize Laravel + Vue projects

### Active Tasks

| Status | Task | Notes |
|--------|------|-------|
| ✅ | Initialize Git repository | Completed |
| ✅ | Create Docker configuration | Completed |
| ✅ | Verify containers start | All containers running |
| ✅ | Initialize Laravel project | Laravel 12 with Sanctum |
| ✅ | Initialize Vue project | Vue 3 + TypeScript + Tailwind |

### Blocked

| Task | Blocked By | Notes |
|------|------------|-------|
| - | - | No blockers |

---

## Progress Summary

| Phase | Total | Done | In Progress | Blocked | Todo | Backlog |
|-------|-------|------|-------------|---------|------|---------|
| Phase 0 | 32 | 28 | 0 | 0 | 4 | 0 |
| Phase 1 | 23 | 23 | 0 | 0 | 0 | 0 |
| Phase 2 | 34 | 0 | 0 | 0 | 0 | 34 |
| Phase 3 | 30 | 0 | 0 | 0 | 0 | 30 |
| Phase 4 | 19 | 0 | 0 | 0 | 0 | 19 |
| Phase 5 | 14 | 0 | 0 | 0 | 0 | 14 |
| Phase 6 | 9 | 0 | 0 | 0 | 0 | 9 |
| **Total** | **161** | **51** | **0** | **0** | **4** | **106** |

---

## Notes

- Update status as tasks progress
- Add new tasks discovered during development
- Move BACKLOG → TODO when ready to start phase
- Document blockers with reasons
- Keep progress summary updated

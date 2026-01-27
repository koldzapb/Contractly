# Contractly - Project Context

## Overview

Contractly is an AI-powered contract analysis platform that helps users understand, track, and manage their contracts.

## Core Features

1. **Contract Upload** - PDF upload with validation (max 10MB, 50 pages)
2. **AI Analysis** - Claude-powered clause identification and risk assessment
3. **Deadline Extraction** - Automatic detection of important dates
4. **Email Reminders** - Scheduled notifications for deadlines
5. **Contract Management** - List, view, search, delete contracts

## Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | Laravel 11+ (PHP 8.3+) |
| Frontend | Vue 3 (Composition API) + TypeScript + Vite |
| Database | PostgreSQL 16 |
| Cache/Queue | Redis 7 |
| AI | Anthropic Claude API |
| Email | Resend (prod) / Mailhog (dev) |
| Storage | S3-compatible (prod) / Local (dev) |
| Container | Docker |

## Directory Structure

```
contractly/
├── .ai/guidelines/       # AI guidelines (Boost reads this)
├── .claude/skills/       # Claude Code skills
├── .cursor/rules/        # Cursor IDE rules
├── docs/                 # Human documentation
├── backend/              # Laravel application
│   ├── app/
│   │   ├── Enums/       # ContractStatus, RiskLevel, etc.
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Jobs/        # AnalyzeContractJob, SendReminderJob
│   │   ├── Models/
│   │   └── Services/    # Business logic
│   ├── database/migrations/
│   ├── routes/api.php
│   └── tests/
├── frontend/             # Vue 3 application
│   ├── src/
│   │   ├── components/
│   │   ├── composables/
│   │   ├── services/    # API layer
│   │   ├── stores/      # Pinia stores
│   │   ├── types/       # TypeScript interfaces
│   │   └── views/
│   └── tests/
└── docker/               # Docker configuration
```

## Key Models

- **User** - Authentication, owns contracts
- **Contract** - Uploaded PDF, status tracking
- **ContractAnalysis** - AI analysis results
- **ContractClause** - Individual clause with risk level
- **ContractDeadline** - Extracted dates
- **Reminder** - Scheduled email notifications

## External Services

- **Anthropic Claude API** - Contract analysis (async via queue)
- **Resend** - Transactional email delivery
- **S3** - File storage (production)

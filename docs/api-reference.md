# Contractly - API Reference

## Overview

Base URL: `http://localhost/api` (development)

Authentication: Laravel Sanctum (cookie-based SPA authentication)

---

## Response Format

### Success (Single Resource)
```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "title": "Contract Title",
    "status": "completed"
  }
}
```

### Success (Collection)
```json
{
  "data": [
    { "id": "...", "title": "..." }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

### Error
```json
{
  "message": "Human readable message",
  "errors": {
    "field": ["Error 1", "Error 2"]
  },
  "code": "VALIDATION_ERROR"
}
```

---

## TypeScript Types

```typescript
// frontend/src/types/api.ts

// Enums
export type ContractStatus = 'pending' | 'processing' | 'completed' | 'failed'
export type RiskLevel = 'none' | 'low' | 'medium' | 'high'
export type ClauseType =
  | 'payment' | 'termination' | 'liability' | 'penalty'
  | 'auto_renewal' | 'non_compete' | 'confidentiality'
  | 'indemnification' | 'dispute_resolution' | 'other'
export type DeadlineType =
  | 'payment' | 'renewal' | 'termination_notice'
  | 'delivery' | 'review_period' | 'other'
export type ReminderStatus = 'pending' | 'sent' | 'failed' | 'cancelled'

// Models
export interface User {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  created_at: string
}

export interface Contract {
  id: string
  title: string
  original_filename: string
  file_size_bytes: number
  page_count: number | null
  status: ContractStatus
  overall_risk_level: RiskLevel | null
  language_detected: string | null
  error_message: string | null
  created_at: string
  updated_at: string
}

export interface ContractAnalysis {
  summary: string
  overall_risk_level: RiskLevel
  key_findings: string[]
  ai_model: string
  tokens_used: number
  processing_time_ms: number
  created_at: string
}

export interface ContractClause {
  id: number
  clause_type: ClauseType
  original_text: string
  plain_explanation: string
  risk_level: RiskLevel
  risk_reason: string | null
  position_index: number
}

export interface ContractDeadline {
  id: number
  deadline_type: DeadlineType
  deadline_date: string | null
  is_recurring: boolean
  recurrence_pattern: 'monthly' | 'quarterly' | 'yearly' | null
  description: string
  source_text: string
}

export interface Reminder {
  id: number
  contract_id: string
  contract_title?: string
  deadline_id: number | null
  title: string
  remind_at: string
  days_before: number
  channel: 'email'
  status: ReminderStatus
  sent_at: string | null
  created_at: string
}

export interface ContractDetailed extends Contract {
  analysis: ContractAnalysis | null
  clauses: ContractClause[]
  deadlines: ContractDeadline[]
  reminders: Reminder[]
}

export interface DashboardStats {
  total_contracts: number
  contracts_by_status: Record<ContractStatus, number>
  contracts_by_risk: Record<Exclude<RiskLevel, 'none'>, number>
  pending_reminders: number
  contracts_this_month: number
}

export interface UpcomingDeadline {
  deadline_id: number
  contract_id: string
  contract_title: string
  deadline_type: DeadlineType
  deadline_date: string
  description: string
  days_until: number
  has_reminder: boolean
}

// Response Wrappers
export interface ApiResponse<T> {
  data: T
}

export interface ApiListResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
  code?: string
}
```

---

## Endpoints

### Authentication

#### POST /auth/register
Create a new user account.

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "securepassword123",
  "password_confirmation": "securepassword123"
}
```

**Response:** `201 Created`
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2025-01-27T10:00:00Z"
  }
}
```

#### POST /auth/login
Authenticate user.

**Request:**
```json
{
  "email": "john@example.com",
  "password": "securepassword123"
}
```

**Response:** `200 OK` - Returns User object

**Errors:** `401` Invalid credentials, `429` Rate limited

#### POST /auth/logout
End session. **Requires auth.**

**Response:** `204 No Content`

#### GET /auth/user
Get current user. **Requires auth.**

**Response:** `200 OK` - Returns User object

#### POST /auth/forgot-password
Request password reset.

**Request:** `{ "email": "john@example.com" }`

**Response:** `200 OK` - `{ "message": "Password reset link sent" }`

#### POST /auth/reset-password
Reset password with token.

**Request:**
```json
{
  "token": "reset-token",
  "email": "john@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

### Contracts

#### GET /contracts
List user's contracts. **Requires auth.**

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `page` | int | Page number (default: 1) |
| `per_page` | int | Items per page (default: 15, max: 50) |
| `status` | string | Filter: pending, processing, completed, failed |
| `risk_level` | string | Filter: low, medium, high |
| `sort` | string | Sort by: created_at, title |
| `order` | string | asc, desc (default: desc) |
| `search` | string | Search title/filename |

**Response:** `200 OK` - Returns paginated Contract list

#### POST /contracts
Upload new contract. **Requires auth.**

**Request:** `multipart/form-data`
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `file` | file | Yes | PDF (max 10MB, 50 pages) |
| `title` | string | No | Custom title |

**Response:** `201 Created` - Returns Contract object

#### GET /contracts/{id}
Get contract with analysis. **Requires auth.**

**Response:** `200 OK` - Returns ContractDetailed object

**Example Response:**
```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "title": "Employment Contract",
    "original_filename": "employment-contract.pdf",
    "file_size_bytes": 245780,
    "page_count": 12,
    "status": "completed",
    "overall_risk_level": "medium",
    "language_detected": "en",
    "error_message": null,
    "created_at": "2025-01-27T10:00:00Z",
    "updated_at": "2025-01-27T10:02:30Z",
    "analysis": {
      "summary": "Standard employment contract with a 12-month non-compete clause.",
      "overall_risk_level": "medium",
      "key_findings": [
        "Non-compete restricts employment for 12 months",
        "All IP belongs to employer",
        "30-day notice required"
      ],
      "ai_model": "claude-sonnet-4-20250514",
      "tokens_used": 2847,
      "processing_time_ms": 4520,
      "created_at": "2025-01-27T10:02:30Z"
    },
    "clauses": [
      {
        "id": 1,
        "clause_type": "non_compete",
        "original_text": "Employee agrees not to engage...",
        "plain_explanation": "You cannot work for competitors for 1 year.",
        "risk_level": "medium",
        "risk_reason": "12-month period is longer than average.",
        "position_index": 0
      }
    ],
    "deadlines": [
      {
        "id": 1,
        "deadline_type": "termination_notice",
        "deadline_date": null,
        "is_recurring": false,
        "recurrence_pattern": null,
        "description": "30-day notice required",
        "source_text": "Either party may terminate..."
      }
    ],
    "reminders": []
  }
}
```

#### GET /contracts/{id}/status
Poll processing status. **Requires auth.**

**Response:** `200 OK`
```json
{
  "data": {
    "id": "550e8400...",
    "status": "processing",
    "error_message": null
  }
}
```

#### DELETE /contracts/{id}
Delete contract. **Requires auth.**

**Response:** `204 No Content`

---

### Reminders

#### GET /reminders
List user's reminders. **Requires auth.**

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `status` | string | Filter: pending, sent, failed, cancelled |
| `upcoming` | bool | Only future pending reminders |

**Response:** `200 OK` - Returns Reminder list

#### POST /contracts/{contract_id}/reminders
Create reminder. **Requires auth.**

**Request:**
```json
{
  "deadline_id": 1,
  "title": "Contract renewal reminder",
  "remind_at": "2026-01-01T09:00:00Z",
  "days_before": 30
}
```

**Response:** `201 Created` - Returns Reminder object

#### PATCH /reminders/{id}
Update reminder. **Requires auth.**

**Request:** `{ "title": "...", "remind_at": "..." }`

**Response:** `200 OK` - Returns Reminder object

#### DELETE /reminders/{id}
Cancel reminder. **Requires auth.**

**Response:** `204 No Content`

---

### Dashboard

#### GET /dashboard/stats
Get statistics. **Requires auth.**

**Response:** `200 OK`
```json
{
  "data": {
    "total_contracts": 42,
    "contracts_by_status": {
      "pending": 2,
      "processing": 1,
      "completed": 38,
      "failed": 1
    },
    "contracts_by_risk": {
      "low": 20,
      "medium": 15,
      "high": 3
    },
    "pending_reminders": 5,
    "contracts_this_month": 8
  }
}
```

#### GET /dashboard/upcoming
Get upcoming deadlines. **Requires auth.**

**Query Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `days` | int | Look ahead (default: 30, max: 90) |
| `limit` | int | Max results (default: 10, max: 50) |

**Response:** `200 OK` - Returns UpcomingDeadline list

---

## Error Codes

| Code | HTTP | Description |
|------|------|-------------|
| `VALIDATION_ERROR` | 422 | Invalid input |
| `UNAUTHENTICATED` | 401 | Not logged in |
| `UNAUTHORIZED` | 403 | No permission |
| `NOT_FOUND` | 404 | Resource not found |
| `RATE_LIMITED` | 429 | Too many requests |
| `FILE_TOO_LARGE` | 422 | File exceeds 10MB |
| `INVALID_FILE_TYPE` | 422 | Not a PDF |
| `ANALYSIS_FAILED` | 500 | AI analysis error |
| `SERVER_ERROR` | 500 | Internal error |

---

## Rate Limits

| Endpoint | Limit |
|----------|-------|
| Authentication | 5/minute |
| Contract Upload | 10/hour |
| General API | 60/minute |

Headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`

---

## Date Format

All dates use **ISO 8601 UTC**: `2025-01-27T10:00:00Z`

Date-only fields use: `YYYY-MM-DD`

# API Conventions

## Response Format

### Single Resource
```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "title": "Contract Title",
    "status": "completed",
    "created_at": "2025-01-27T10:00:00Z"
  }
}
```

### Collection
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

## Data Types

### Enums (string values)

```typescript
type ContractStatus = 'pending' | 'processing' | 'completed' | 'failed'
type RiskLevel = 'none' | 'low' | 'medium' | 'high'
type ClauseType = 'payment' | 'termination' | 'liability' | 'penalty'
  | 'auto_renewal' | 'non_compete' | 'confidentiality'
  | 'indemnification' | 'dispute_resolution' | 'other'
type DeadlineType = 'payment' | 'renewal' | 'termination_notice'
  | 'delivery' | 'review_period' | 'other'
type ReminderStatus = 'pending' | 'sent' | 'failed' | 'cancelled'
```

### Core Models

```typescript
interface Contract {
  id: string                    // UUID
  title: string
  original_filename: string
  file_size_bytes: number
  page_count: number | null
  status: ContractStatus
  overall_risk_level: RiskLevel | null
  language_detected: string | null
  error_message: string | null
  created_at: string            // ISO 8601
  updated_at: string
}

interface ContractAnalysis {
  summary: string
  overall_risk_level: RiskLevel
  key_findings: string[]
  ai_model: string
  tokens_used: number
  processing_time_ms: number
  created_at: string
}

interface ContractClause {
  id: number
  clause_type: ClauseType
  original_text: string
  plain_explanation: string
  risk_level: RiskLevel
  risk_reason: string | null
  position_index: number
}

interface ContractDeadline {
  id: number
  deadline_type: DeadlineType
  deadline_date: string | null  // YYYY-MM-DD
  is_recurring: boolean
  recurrence_pattern: 'monthly' | 'quarterly' | 'yearly' | null
  description: string
  source_text: string
}

interface Reminder {
  id: number
  contract_id: string
  deadline_id: number | null
  title: string
  remind_at: string             // ISO 8601
  days_before: number
  channel: 'email'
  status: ReminderStatus
  sent_at: string | null
  created_at: string
}
```

## Laravel Resource Pattern

```php
class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status->value,  // Enum to string
            'overall_risk_level' => $this->analysis?->overall_risk_level->value,
            'created_at' => $this->created_at->toIso8601String(),

            // Conditional relationships
            'analysis' => $this->whenLoaded('analysis', fn () =>
                ContractAnalysisResource::make($this->analysis)
            ),
            'clauses' => $this->whenLoaded('clauses', fn () =>
                ClauseResource::collection($this->clauses)
            ),
        ];
    }
}
```

## Frontend API Service Pattern

```typescript
const client = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
})

export const api = {
  contracts: {
    list: () => client.get('/contracts').then(r => r.data.data),
    get: (id: string) => client.get(`/contracts/${id}`).then(r => r.data.data),
    upload: (file: File, title?: string) => {
      const form = new FormData()
      form.append('file', file)
      if (title) form.append('title', title)
      return client.post('/contracts', form).then(r => r.data.data)
    },
    delete: (id: string) => client.delete(`/contracts/${id}`),
  },
}
```

## Date/Time Format

- All datetime: ISO 8601 UTC (`2025-01-27T10:00:00Z`)
- Date only: `YYYY-MM-DD` (`2025-01-27`)
- Frontend parsing: `new Date(dateString)` or `parseISO()` from date-fns

## Error Codes

| Code | HTTP | Description |
|------|------|-------------|
| `VALIDATION_ERROR` | 422 | Request validation failed |
| `UNAUTHENTICATED` | 401 | Not logged in |
| `UNAUTHORIZED` | 403 | No permission |
| `NOT_FOUND` | 404 | Resource not found |
| `RATE_LIMITED` | 429 | Too many requests |
| `SERVER_ERROR` | 500 | Internal error |

// User types
export interface User {
  id: number
  name: string
  email: string
  created_at: string
  updated_at: string
}

// Contract types
export type ContractStatus = 'pending' | 'processing' | 'completed' | 'failed'

export interface Contract {
  id: string
  title: string
  original_filename: string
  file_size: number
  file_size_human: string
  page_count: number | null
  status: ContractStatus
  overall_risk_level: RiskLevel | null
  language_detected: string | null
  error_message?: string
  analyzed_at: string | null
  created_at: string
  updated_at: string
  analysis?: ContractAnalysis
}

// Analysis types
export type RiskLevel = 'low' | 'medium' | 'high'

export interface ContractAnalysis {
  id: string
  summary: string
  overall_risk_level: RiskLevel
  key_findings: string[]
  ai_model: string
  tokens_used: number
  processing_time_ms: number
  created_at: string
  clauses?: ContractClause[]
  deadlines?: ContractDeadline[]
}

// Clause types
export type ClauseType =
  | 'payment_terms'
  | 'termination'
  | 'liability'
  | 'confidentiality'
  | 'intellectual_property'
  | 'indemnification'
  | 'warranty'
  | 'force_majeure'
  | 'dispute_resolution'
  | 'governing_law'
  | 'assignment'
  | 'amendment'
  | 'other'

export interface ContractClause {
  id: string
  clause_type: ClauseType
  clause_type_label: string
  original_text: string
  plain_explanation: string
  risk_level: RiskLevel
  risk_reason: string | null
  page_number: number | null
  position_index: number
}

// Deadline types
export type DeadlineType =
  | 'payment_due'
  | 'renewal_date'
  | 'termination_notice'
  | 'delivery_date'
  | 'review_date'
  | 'start_date'
  | 'end_date'
  | 'milestone'
  | 'other'

export type DeadlineUrgency = 'overdue' | 'urgent' | 'soon' | 'normal' | 'far'

export interface ContractDeadline {
  id: string
  deadline_type: DeadlineType
  deadline_type_label: string
  title: string
  description: string | null
  deadline_date: string | null
  source_text: string | null
  is_recurring: boolean
  recurrence_pattern: string | null
  days_until: number | null
  urgency: DeadlineUrgency | null
  is_past: boolean
}

// Reminder types
export type ReminderStatus = 'pending' | 'sent' | 'failed' | 'cancelled'

export interface Reminder {
  id: string
  contract_deadline_id: string
  contract_id: string
  remind_at: string
  status: ReminderStatus
  sent_at: string | null
}

// API Response types
export interface ApiResponse<T> {
  data: T
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    from: number
    last_page: number
    per_page: number
    to: number
    total: number
  }
  links: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

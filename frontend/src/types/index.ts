// User types
export interface User {
  id: number
  name: string
  email: string
  created_at: string
  updated_at: string
}

// Contract types
export type ContractStatus = 'draft' | 'processing' | 'analyzed' | 'failed'

export interface Contract {
  id: number
  user_id: number
  title: string
  original_filename: string
  file_path: string
  file_size: number
  mime_type: string
  status: ContractStatus
  created_at: string
  updated_at: string
  analysis?: ContractAnalysis
  clauses?: ContractClause[]
  deadlines?: ContractDeadline[]
}

// Analysis types
export type RiskLevel = 'low' | 'medium' | 'high'

export interface ContractAnalysis {
  id: number
  contract_id: number
  summary: string
  overall_risk_level: RiskLevel
  key_findings: string[]
  recommendations: string[]
  analyzed_at: string
}

// Clause types
export type ClauseType =
  | 'payment'
  | 'termination'
  | 'liability'
  | 'confidentiality'
  | 'intellectual_property'
  | 'warranty'
  | 'indemnification'
  | 'dispute_resolution'
  | 'other'

export interface ContractClause {
  id: number
  contract_id: number
  clause_type: ClauseType
  title: string
  content: string
  risk_level: RiskLevel
  explanation: string
  page_number?: number
}

// Deadline types
export type DeadlineType = 'payment' | 'renewal' | 'termination' | 'deliverable' | 'other'

export interface ContractDeadline {
  id: number
  contract_id: number
  deadline_type: DeadlineType
  title: string
  description: string
  due_date: string
  amount?: number
  currency?: string
}

// Reminder types
export type ReminderStatus = 'pending' | 'sent' | 'failed'

export interface Reminder {
  id: number
  contract_deadline_id: number
  remind_at: string
  status: ReminderStatus
  sent_at?: string
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

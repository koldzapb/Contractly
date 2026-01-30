// User types
export interface User {
  id: number
  name: string
  email: string
  created_at: string
  updated_at: string
}

// Document Validation types
export type DocumentType =
  | 'contract'
  | 'amendment'
  | 'nda'
  | 'mou'
  | 'loi'
  | 'term_sheet'
  | 'invoice'
  | 'receipt'
  | 'letter'
  | 'report'
  | 'other'
  | 'unknown'

export type DocumentCategory = 'legal' | 'pre_contractual' | 'non_legal'

export interface DocumentClassification {
  is_legal_document: boolean
  document_type: DocumentType
  document_type_label: string
  category: DocumentCategory
  confidence: number
  rejection_reason: string | null
  warnings: string[]
  analyzed_with_override: boolean
}

// PII Redaction types
export type PiiType = 'ssn' | 'email' | 'phone' | 'credit_card' | 'bank_routing' | 'bank_account'

export interface DetectedPii {
  id: string
  type: PiiType
  type_label: string
  value: string
  redacted_value: string
  start_position: number
  end_position: number
  context: string
  selected: boolean
}

export interface PiiDetectionResult {
  has_pii: boolean
  total_count: number
  counts_by_type: Record<PiiType, number>
  items: DetectedPii[]
  extracted_text: string
}

// Contract types
export type ContractStatus = 'pending' | 'processing' | 'completed' | 'failed'

// File type for uploaded contracts
export type FileType = 'pdf' | 'image' | 'text'

export interface Contract {
  id: string
  title: string
  original_filename: string
  file_size: number
  file_size_human: string
  file_type: FileType
  file_type_label: string
  mime_type: string | null
  page_count: number | null
  status: ContractStatus
  overall_risk_level: RiskLevel | null
  language_detected: string | null
  error_message?: string
  analyzed_at: string | null
  created_at: string
  updated_at: string
  analysis?: ContractAnalysis
  document_classification?: DocumentClassification
  pii_detection?: PiiDetectionResult
  has_redactions?: boolean
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

export type DeadlineUrgency = 'overdue' | 'critical' | 'high' | 'medium' | 'low' | 'unknown'

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
  has_reminder: boolean
}

// Reminder types
export type ReminderStatus = 'pending' | 'sent' | 'failed' | 'cancelled'

export interface ReminderContract {
  id: string
  title: string
}

export interface ReminderDeadline {
  id: string
  title: string
  deadline_date: string | null
  deadline_type: DeadlineType
  deadline_type_label: string
}

export interface Reminder {
  id: string
  title: string
  remind_at: string
  days_before: number
  channel: string
  status: ReminderStatus
  status_label: string
  sent_at: string | null
  is_pending: boolean
  is_sent: boolean
  is_due: boolean
  created_at: string
  updated_at: string
  contract?: ReminderContract
  deadline?: ReminderDeadline
}

export interface CreateReminderData {
  contract_deadline_id: string
  days_before: number
  title?: string
  deadline_date?: string
}

export interface UpdateReminderData {
  days_before?: number
  title?: string
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

// Search & Filter types
export type SortField =
  | 'created_at'
  | 'title'
  | 'overall_risk_level'
  | 'status'
  | 'analyzed_at'
  | 'file_size'

export type SortOrder = 'asc' | 'desc'

export interface ContractSearchFilters {
  q?: string
  status?: ContractStatus[]
  risk_level?: RiskLevel[]
  file_type?: FileType[]
  date_from?: string
  date_to?: string
  has_deadlines?: boolean
  sort_by?: SortField
  sort_order?: SortOrder
  per_page?: number
  page?: number
}

export interface ContractSearchMeta {
  query: string | null
  statuses: ContractStatus[] | null
  risk_levels: RiskLevel[] | null
  file_types: FileType[] | null
  date_from: string | null
  date_to: string | null
  has_deadlines: boolean | null
  sort_by: SortField
  sort_order: SortOrder
  has_filters: boolean
}

export interface ContractSearchResponse {
  data: Contract[]
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
  filters: ContractSearchMeta
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

// Contract Comparison types
export type ComparisonMatchType = 'matched' | 'only_in_a' | 'only_in_b'

export interface ComparisonClauseData {
  id: string
  clause_type: ClauseType
  clause_type_label: string
  original_text: string
  plain_explanation: string
  risk_level: RiskLevel
  risk_reason: string | null
  page_number: number | null
}

export interface ClauseComparison {
  match_type: ComparisonMatchType
  similarity: number | null
  has_risk_difference: boolean
  clause_a: ComparisonClauseData | null
  clause_b: ComparisonClauseData | null
}

export interface ComparisonDeadlineData {
  id: string
  deadline_type: DeadlineType
  deadline_type_label: string
  title: string
  description: string | null
  deadline_date: string | null
  is_recurring: boolean
  recurrence_pattern: string | null
}

export interface DeadlineComparison {
  match_type: ComparisonMatchType
  days_difference: number | null
  has_date_difference: boolean
  deadline_a: ComparisonDeadlineData | null
  deadline_b: ComparisonDeadlineData | null
}

export interface ComparisonContractSummary {
  id: string
  title: string
  overall_risk_level: RiskLevel | null
}

export interface RiskComparison {
  contract_a: RiskLevel | null
  contract_b: RiskLevel | null
  changed: boolean
}

export interface ComparisonStats {
  total_clauses_a: number
  total_clauses_b: number
  matched_clauses: number
  clauses_only_in_a: number
  clauses_only_in_b: number
  total_deadlines_a: number
  total_deadlines_b: number
  matched_deadlines: number
  deadlines_only_in_a: number
  deadlines_only_in_b: number
}

export interface ContractComparisonResult {
  contract_a: ComparisonContractSummary
  contract_b: ComparisonContractSummary
  similarity_score: number
  risk_comparison: RiskComparison
  clauses: {
    matched: ClauseComparison[]
    only_in_a: ClauseComparison[]
    only_in_b: ClauseComparison[]
  }
  deadlines: {
    matched: DeadlineComparison[]
    only_in_a: DeadlineComparison[]
    only_in_b: DeadlineComparison[]
  }
  stats: ComparisonStats
}

export interface CompareContractsRequest {
  contract_id_a: string
  contract_id_b: string
}

// Dashboard types
export interface DashboardStats {
  total_contracts: number
  pending_analysis: number
  processing_analysis: number
  completed_analysis: number
  failed_analysis: number
  high_risk_clauses: number
  overdue_deadlines: number
}

export interface DashboardDeadline {
  id: string
  deadline_type: DeadlineType
  deadline_type_label: string
  title: string
  deadline_date: string | null
  days_until: number | null
  urgency: DeadlineUrgency | null
  is_past: boolean
  contract: { id: string; title: string }
}

export interface DashboardContract {
  id: string
  title: string
  status: ContractStatus
  overall_risk_level: RiskLevel | null
  created_at: string
}

export interface DashboardData {
  stats: DashboardStats
  upcoming_deadlines: DashboardDeadline[]
  recent_contracts: DashboardContract[]
}

// Chat types
export type ChatRole = 'user' | 'assistant'

export interface ChatMessage {
  id: string
  role: ChatRole
  role_label: string
  content: string
  tokens_used: number | null
  is_off_topic: boolean
  is_user_message: boolean
  is_assistant_message: boolean
  created_at: string
}

export interface SendMessageData {
  message: string
}

export interface ClearChatResponse {
  message: string
  messages_deleted: number
}

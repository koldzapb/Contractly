import type { RiskLevel } from './index'

// Quick Analysis request
export interface AnalyzeTextRequest {
  text: string
  title?: string
}

// Quick Analysis clause (simplified, no database ID)
export interface QuickAnalysisClause {
  id: string
  clause_type: string
  clause_type_label: string
  original_text: string
  plain_explanation: string
  risk_level: RiskLevel
  risk_reason: string | null
  page_number: number | null
}

// Quick Analysis deadline (simplified, no database ID)
export interface QuickAnalysisDeadline {
  id: string
  deadline_type: string
  deadline_type_label: string
  title: string
  description: string | null
  deadline_date: string | null
  source_text: string | null
  is_recurring: boolean
  recurrence_pattern: string | null
}

// Quick Analysis result (ephemeral, not stored)
export interface QuickAnalysisResult {
  title: string
  summary: string
  overall_risk_level: RiskLevel
  key_findings: string[]
  clauses: QuickAnalysisClause[]
  deadlines: QuickAnalysisDeadline[]
  tokens_used: number
  processing_time_ms: number
  pii_warning: boolean
}

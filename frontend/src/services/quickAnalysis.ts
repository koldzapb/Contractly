import api from './api'
import type { ApiResponse } from '@/types'
import type { AnalyzeTextRequest, QuickAnalysisResult } from '@/types/quickAnalysis'

/**
 * Analyze pasted contract text without storing in database.
 * Results are ephemeral and not persisted.
 */
export async function analyzeText(data: AnalyzeTextRequest): Promise<QuickAnalysisResult> {
  const response = await api.post<ApiResponse<QuickAnalysisResult>>('/analyze-text', data)

  return response.data.data
}

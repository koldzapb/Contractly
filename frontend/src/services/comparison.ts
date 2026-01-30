import api from './api'
import type { ApiResponse, ContractComparisonResult, CompareContractsRequest } from '@/types'

/**
 * Compare two contracts
 */
export async function compareContracts(
  request: CompareContractsRequest,
): Promise<ContractComparisonResult> {
  const response = await api.post<ApiResponse<ContractComparisonResult>>(
    '/contracts/compare',
    request,
  )
  return response.data.data
}

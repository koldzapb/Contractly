import api from './api'
import type { ApiResponse, DashboardData } from '@/types'

/**
 * Get dashboard data for the authenticated user
 */
export async function getDashboard(): Promise<DashboardData> {
  const response = await api.get<ApiResponse<DashboardData>>('/dashboard')

  return response.data.data
}

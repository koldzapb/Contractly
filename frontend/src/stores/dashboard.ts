import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { DashboardData, DashboardStats, DashboardDeadline, DashboardContract } from '@/types'
import * as dashboardService from '@/services/dashboard'

export const useDashboardStore = defineStore('dashboard', () => {
  // State
  const data = ref<DashboardData | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const stats = computed<DashboardStats | null>(() => data.value?.stats ?? null)
  const upcomingDeadlines = computed<DashboardDeadline[]>(
    () => data.value?.upcoming_deadlines ?? [],
  )
  const recentContracts = computed<DashboardContract[]>(() => data.value?.recent_contracts ?? [])
  const hasData = computed(() => data.value !== null)

  /**
   * Fetch dashboard data
   */
  async function fetchDashboard(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      data.value = await dashboardService.getDashboard()
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to load dashboard'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Clear error
   */
  function clearError(): void {
    error.value = null
  }

  /**
   * Reset store state
   */
  function $reset(): void {
    data.value = null
    loading.value = false
    error.value = null
  }

  return {
    // State
    data,
    loading,
    error,

    // Computed
    stats,
    upcomingDeadlines,
    recentContracts,
    hasData,

    // Actions
    fetchDashboard,
    clearError,
    $reset,
  }
})

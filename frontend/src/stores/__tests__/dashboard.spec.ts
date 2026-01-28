import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useDashboardStore } from '../dashboard'
import * as dashboardService from '@/services/dashboard'
import type { DashboardData } from '@/types'

vi.mock('@/services/dashboard')

const mockDashboardData: DashboardData = {
  stats: {
    total_contracts: 12,
    pending_analysis: 2,
    processing_analysis: 1,
    completed_analysis: 8,
    failed_analysis: 1,
    high_risk_clauses: 5,
    overdue_deadlines: 2,
  },
  upcoming_deadlines: [
    {
      id: 'deadline-uuid-1',
      deadline_type: 'renewal_date',
      deadline_type_label: 'Renewal Date',
      title: 'Contract Renewal Due',
      deadline_date: '2024-02-15',
      days_until: 7,
      urgency: 'high',
      is_past: false,
      contract: { id: 'contract-uuid-1', title: 'Service Agreement' },
    },
    {
      id: 'deadline-uuid-2',
      deadline_type: 'payment_due',
      deadline_type_label: 'Payment Due',
      title: 'Payment Due',
      deadline_date: '2024-02-20',
      days_until: 12,
      urgency: 'medium',
      is_past: false,
      contract: { id: 'contract-uuid-2', title: 'NDA Agreement' },
    },
  ],
  recent_contracts: [
    {
      id: 'contract-uuid-1',
      title: 'Service Agreement',
      status: 'completed',
      overall_risk_level: 'medium',
      created_at: '2024-01-20T10:30:00Z',
    },
    {
      id: 'contract-uuid-2',
      title: 'NDA Agreement',
      status: 'completed',
      overall_risk_level: 'low',
      created_at: '2024-01-18T08:00:00Z',
    },
  ],
}

describe('dashboard store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('initial state', () => {
    it('has data set to null', () => {
      const store = useDashboardStore()
      expect(store.data).toBeNull()
    })

    it('has loading set to false', () => {
      const store = useDashboardStore()
      expect(store.loading).toBe(false)
    })

    it('has error set to null', () => {
      const store = useDashboardStore()
      expect(store.error).toBeNull()
    })

    it('has hasData computed as false', () => {
      const store = useDashboardStore()
      expect(store.hasData).toBe(false)
    })
  })

  describe('fetchDashboard', () => {
    it('fetches dashboard data successfully', async () => {
      vi.mocked(dashboardService.getDashboard).mockResolvedValue(mockDashboardData)

      const store = useDashboardStore()
      await store.fetchDashboard()

      expect(dashboardService.getDashboard).toHaveBeenCalled()
      expect(store.data).toEqual(mockDashboardData)
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })

    it('sets loading to true while fetching', async () => {
      let resolvePromise: (value: DashboardData) => void
      const promise = new Promise<DashboardData>((resolve) => {
        resolvePromise = resolve
      })
      vi.mocked(dashboardService.getDashboard).mockReturnValue(promise)

      const store = useDashboardStore()
      const fetchPromise = store.fetchDashboard()

      expect(store.loading).toBe(true)

      resolvePromise!(mockDashboardData)
      await fetchPromise

      expect(store.loading).toBe(false)
    })

    it('sets error on failure', async () => {
      const error = { response: { data: { message: 'Failed to load dashboard' } } }
      vi.mocked(dashboardService.getDashboard).mockRejectedValue(error)

      const store = useDashboardStore()
      await expect(store.fetchDashboard()).rejects.toEqual(error)

      expect(store.error).toBe('Failed to load dashboard')
      expect(store.loading).toBe(false)
    })

    it('uses default error message when response has no message', async () => {
      vi.mocked(dashboardService.getDashboard).mockRejectedValue(new Error('Network error'))

      const store = useDashboardStore()
      await expect(store.fetchDashboard()).rejects.toThrow('Network error')

      expect(store.error).toBe('Failed to load dashboard')
    })
  })

  describe('computed properties', () => {
    beforeEach(async () => {
      vi.mocked(dashboardService.getDashboard).mockResolvedValue(mockDashboardData)
    })

    it('stats returns dashboard stats', async () => {
      const store = useDashboardStore()
      expect(store.stats).toBeNull()

      await store.fetchDashboard()
      expect(store.stats).toEqual(mockDashboardData.stats)
    })

    it('upcomingDeadlines returns deadlines array', async () => {
      const store = useDashboardStore()
      expect(store.upcomingDeadlines).toEqual([])

      await store.fetchDashboard()
      expect(store.upcomingDeadlines).toEqual(mockDashboardData.upcoming_deadlines)
      expect(store.upcomingDeadlines).toHaveLength(2)
    })

    it('recentContracts returns contracts array', async () => {
      const store = useDashboardStore()
      expect(store.recentContracts).toEqual([])

      await store.fetchDashboard()
      expect(store.recentContracts).toEqual(mockDashboardData.recent_contracts)
      expect(store.recentContracts).toHaveLength(2)
    })

    it('hasData returns true when data is loaded', async () => {
      const store = useDashboardStore()
      expect(store.hasData).toBe(false)

      await store.fetchDashboard()
      expect(store.hasData).toBe(true)
    })
  })

  describe('clearError', () => {
    it('clears the error', async () => {
      const error = { response: { data: { message: 'Test error' } } }
      vi.mocked(dashboardService.getDashboard).mockRejectedValue(error)

      const store = useDashboardStore()
      await expect(store.fetchDashboard()).rejects.toEqual(error)
      expect(store.error).toBe('Test error')

      store.clearError()
      expect(store.error).toBeNull()
    })
  })

  describe('$reset', () => {
    it('resets the store to initial state', async () => {
      vi.mocked(dashboardService.getDashboard).mockResolvedValue(mockDashboardData)

      const store = useDashboardStore()
      await store.fetchDashboard()

      expect(store.data).not.toBeNull()
      expect(store.hasData).toBe(true)

      store.$reset()

      expect(store.data).toBeNull()
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
      expect(store.hasData).toBe(false)
    })
  })
})

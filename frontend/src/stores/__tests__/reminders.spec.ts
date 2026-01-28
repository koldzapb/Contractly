import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useRemindersStore } from '../reminders'
import * as remindersService from '@/services/reminders'
import type { Reminder, PaginatedResponse } from '@/types'

vi.mock('@/services/reminders')

const mockReminder: Reminder = {
  id: 'reminder-uuid-1',
  title: 'Test Reminder',
  remind_at: '2024-03-10T08:00:00Z',
  days_before: 7,
  channel: 'email',
  status: 'pending',
  status_label: 'Pending',
  sent_at: null,
  is_pending: true,
  is_sent: false,
  is_due: false,
  created_at: '2024-01-01T00:00:00Z',
  updated_at: '2024-01-01T00:00:00Z',
  contract: {
    id: 'contract-uuid-1',
    title: 'Test Contract',
  },
  deadline: {
    id: 'deadline-uuid-1',
    title: 'Payment Due',
    deadline_date: '2024-03-17T00:00:00Z',
    deadline_type: 'payment_due',
    deadline_type_label: 'Payment Due',
  },
}

const mockPaginatedResponse: PaginatedResponse<Reminder> = {
  data: [mockReminder],
  meta: {
    current_page: 1,
    from: 1,
    last_page: 1,
    per_page: 15,
    to: 1,
    total: 1,
  },
  links: {
    first: '/api/reminders?page=1',
    last: '/api/reminders?page=1',
    prev: null,
    next: null,
  },
}

describe('reminders store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('initial state', () => {
    it('has empty reminders array', () => {
      const store = useRemindersStore()
      expect(store.reminders).toEqual([])
    })

    it('has loading set to false', () => {
      const store = useRemindersStore()
      expect(store.loading).toBe(false)
    })

    it('has error set to null', () => {
      const store = useRemindersStore()
      expect(store.error).toBeNull()
    })

    it('has statusFilter set to undefined', () => {
      const store = useRemindersStore()
      expect(store.statusFilter).toBeUndefined()
    })
  })

  describe('fetchReminders', () => {
    it('fetches reminders successfully', async () => {
      vi.mocked(remindersService.getReminders).mockResolvedValue(mockPaginatedResponse)

      const store = useRemindersStore()
      await store.fetchReminders()

      expect(remindersService.getReminders).toHaveBeenCalled()
      expect(store.reminders).toEqual([mockReminder])
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })

    it('updates pagination state', async () => {
      vi.mocked(remindersService.getReminders).mockResolvedValue(mockPaginatedResponse)

      const store = useRemindersStore()
      await store.fetchReminders()

      expect(store.pagination).toEqual({
        currentPage: 1,
        lastPage: 1,
        perPage: 15,
        total: 1,
      })
    })

    it('sets loading to true while fetching', async () => {
      let resolvePromise: (value: PaginatedResponse<Reminder>) => void
      const promise = new Promise<PaginatedResponse<Reminder>>((resolve) => {
        resolvePromise = resolve
      })
      vi.mocked(remindersService.getReminders).mockReturnValue(promise)

      const store = useRemindersStore()
      const fetchPromise = store.fetchReminders()

      expect(store.loading).toBe(true)

      resolvePromise!(mockPaginatedResponse)
      await fetchPromise

      expect(store.loading).toBe(false)
    })

    it('sets error on failure', async () => {
      const error = { response: { data: { message: 'Failed to load reminders' } } }
      vi.mocked(remindersService.getReminders).mockRejectedValue(error)

      const store = useRemindersStore()
      await expect(store.fetchReminders()).rejects.toEqual(error)

      expect(store.error).toBe('Failed to load reminders')
      expect(store.loading).toBe(false)
    })

    it('passes status filter to service', async () => {
      vi.mocked(remindersService.getReminders).mockResolvedValue(mockPaginatedResponse)

      const store = useRemindersStore()
      store.setStatusFilter('pending')
      await store.fetchReminders()

      expect(remindersService.getReminders).toHaveBeenCalledWith({
        page: 1,
        per_page: 15,
        status: 'pending',
      })
    })
  })

  describe('createReminder', () => {
    it('creates reminder and adds to list', async () => {
      vi.mocked(remindersService.createReminder).mockResolvedValue(mockReminder)

      const store = useRemindersStore()
      const data = {
        contract_deadline_id: 'deadline-uuid-1',
        days_before: 7,
        title: 'Test Reminder',
      }

      const result = await store.createReminder(data)

      expect(remindersService.createReminder).toHaveBeenCalledWith(data)
      expect(result).toEqual(mockReminder)
      expect(store.reminders).toHaveLength(1)
      expect(store.reminders[0]).toEqual(mockReminder)
      expect(store.pagination.total).toBe(1)
    })

    it('sets error on failure', async () => {
      const error = { response: { data: { message: 'Failed to create reminder' } } }
      vi.mocked(remindersService.createReminder).mockRejectedValue(error)

      const store = useRemindersStore()
      await expect(
        store.createReminder({ contract_deadline_id: 'test', days_before: 7 }),
      ).rejects.toEqual(error)

      expect(store.error).toBe('Failed to create reminder')
    })
  })

  describe('updateReminder', () => {
    it('updates reminder in list', async () => {
      vi.mocked(remindersService.getReminders).mockResolvedValue(mockPaginatedResponse)
      const updatedReminder = { ...mockReminder, title: 'Updated Title' }
      vi.mocked(remindersService.updateReminder).mockResolvedValue(updatedReminder)

      const store = useRemindersStore()
      await store.fetchReminders()
      const result = await store.updateReminder(mockReminder.id, { title: 'Updated Title' })

      expect(remindersService.updateReminder).toHaveBeenCalledWith(mockReminder.id, {
        title: 'Updated Title',
      })
      expect(result.title).toBe('Updated Title')
      expect(store.reminders[0].title).toBe('Updated Title')
    })
  })

  describe('deleteReminder', () => {
    it('removes reminder from list', async () => {
      vi.mocked(remindersService.getReminders).mockResolvedValue(mockPaginatedResponse)
      vi.mocked(remindersService.deleteReminder).mockResolvedValue()

      const store = useRemindersStore()
      await store.fetchReminders()
      expect(store.reminders).toHaveLength(1)

      await store.deleteReminder(mockReminder.id)

      expect(remindersService.deleteReminder).toHaveBeenCalledWith(mockReminder.id)
      expect(store.reminders).toHaveLength(0)
      expect(store.pagination.total).toBe(0)
    })
  })

  describe('cancelReminder', () => {
    it('cancels reminder and updates status', async () => {
      vi.mocked(remindersService.getReminders).mockResolvedValue(mockPaginatedResponse)
      const cancelledReminder = { ...mockReminder, status: 'cancelled' as const }
      vi.mocked(remindersService.cancelReminder).mockResolvedValue(cancelledReminder)

      const store = useRemindersStore()
      await store.fetchReminders()
      const result = await store.cancelReminder(mockReminder.id)

      expect(remindersService.cancelReminder).toHaveBeenCalledWith(mockReminder.id)
      expect(result.status).toBe('cancelled')
      expect(store.reminders[0].status).toBe('cancelled')
    })
  })

  describe('computed properties', () => {
    it('hasReminders returns true when reminders exist', async () => {
      vi.mocked(remindersService.getReminders).mockResolvedValue(mockPaginatedResponse)

      const store = useRemindersStore()
      expect(store.hasReminders).toBe(false)

      await store.fetchReminders()
      expect(store.hasReminders).toBe(true)
    })

    it('pendingReminders filters by pending status', async () => {
      const reminders = [
        { ...mockReminder, id: '1', status: 'pending' as const },
        { ...mockReminder, id: '2', status: 'sent' as const },
        { ...mockReminder, id: '3', status: 'pending' as const },
      ]
      vi.mocked(remindersService.getReminders).mockResolvedValue({
        ...mockPaginatedResponse,
        data: reminders,
      })

      const store = useRemindersStore()
      await store.fetchReminders()

      expect(store.pendingReminders).toHaveLength(2)
      expect(store.pendingReminders.every((r) => r.status === 'pending')).toBe(true)
    })

    it('sentReminders filters by sent status', async () => {
      const reminders = [
        { ...mockReminder, id: '1', status: 'pending' as const },
        { ...mockReminder, id: '2', status: 'sent' as const },
      ]
      vi.mocked(remindersService.getReminders).mockResolvedValue({
        ...mockPaginatedResponse,
        data: reminders,
      })

      const store = useRemindersStore()
      await store.fetchReminders()

      expect(store.sentReminders).toHaveLength(1)
      expect(store.sentReminders[0].status).toBe('sent')
    })
  })

  describe('setStatusFilter', () => {
    it('sets the status filter', () => {
      const store = useRemindersStore()
      store.setStatusFilter('pending')
      expect(store.statusFilter).toBe('pending')
    })

    it('clears the status filter with undefined', () => {
      const store = useRemindersStore()
      store.setStatusFilter('pending')
      store.setStatusFilter(undefined)
      expect(store.statusFilter).toBeUndefined()
    })
  })

  describe('clearError', () => {
    it('clears the error', async () => {
      const error = { response: { data: { message: 'Test error' } } }
      vi.mocked(remindersService.getReminders).mockRejectedValue(error)

      const store = useRemindersStore()
      await expect(store.fetchReminders()).rejects.toEqual(error)
      expect(store.error).toBe('Test error')

      store.clearError()
      expect(store.error).toBeNull()
    })
  })

  describe('goToPage', () => {
    it('fetches reminders for specific page', async () => {
      vi.mocked(remindersService.getReminders).mockResolvedValue(mockPaginatedResponse)

      const store = useRemindersStore()
      await store.goToPage(2)

      expect(remindersService.getReminders).toHaveBeenCalledWith({
        page: 2,
        per_page: 15,
        status: undefined,
      })
    })
  })
})

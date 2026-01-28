import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { Reminder } from '@/types'
import * as remindersService from '@/services/reminders'
import type { ReminderListParams } from '@/services/reminders'
import type { CreateReminderData, UpdateReminderData } from '@/types'

export const useRemindersStore = defineStore('reminders', () => {
  // State
  const reminders = ref<Reminder[]>([])
  const currentReminder = ref<Reminder | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Pagination state
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  })

  // Filter state
  const statusFilter = ref<string | undefined>(undefined)

  // Computed
  const hasReminders = computed(() => reminders.value.length > 0)
  const hasMorePages = computed(() => pagination.value.currentPage < pagination.value.lastPage)
  const pendingReminders = computed(() => reminders.value.filter((r) => r.status === 'pending'))
  const sentReminders = computed(() => reminders.value.filter((r) => r.status === 'sent'))

  /**
   * Fetch paginated reminders
   */
  async function fetchReminders(params: ReminderListParams = {}): Promise<void> {
    loading.value = true
    error.value = null

    try {
      const finalStatus = params.status ?? statusFilter.value
      const response = await remindersService.getReminders({
        page: params.page || pagination.value.currentPage,
        per_page: params.per_page || pagination.value.perPage,
        ...(finalStatus ? { status: finalStatus } : {}),
      })

      reminders.value = response.data
      pagination.value = {
        currentPage: response.meta.current_page,
        lastPage: response.meta.last_page,
        perPage: response.meta.per_page,
        total: response.meta.total,
      }
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to load reminders'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch a single reminder
   */
  async function fetchReminder(id: string): Promise<Reminder> {
    loading.value = true
    error.value = null

    try {
      currentReminder.value = await remindersService.getReminder(id)
      return currentReminder.value
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to load reminder'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Create a new reminder
   */
  async function createReminder(data: CreateReminderData): Promise<Reminder> {
    loading.value = true
    error.value = null

    try {
      const reminder = await remindersService.createReminder(data)

      // Add to the beginning of the list
      reminders.value.unshift(reminder)
      pagination.value.total += 1

      return reminder
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to create reminder'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Update a reminder
   */
  async function updateReminder(id: string, data: UpdateReminderData): Promise<Reminder> {
    loading.value = true
    error.value = null

    try {
      const updated = await remindersService.updateReminder(id, data)

      // Update in list
      const index = reminders.value.findIndex((r) => r.id === id)
      if (index !== -1) {
        reminders.value[index] = updated
      }

      // Update current if it matches
      if (currentReminder.value?.id === id) {
        currentReminder.value = updated
      }

      return updated
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to update reminder'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete a reminder
   */
  async function deleteReminder(id: string): Promise<void> {
    loading.value = true
    error.value = null

    try {
      await remindersService.deleteReminder(id)

      // Remove from list
      reminders.value = reminders.value.filter((r) => r.id !== id)
      pagination.value.total = Math.max(0, pagination.value.total - 1)

      // Clear current if it matches
      if (currentReminder.value?.id === id) {
        currentReminder.value = null
      }
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to delete reminder'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Cancel a pending reminder
   */
  async function cancelReminder(id: string): Promise<Reminder> {
    loading.value = true
    error.value = null

    try {
      const updated = await remindersService.cancelReminder(id)

      // Update in list
      const index = reminders.value.findIndex((r) => r.id === id)
      if (index !== -1) {
        reminders.value[index] = updated
      }

      // Update current if it matches
      if (currentReminder.value?.id === id) {
        currentReminder.value = updated
      }

      return updated
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to cancel reminder'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Set status filter
   */
  function setStatusFilter(status: string | undefined): void {
    statusFilter.value = status
  }

  /**
   * Clear error
   */
  function clearError(): void {
    error.value = null
  }

  /**
   * Go to specific page
   */
  async function goToPage(page: number): Promise<void> {
    await fetchReminders({ page })
  }

  return {
    // State
    reminders,
    currentReminder,
    loading,
    error,
    pagination,
    statusFilter,

    // Computed
    hasReminders,
    hasMorePages,
    pendingReminders,
    sentReminders,

    // Actions
    fetchReminders,
    fetchReminder,
    createReminder,
    updateReminder,
    deleteReminder,
    cancelReminder,
    setStatusFilter,
    clearError,
    goToPage,
  }
})

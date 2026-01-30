import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { NotificationPreferences, UpdateNotificationPreferencesData } from '@/types'
import {
  getNotificationPreferences,
  updateNotificationPreferences,
  resetNotificationPreferences,
} from '@/services/notifications'

export const useNotificationsStore = defineStore('notifications', () => {
  // State
  const preferences = ref<NotificationPreferences | null>(null)
  const loading = ref(false)
  const saving = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const hasPreferences = computed(() => preferences.value !== null)

  // Actions
  async function fetchPreferences(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      preferences.value = await getNotificationPreferences()
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load preferences'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updatePreferences(data: UpdateNotificationPreferencesData): Promise<void> {
    saving.value = true
    error.value = null

    try {
      preferences.value = await updateNotificationPreferences(data)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to update preferences'
      throw err
    } finally {
      saving.value = false
    }
  }

  async function resetPreferences(): Promise<void> {
    saving.value = true
    error.value = null

    try {
      await resetNotificationPreferences()
      // Refresh preferences after reset
      preferences.value = await getNotificationPreferences()
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to reset preferences'
      throw err
    } finally {
      saving.value = false
    }
  }

  function clearError(): void {
    error.value = null
  }

  return {
    // State
    preferences,
    loading,
    saving,
    error,
    // Getters
    hasPreferences,
    // Actions
    fetchPreferences,
    updatePreferences,
    resetPreferences,
    clearError,
  }
})

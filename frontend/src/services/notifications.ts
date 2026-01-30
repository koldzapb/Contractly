import api from './api'
import type { NotificationPreferences, UpdateNotificationPreferencesData } from '@/types'

/**
 * Get notification preferences for the current user
 */
export async function getNotificationPreferences(): Promise<NotificationPreferences> {
  const response = await api.get<{ data: NotificationPreferences }>('/notifications/preferences')
  return response.data.data
}

/**
 * Update notification preferences
 */
export async function updateNotificationPreferences(
  data: UpdateNotificationPreferencesData,
): Promise<NotificationPreferences> {
  const response = await api.put<{ data: NotificationPreferences }>(
    '/notifications/preferences',
    data,
  )
  return response.data.data
}

/**
 * Reset notification preferences to defaults
 */
export async function resetNotificationPreferences(): Promise<void> {
  await api.post('/notifications/preferences/reset')
}

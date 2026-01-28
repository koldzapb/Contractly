import api from './api'
import type {
  Reminder,
  ApiResponse,
  PaginatedResponse,
  CreateReminderData,
  UpdateReminderData,
} from '@/types'

export interface ReminderListParams {
  page?: number
  per_page?: number
  status?: string
}

/**
 * Get paginated list of reminders
 */
export async function getReminders(
  params: ReminderListParams = {},
): Promise<PaginatedResponse<Reminder>> {
  const response = await api.get<PaginatedResponse<Reminder>>('/reminders', {
    params: {
      page: params.page || 1,
      per_page: params.per_page || 15,
      status: params.status,
    },
  })

  return response.data
}

/**
 * Get a single reminder by ID
 */
export async function getReminder(id: string): Promise<Reminder> {
  const response = await api.get<ApiResponse<Reminder>>(`/reminders/${id}`)

  return response.data.data
}

/**
 * Create a new reminder
 */
export async function createReminder(data: CreateReminderData): Promise<Reminder> {
  const response = await api.post<ApiResponse<Reminder>>('/reminders', data)

  return response.data.data
}

/**
 * Update a reminder
 */
export async function updateReminder(id: string, data: UpdateReminderData): Promise<Reminder> {
  const response = await api.put<ApiResponse<Reminder>>(`/reminders/${id}`, data)

  return response.data.data
}

/**
 * Delete a reminder
 */
export async function deleteReminder(id: string): Promise<void> {
  await api.delete(`/reminders/${id}`)
}

/**
 * Cancel a pending reminder
 */
export async function cancelReminder(id: string): Promise<Reminder> {
  const response = await api.post<ApiResponse<Reminder>>(`/reminders/${id}/cancel`)

  return response.data.data
}

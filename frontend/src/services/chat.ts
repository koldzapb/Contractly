import api from './api'
import type { ChatMessage, ApiResponse, SendMessageData, ClearChatResponse } from '@/types'

/**
 * Get chat history for a contract
 */
export async function getChatHistory(contractId: string): Promise<ChatMessage[]> {
  const response = await api.get<{ data: ChatMessage[] }>(`/contracts/${contractId}/chat`)

  return response.data.data
}

/**
 * Send a message in the contract chat
 */
export async function sendMessage(contractId: string, data: SendMessageData): Promise<ChatMessage> {
  const response = await api.post<ApiResponse<ChatMessage>>(`/contracts/${contractId}/chat`, data)

  return response.data.data
}

/**
 * Clear chat history for a contract
 */
export async function clearChatHistory(contractId: string): Promise<ClearChatResponse> {
  const response = await api.delete<ClearChatResponse>(`/contracts/${contractId}/chat`)

  return response.data
}

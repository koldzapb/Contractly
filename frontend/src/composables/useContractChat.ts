import { ref, computed } from 'vue'
import type { ChatMessage } from '@/types'
import * as chatService from '@/services/chat'

export function useContractChat(contractId: string) {
  // State
  const messages = ref<ChatMessage[]>([])
  const loading = ref(false)
  const sending = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const hasMessages = computed(() => messages.value.length > 0)
  const lastMessage = computed(() =>
    messages.value.length > 0 ? messages.value[messages.value.length - 1] : null,
  )

  /**
   * Fetch chat history
   */
  async function fetchHistory(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      messages.value = await chatService.getChatHistory(contractId)
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to load chat history'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Send a message
   */
  async function sendMessage(message: string): Promise<ChatMessage> {
    sending.value = true
    error.value = null

    // Add optimistic user message
    const tempUserMessage: ChatMessage = {
      id: `temp-${Date.now()}`,
      role: 'user',
      role_label: 'User',
      content: message,
      tokens_used: null,
      is_off_topic: false,
      is_user_message: true,
      is_assistant_message: false,
      created_at: new Date().toISOString(),
    }
    messages.value.push(tempUserMessage)

    try {
      const assistantMessage = await chatService.sendMessage(contractId, { message })

      // Replace temp message with actual message from response
      // The backend saves the user message, so we get the assistant message back
      // We need to also add the user message from the server ideally, but the API only returns the assistant message
      // So we keep our temp message and add the assistant response
      messages.value.push(assistantMessage)

      return assistantMessage
    } catch (e: unknown) {
      // Remove the optimistic message on error
      messages.value = messages.value.filter((m) => m.id !== tempUserMessage.id)

      const err = e as { response?: { data?: { message?: string; code?: string } } }
      const errorMessage = err.response?.data?.message ?? 'Failed to send message'
      const errorCode = err.response?.data?.code

      if (errorCode === 'ANALYSIS_NOT_COMPLETE') {
        error.value = 'Contract analysis must be completed before chatting.'
      } else if (errorCode === 'AI_ERROR') {
        error.value = 'AI service is temporarily unavailable. Please try again.'
      } else {
        error.value = errorMessage
      }

      throw e
    } finally {
      sending.value = false
    }
  }

  /**
   * Clear chat history
   */
  async function clearHistory(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      await chatService.clearChatHistory(contractId)
      messages.value = []
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to clear chat history'
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

  return {
    // State
    messages,
    loading,
    sending,
    error,

    // Computed
    hasMessages,
    lastMessage,

    // Actions
    fetchHistory,
    sendMessage,
    clearHistory,
    clearError,
  }
}

import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useContractChat } from '../useContractChat'
import * as chatService from '@/services/chat'
import type { ChatMessage } from '@/types'

// Mock the chat service
vi.mock('@/services/chat', () => ({
  getChatHistory: vi.fn(),
  sendMessage: vi.fn(),
  clearChatHistory: vi.fn(),
}))

const mockMessages: ChatMessage[] = [
  {
    id: 'msg-1',
    role: 'user',
    role_label: 'User',
    content: 'What are the payment terms?',
    tokens_used: null,
    is_off_topic: false,
    is_user_message: true,
    is_assistant_message: false,
    created_at: '2024-01-28T10:00:00Z',
  },
  {
    id: 'msg-2',
    role: 'assistant',
    role_label: 'Assistant',
    content: 'Based on Section 5...',
    tokens_used: 150,
    is_off_topic: false,
    is_user_message: false,
    is_assistant_message: true,
    created_at: '2024-01-28T10:00:05Z',
  },
]

describe('useContractChat', () => {
  const contractId = 'contract-123'

  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('initial state', () => {
    it('starts with empty messages', () => {
      const { messages } = useContractChat(contractId)

      expect(messages.value).toEqual([])
    })

    it('starts with loading false', () => {
      const { loading } = useContractChat(contractId)

      expect(loading.value).toBe(false)
    })

    it('starts with sending false', () => {
      const { sending } = useContractChat(contractId)

      expect(sending.value).toBe(false)
    })

    it('starts with no error', () => {
      const { error } = useContractChat(contractId)

      expect(error.value).toBeNull()
    })
  })

  describe('computed properties', () => {
    it('hasMessages returns false when empty', () => {
      const { hasMessages } = useContractChat(contractId)

      expect(hasMessages.value).toBe(false)
    })

    it('lastMessage returns null when empty', () => {
      const { lastMessage } = useContractChat(contractId)

      expect(lastMessage.value).toBeNull()
    })
  })

  describe('fetchHistory', () => {
    it('fetches chat history successfully', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue(mockMessages)

      const { messages, fetchHistory, hasMessages } = useContractChat(contractId)
      await fetchHistory()

      expect(chatService.getChatHistory).toHaveBeenCalledWith(contractId)
      expect(messages.value).toEqual(mockMessages)
      expect(hasMessages.value).toBe(true)
    })

    it('sets loading state during fetch', async () => {
      vi.mocked(chatService.getChatHistory).mockImplementation(
        () => new Promise((resolve) => setTimeout(() => resolve([]), 100)),
      )

      const { loading, fetchHistory } = useContractChat(contractId)

      const fetchPromise = fetchHistory()
      expect(loading.value).toBe(true)

      await fetchPromise
      expect(loading.value).toBe(false)
    })

    it('sets error on failure', async () => {
      const errorResponse = {
        response: { data: { message: 'Failed to load' } },
      }
      vi.mocked(chatService.getChatHistory).mockRejectedValue(errorResponse)

      const { error, fetchHistory } = useContractChat(contractId)

      await expect(fetchHistory()).rejects.toEqual(errorResponse)
      expect(error.value).toBe('Failed to load')
    })
  })

  describe('sendMessage', () => {
    const newAssistantMessage: ChatMessage = {
      id: 'msg-3',
      role: 'assistant',
      role_label: 'Assistant',
      content: 'The answer is...',
      tokens_used: 100,
      is_off_topic: false,
      is_user_message: false,
      is_assistant_message: true,
      created_at: '2024-01-28T10:01:00Z',
    }

    it('sends message successfully', async () => {
      vi.mocked(chatService.sendMessage).mockResolvedValue(newAssistantMessage)

      const { messages, sendMessage } = useContractChat(contractId)
      await sendMessage('What are the terms?')

      expect(chatService.sendMessage).toHaveBeenCalledWith(contractId, {
        message: 'What are the terms?',
      })
      // Should have optimistic user message + assistant response
      expect(messages.value).toHaveLength(2)
    })

    it('adds optimistic user message', async () => {
      vi.mocked(chatService.sendMessage).mockImplementation(
        () => new Promise((resolve) => setTimeout(() => resolve(newAssistantMessage), 100)),
      )

      const { messages, sendMessage } = useContractChat(contractId)
      const sendPromise = sendMessage('Hello')

      // Optimistic message should be added immediately
      expect(messages.value).toHaveLength(1)
      expect(messages.value[0].content).toBe('Hello')
      expect(messages.value[0].is_user_message).toBe(true)

      await sendPromise
    })

    it('sets sending state during send', async () => {
      vi.mocked(chatService.sendMessage).mockImplementation(
        () => new Promise((resolve) => setTimeout(() => resolve(newAssistantMessage), 100)),
      )

      const { sending, sendMessage } = useContractChat(contractId)

      const sendPromise = sendMessage('Hello')
      expect(sending.value).toBe(true)

      await sendPromise
      expect(sending.value).toBe(false)
    })

    it('removes optimistic message on error', async () => {
      const errorResponse = { response: { data: { message: 'AI unavailable' } } }
      vi.mocked(chatService.sendMessage).mockRejectedValue(errorResponse)

      const { messages, sendMessage } = useContractChat(contractId)

      await expect(sendMessage('Hello')).rejects.toEqual(errorResponse)
      expect(messages.value).toHaveLength(0)
    })

    it('sets error on failure', async () => {
      const errorResponse = { response: { data: { message: 'AI unavailable' } } }
      vi.mocked(chatService.sendMessage).mockRejectedValue(errorResponse)

      const { error, sendMessage } = useContractChat(contractId)

      await expect(sendMessage('Hello')).rejects.toEqual(errorResponse)
      expect(error.value).toBe('AI unavailable')
    })
  })

  describe('clearHistory', () => {
    it('clears messages', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue(mockMessages)
      vi.mocked(chatService.clearChatHistory).mockResolvedValue({
        message: 'Cleared',
        messages_deleted: 2,
      })

      const { messages, fetchHistory, clearHistory } = useContractChat(contractId)
      await fetchHistory()
      expect(messages.value).toHaveLength(2)

      await clearHistory()
      expect(messages.value).toHaveLength(0)
    })

    it('calls clearChatHistory service', async () => {
      vi.mocked(chatService.clearChatHistory).mockResolvedValue({
        message: 'Cleared',
        messages_deleted: 0,
      })

      const { clearHistory } = useContractChat(contractId)
      await clearHistory()

      expect(chatService.clearChatHistory).toHaveBeenCalledWith(contractId)
    })
  })

  describe('clearError', () => {
    it('clears error state', async () => {
      const errorResponse = { response: { data: { message: 'Error' } } }
      vi.mocked(chatService.getChatHistory).mockRejectedValue(errorResponse)

      const { error, fetchHistory, clearError } = useContractChat(contractId)

      await expect(fetchHistory()).rejects.toEqual(errorResponse)
      expect(error.value).toBe('Error')

      clearError()
      expect(error.value).toBeNull()
    })
  })
})

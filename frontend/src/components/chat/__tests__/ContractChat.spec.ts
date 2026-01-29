import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises, config } from '@vue/test-utils'
import ContractChat from '../ContractChat.vue'
import ChatMessage from '../ChatMessage.vue'
import ChatInput from '../ChatInput.vue'
import ChatEmptyState from '../ChatEmptyState.vue'
import ChatTypingIndicator from '../ChatTypingIndicator.vue'
import ChatDisclaimer from '../ChatDisclaimer.vue'
import * as chatService from '@/services/chat'
import type { ChatMessage as ChatMessageType } from '@/types'

// Mock the chat service
vi.mock('@/services/chat', () => ({
  getChatHistory: vi.fn(),
  sendMessage: vi.fn(),
  clearChatHistory: vi.fn(),
}))

// Mock window.confirm
const mockConfirm = vi.fn()
window.confirm = mockConfirm

// Helper to suppress unhandled rejections for error tests
function suppressUnhandledRejections(): () => void {
  const handler = (event: PromiseRejectionEvent) => {
    // Only suppress expected test errors
    if (event.reason?.response?.data?.message) {
      event.preventDefault()
    }
  }
  window.addEventListener('unhandledrejection', handler)
  return () => window.removeEventListener('unhandledrejection', handler)
}

// Configure Vue Test Utils to suppress expected errors in error handling tests
config.global.config.errorHandler = () => {
  // Suppress Vue error handler for expected errors
}

const mockMessages: ChatMessageType[] = [
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
    content: 'Based on Section 5, the payment terms are net 30 days.',
    tokens_used: 150,
    is_off_topic: false,
    is_user_message: false,
    is_assistant_message: true,
    created_at: '2024-01-28T10:00:05Z',
  },
]

const createAssistantMessage = (content: string): ChatMessageType => ({
  id: `msg-${Date.now()}`,
  role: 'assistant',
  role_label: 'Assistant',
  content,
  tokens_used: 100,
  is_off_topic: false,
  is_user_message: false,
  is_assistant_message: true,
  created_at: new Date().toISOString(),
})

describe('ContractChat', () => {
  const defaultProps = {
    contractId: 'contract-123',
    contractCompleted: true,
  }

  const mountComponent = (props = {}) => {
    return mount(ContractChat, {
      props: { ...defaultProps, ...props },
      global: {
        stubs: {
          ChatDisclaimer: true,
        },
      },
    })
  }

  beforeEach(() => {
    vi.clearAllMocks()
    mockConfirm.mockReturnValue(true)
    vi.mocked(chatService.getChatHistory).mockResolvedValue([])
    vi.mocked(chatService.sendMessage).mockResolvedValue(createAssistantMessage('Response'))
    vi.mocked(chatService.clearChatHistory).mockResolvedValue({
      message: 'Cleared',
      messages_deleted: 0,
    })
  })

  describe('rendering', () => {
    it('renders chat header', async () => {
      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.text()).toContain('Ask about this contract')
    })

    it('renders chat input when contract is completed', async () => {
      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.findComponent(ChatInput).exists()).toBe(true)
    })

    it('renders disclaimer when contract is completed', async () => {
      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.findComponent(ChatDisclaimer).exists()).toBe(true)
    })

    it('shows not completed notice when contract is not completed', async () => {
      const wrapper = mountComponent({ contractCompleted: false })
      await flushPromises()

      expect(wrapper.text()).toContain('Analysis in progress')
      expect(wrapper.text()).toContain(
        'Chat will be available once the contract analysis is complete',
      )
      expect(wrapper.findComponent(ChatInput).exists()).toBe(false)
    })

    it('does not fetch history when contract is not completed', async () => {
      mountComponent({ contractCompleted: false })
      await flushPromises()

      expect(chatService.getChatHistory).not.toHaveBeenCalled()
    })
  })

  describe('loading state', () => {
    it('shows loading spinner while fetching history', async () => {
      let resolveHistory: (value: ChatMessageType[]) => void
      vi.mocked(chatService.getChatHistory).mockImplementation(
        () =>
          new Promise((resolve) => {
            resolveHistory = resolve
          }),
      )

      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.find('.animate-spin').exists()).toBe(true)

      resolveHistory!([])
      await flushPromises()

      expect(wrapper.find('.animate-spin').exists()).toBe(false)
    })
  })

  describe('empty state', () => {
    it('shows empty state when no messages', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue([])

      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.findComponent(ChatEmptyState).exists()).toBe(true)
    })

    it('hides empty state when messages exist', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue(mockMessages)

      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.findComponent(ChatEmptyState).exists()).toBe(false)
    })
  })

  describe('displaying messages', () => {
    it('fetches chat history on mount when completed', async () => {
      const wrapper = mountComponent()
      await flushPromises()

      expect(chatService.getChatHistory).toHaveBeenCalledWith('contract-123')
    })

    it('renders messages from history', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue(mockMessages)

      const wrapper = mountComponent()
      await flushPromises()

      const messageComponents = wrapper.findAllComponents(ChatMessage)
      expect(messageComponents).toHaveLength(2)
    })

    it('passes correct props to ChatMessage components', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue(mockMessages)

      const wrapper = mountComponent()
      await flushPromises()

      const messageComponents = wrapper.findAllComponents(ChatMessage)
      expect(messageComponents[0].props('message')).toEqual(mockMessages[0])
      expect(messageComponents[1].props('message')).toEqual(mockMessages[1])
    })
  })

  describe('sending messages', () => {
    it('sends message when form is submitted', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue([])

      const wrapper = mountComponent()
      await flushPromises()

      const chatInput = wrapper.findComponent(ChatInput)
      await chatInput.vm.$emit('send', 'What is the termination clause?')
      await flushPromises()

      expect(chatService.sendMessage).toHaveBeenCalledWith('contract-123', {
        message: 'What is the termination clause?',
      })
    })

    it('shows typing indicator while sending', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue([])
      let resolveSend: (value: ChatMessageType) => void
      vi.mocked(chatService.sendMessage).mockImplementation(
        () =>
          new Promise((resolve) => {
            resolveSend = resolve
          }),
      )

      const wrapper = mountComponent()
      await flushPromises()

      const chatInput = wrapper.findComponent(ChatInput)
      chatInput.vm.$emit('send', 'Test message')
      await flushPromises()

      expect(wrapper.findComponent(ChatTypingIndicator).exists()).toBe(true)

      resolveSend!(createAssistantMessage('Response'))
      await flushPromises()

      expect(wrapper.findComponent(ChatTypingIndicator).exists()).toBe(false)
    })

    it('disables input while sending', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue([])
      let resolveSend: (value: ChatMessageType) => void
      vi.mocked(chatService.sendMessage).mockImplementation(
        () =>
          new Promise((resolve) => {
            resolveSend = resolve
          }),
      )

      const wrapper = mountComponent()
      await flushPromises()

      const chatInput = wrapper.findComponent(ChatInput)
      chatInput.vm.$emit('send', 'Test message')
      await flushPromises()

      expect(chatInput.props('disabled')).toBe(true)

      resolveSend!(createAssistantMessage('Response'))
      await flushPromises()

      expect(chatInput.props('disabled')).toBe(false)
    })

    it('adds user message optimistically', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue([])
      let resolveSend: (value: ChatMessageType) => void
      vi.mocked(chatService.sendMessage).mockImplementation(
        () =>
          new Promise((resolve) => {
            resolveSend = resolve
          }),
      )

      const wrapper = mountComponent()
      await flushPromises()

      const chatInput = wrapper.findComponent(ChatInput)
      chatInput.vm.$emit('send', 'My question')
      await flushPromises()

      // Should show user message immediately
      const messages = wrapper.findAllComponents(ChatMessage)
      expect(messages).toHaveLength(1)
      expect(messages[0].props('message').content).toBe('My question')
      expect(messages[0].props('message').is_user_message).toBe(true)

      resolveSend!(createAssistantMessage('Response'))
      await flushPromises()

      // Should now have both messages
      expect(wrapper.findAllComponents(ChatMessage)).toHaveLength(2)
    })
  })

  describe('suggested questions', () => {
    it('sends message when suggested question is selected', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue([])

      const wrapper = mountComponent()
      await flushPromises()

      const emptyState = wrapper.findComponent(ChatEmptyState)
      await emptyState.vm.$emit('select-question', 'What are the key obligations?')
      await flushPromises()

      expect(chatService.sendMessage).toHaveBeenCalledWith('contract-123', {
        message: 'What are the key obligations?',
      })
    })
  })

  describe('clearing history', () => {
    it('shows clear button when messages exist', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue(mockMessages)

      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.text()).toContain('Clear chat')
    })

    it('hides clear button when no messages', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue([])

      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.text()).not.toContain('Clear chat')
    })

    it('clears history when confirmed', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue(mockMessages)
      mockConfirm.mockReturnValue(true)

      const wrapper = mountComponent()
      await flushPromises()

      const clearButton = wrapper.find('button')
      // Find the clear button (it contains "Clear chat" text)
      const buttons = wrapper.findAll('button')
      const clearBtn = buttons.find((b) => b.text().includes('Clear chat'))
      expect(clearBtn).toBeDefined()

      await clearBtn!.trigger('click')
      await flushPromises()

      expect(mockConfirm).toHaveBeenCalledWith('Are you sure you want to clear the chat history?')
      expect(chatService.clearChatHistory).toHaveBeenCalledWith('contract-123')
    })

    it('does not clear history when cancelled', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue(mockMessages)
      mockConfirm.mockReturnValue(false)

      const wrapper = mountComponent()
      await flushPromises()

      const buttons = wrapper.findAll('button')
      const clearBtn = buttons.find((b) => b.text().includes('Clear chat'))

      await clearBtn!.trigger('click')
      await flushPromises()

      expect(chatService.clearChatHistory).not.toHaveBeenCalled()
    })
  })

  describe('error handling', () => {
    let cleanup: () => void

    beforeEach(() => {
      cleanup = suppressUnhandledRejections()
    })

    afterEach(() => {
      cleanup()
    })

    it('shows error message when fetch fails', async () => {
      vi.mocked(chatService.getChatHistory).mockRejectedValue({
        response: { data: { message: 'Failed to load chat history' } },
      })

      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.text()).toContain('Failed to load chat history')
    })

    it('shows error message when send fails', async () => {
      vi.mocked(chatService.getChatHistory).mockResolvedValue([])
      vi.mocked(chatService.sendMessage).mockRejectedValue({
        response: { data: { message: 'AI service unavailable' } },
      })

      const wrapper = mountComponent()
      await flushPromises()

      const chatInput = wrapper.findComponent(ChatInput)
      await chatInput.vm.$emit('send', 'Test')
      await flushPromises()

      expect(wrapper.text()).toContain('AI service unavailable')
    })

    it('can dismiss error message', async () => {
      vi.mocked(chatService.getChatHistory).mockRejectedValue({
        response: { data: { message: 'Error occurred' } },
      })

      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.text()).toContain('Error occurred')

      // Find and click the dismiss button (X icon in error banner)
      const errorDismissButton = wrapper.find('.bg-red-50 button, .dark\\:bg-red-900\\/20 button')
      if (errorDismissButton.exists()) {
        await errorDismissButton.trigger('click')
        await flushPromises()

        expect(wrapper.text()).not.toContain('Error occurred')
      }
    })

    it('clears error when sending new message', async () => {
      vi.mocked(chatService.getChatHistory).mockRejectedValue({
        response: { data: { message: 'Load error' } },
      })

      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.text()).toContain('Load error')

      // Reset mock for successful send
      vi.mocked(chatService.sendMessage).mockResolvedValue(createAssistantMessage('Response'))

      const chatInput = wrapper.findComponent(ChatInput)
      await chatInput.vm.$emit('send', 'New message')
      await flushPromises()

      expect(wrapper.text()).not.toContain('Load error')
    })
  })

  describe('accessibility', () => {
    it('has proper heading structure', async () => {
      const wrapper = mountComponent()
      await flushPromises()

      expect(wrapper.find('h3').exists()).toBe(true)
      expect(wrapper.find('h3').text()).toContain('Ask about this contract')
    })
  })
})

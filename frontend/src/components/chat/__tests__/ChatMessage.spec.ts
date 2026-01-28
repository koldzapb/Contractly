import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ChatMessage from '../ChatMessage.vue'
import type { ChatMessage as ChatMessageType } from '@/types'

const mockUserMessage: ChatMessageType = {
  id: 'msg-1',
  role: 'user',
  role_label: 'User',
  content: 'What are the payment terms?',
  tokens_used: null,
  is_off_topic: false,
  is_user_message: true,
  is_assistant_message: false,
  created_at: '2024-01-28T10:00:00Z',
}

const mockAssistantMessage: ChatMessageType = {
  id: 'msg-2',
  role: 'assistant',
  role_label: 'Assistant',
  content: 'Based on Section 5, the payment terms are...',
  tokens_used: 150,
  is_off_topic: false,
  is_user_message: false,
  is_assistant_message: true,
  created_at: '2024-01-28T10:00:05Z',
}

describe('ChatMessage', () => {
  const mountComponent = (message: ChatMessageType = mockUserMessage) => {
    return mount(ChatMessage, {
      props: { message },
    })
  }

  describe('user message', () => {
    it('renders user message content', () => {
      const wrapper = mountComponent(mockUserMessage)

      expect(wrapper.text()).toContain('What are the payment terms?')
    })

    it('shows user role label', () => {
      const wrapper = mountComponent(mockUserMessage)

      expect(wrapper.text()).toContain('User')
    })

    it('aligns user message to the right', () => {
      const wrapper = mountComponent(mockUserMessage)

      const container = wrapper.find('.flex')
      expect(container.classes()).toContain('items-end')
    })

    it('applies user bubble styling', () => {
      const wrapper = mountComponent(mockUserMessage)

      const bubble = wrapper.find('.rounded-2xl')
      expect(bubble.classes()).toContain('bg-indigo-600')
    })

    it('does not show tokens for user messages', () => {
      const wrapper = mountComponent(mockUserMessage)

      expect(wrapper.text()).not.toContain('tokens')
    })
  })

  describe('assistant message', () => {
    it('renders assistant message content', () => {
      const wrapper = mountComponent(mockAssistantMessage)

      expect(wrapper.text()).toContain('Based on Section 5')
    })

    it('shows assistant role label', () => {
      const wrapper = mountComponent(mockAssistantMessage)

      expect(wrapper.text()).toContain('Assistant')
    })

    it('aligns assistant message to the left', () => {
      const wrapper = mountComponent(mockAssistantMessage)

      const container = wrapper.find('.flex')
      expect(container.classes()).toContain('items-start')
    })

    it('applies assistant bubble styling', () => {
      const wrapper = mountComponent(mockAssistantMessage)

      const bubble = wrapper.find('.rounded-2xl')
      expect(bubble.classes()).toContain('bg-gray-100')
    })

    it('shows tokens used for assistant messages', () => {
      const wrapper = mountComponent(mockAssistantMessage)

      expect(wrapper.text()).toContain('150 tokens')
    })
  })

  describe('off-topic message', () => {
    it('applies off-topic styling', () => {
      const offTopicMessage: ChatMessageType = {
        ...mockAssistantMessage,
        is_off_topic: true,
      }
      const wrapper = mountComponent(offTopicMessage)

      const bubble = wrapper.find('.rounded-2xl')
      expect(bubble.classes()).toContain('bg-amber-50')
    })
  })

  describe('timestamp', () => {
    it('displays formatted time', () => {
      const wrapper = mountComponent(mockUserMessage)

      // The time format depends on locale, just check it's present
      const text = wrapper.text()
      expect(text).toMatch(/\d{1,2}:\d{2}/)
    })
  })
})

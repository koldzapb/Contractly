import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ChatInput from '../ChatInput.vue'

describe('ChatInput', () => {
  const mountComponent = (props = {}) => {
    return mount(ChatInput, {
      props,
    })
  }

  describe('rendering', () => {
    it('renders textarea', () => {
      const wrapper = mountComponent()

      expect(wrapper.find('textarea').exists()).toBe(true)
    })

    it('renders send button', () => {
      const wrapper = mountComponent()

      expect(wrapper.find('button[type="submit"]').exists()).toBe(true)
    })

    it('shows default placeholder', () => {
      const wrapper = mountComponent()

      const textarea = wrapper.find('textarea')
      expect(textarea.attributes('placeholder')).toBe('Type your question...')
    })

    it('shows custom placeholder', () => {
      const wrapper = mountComponent({ placeholder: 'Ask something...' })

      const textarea = wrapper.find('textarea')
      expect(textarea.attributes('placeholder')).toBe('Ask something...')
    })
  })

  describe('disabled state', () => {
    it('disables textarea when disabled prop is true', () => {
      const wrapper = mountComponent({ disabled: true })

      const textarea = wrapper.find('textarea')
      expect(textarea.attributes('disabled')).toBeDefined()
    })

    it('disables send button when disabled prop is true', () => {
      const wrapper = mountComponent({ disabled: true })

      const button = wrapper.find('button[type="submit"]')
      expect(button.attributes('disabled')).toBeDefined()
    })
  })

  describe('send functionality', () => {
    it('does not emit when message is empty', async () => {
      const wrapper = mountComponent()

      const button = wrapper.find('button[type="submit"]')
      await button.trigger('click')

      expect(wrapper.emitted('send')).toBeFalsy()
    })

    it('emits send event with message when submitted', async () => {
      const wrapper = mountComponent()

      const textarea = wrapper.find('textarea')
      await textarea.setValue('What are the payment terms?')

      const form = wrapper.find('form')
      await form.trigger('submit')

      expect(wrapper.emitted('send')).toBeTruthy()
      expect(wrapper.emitted('send')![0]).toEqual(['What are the payment terms?'])
    })

    it('clears input after sending', async () => {
      const wrapper = mountComponent()

      const textarea = wrapper.find('textarea')
      await textarea.setValue('What are the payment terms?')

      const form = wrapper.find('form')
      await form.trigger('submit')

      expect((textarea.element as HTMLTextAreaElement).value).toBe('')
    })

    it('trims whitespace from message', async () => {
      const wrapper = mountComponent()

      const textarea = wrapper.find('textarea')
      await textarea.setValue('  What are the payment terms?  ')

      const form = wrapper.find('form')
      await form.trigger('submit')

      expect(wrapper.emitted('send')![0]).toEqual(['What are the payment terms?'])
    })

    it('does not emit for whitespace-only message', async () => {
      const wrapper = mountComponent()

      const textarea = wrapper.find('textarea')
      await textarea.setValue('   ')

      const form = wrapper.find('form')
      await form.trigger('submit')

      expect(wrapper.emitted('send')).toBeFalsy()
    })
  })

  describe('send button state', () => {
    it('disables send button when message is empty', () => {
      const wrapper = mountComponent()

      const button = wrapper.find('button[type="submit"]')
      expect(button.attributes('disabled')).toBeDefined()
    })

    it('enables send button when message has content', async () => {
      const wrapper = mountComponent()

      const textarea = wrapper.find('textarea')
      await textarea.setValue('Hello')

      const button = wrapper.find('button[type="submit"]')
      expect(button.attributes('disabled')).toBeUndefined()
    })
  })
})

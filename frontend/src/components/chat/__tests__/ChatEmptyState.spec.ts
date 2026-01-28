import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ChatEmptyState from '../ChatEmptyState.vue'

describe('ChatEmptyState', () => {
  const mountComponent = () => {
    return mount(ChatEmptyState)
  }

  describe('rendering', () => {
    it('renders title', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Ask about your contract')
    })

    it('renders description', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('I can help you understand this contract')
    })

    it('renders suggested questions section', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Suggested questions')
    })

    it('renders all suggested questions', () => {
      const wrapper = mountComponent()
      const buttons = wrapper.findAll('button')

      expect(buttons.length).toBe(5)
    })
  })

  describe('suggested questions', () => {
    it('includes key obligations question', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('What are the key obligations I need to fulfill?')
    })

    it('includes termination question', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('What happens if I want to terminate early?')
    })

    it('includes auto-renewal question', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Are there any automatic renewal clauses?')
    })

    it('includes payment terms question', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('What are the payment terms?')
    })

    it('includes high risk question', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('What are the highest risk clauses?')
    })
  })

  describe('interactions', () => {
    it('emits selectQuestion event when question is clicked', async () => {
      const wrapper = mountComponent()

      const button = wrapper.find('button')
      await button.trigger('click')

      expect(wrapper.emitted('selectQuestion')).toBeTruthy()
    })

    it('emits the selected question text', async () => {
      const wrapper = mountComponent()

      const buttons = wrapper.findAll('button')
      await buttons[0].trigger('click')

      expect(wrapper.emitted('selectQuestion')![0]).toEqual([
        'What are the key obligations I need to fulfill?',
      ])
    })

    it('emits different questions for different buttons', async () => {
      const wrapper = mountComponent()

      const buttons = wrapper.findAll('button')
      await buttons[1].trigger('click')

      expect(wrapper.emitted('selectQuestion')![0]).toEqual([
        'What happens if I want to terminate early?',
      ])
    })
  })
})

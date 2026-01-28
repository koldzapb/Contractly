import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ClauseCard from '../ClauseCard.vue'
import type { ContractClause } from '@/types'

const mockClause: ContractClause = {
  id: 'clause-123',
  clause_type: 'termination',
  clause_type_label: 'Termination',
  original_text: 'Either party may terminate this agreement with 30 days written notice.',
  plain_explanation:
    'You or the company can end the contract by giving 30 days advance notice in writing.',
  risk_level: 'medium',
  risk_reason: 'Short notice period may not be sufficient for finding new employment.',
  page_number: 5,
  position_index: 3,
}

describe('ClauseCard', () => {
  const mountComponent = (clause: ContractClause = mockClause) => {
    return mount(ClauseCard, {
      props: { clause },
      global: {
        stubs: {
          RiskBadge: {
            template: '<span class="risk-badge" :class="level">{{ level }}</span>',
            props: ['level', 'size', 'showLabel'],
          },
        },
      },
    })
  }

  describe('basic rendering', () => {
    it('renders clause type label', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Termination')
    })

    it('renders plain explanation', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('end the contract by giving 30 days advance notice')
    })

    it('renders page number', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Page 5')
    })

    it('does not render page number when null', () => {
      const wrapper = mountComponent({
        ...mockClause,
        page_number: null,
      })

      expect(wrapper.text()).not.toContain('Page')
    })
  })

  describe('risk level styling', () => {
    it('renders risk badge', () => {
      const wrapper = mountComponent()

      expect(wrapper.find('.risk-badge').exists()).toBe(true)
    })

    it('applies green border for low risk', () => {
      const wrapper = mountComponent({
        ...mockClause,
        risk_level: 'low',
      })

      expect(wrapper.find('.border-l-green-500').exists()).toBe(true)
    })

    it('applies yellow border for medium risk', () => {
      const wrapper = mountComponent({
        ...mockClause,
        risk_level: 'medium',
      })

      expect(wrapper.find('.border-l-yellow-500').exists()).toBe(true)
    })

    it('applies red border for high risk', () => {
      const wrapper = mountComponent({
        ...mockClause,
        risk_level: 'high',
      })

      expect(wrapper.find('.border-l-red-500').exists()).toBe(true)
    })
  })

  describe('risk reason', () => {
    it('renders risk reason when provided', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Short notice period may not be sufficient')
    })

    it('does not render risk reason when null', () => {
      const wrapper = mountComponent({
        ...mockClause,
        risk_reason: null,
      })

      expect(wrapper.text()).not.toContain('Short notice period')
    })
  })

  describe('expandable original text', () => {
    it('shows expand button when original text exists', () => {
      const wrapper = mountComponent()

      expect(wrapper.find('button').exists()).toBe(true)
    })

    it('does not show expand button when no original text', () => {
      const wrapper = mountComponent({
        ...mockClause,
        original_text: '',
      })

      expect(wrapper.find('button').exists()).toBe(false)
    })

    it('does not show original text initially', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).not.toContain('Either party may terminate')
    })

    it('shows original text after clicking expand button', async () => {
      const wrapper = mountComponent()

      const button = wrapper.find('button')
      await button.trigger('click')

      expect(wrapper.text()).toContain('Original Text')
      expect(wrapper.text()).toContain('Either party may terminate')
    })

    it('hides original text after clicking again', async () => {
      const wrapper = mountComponent()

      const button = wrapper.find('button')
      await button.trigger('click')
      await button.trigger('click')

      expect(wrapper.text()).not.toContain('Either party may terminate')
    })

    it('rotates chevron icon when expanded', async () => {
      const wrapper = mountComponent()

      const button = wrapper.find('button')
      const svg = button.find('svg')

      expect(svg.classes()).not.toContain('rotate-180')

      await button.trigger('click')

      expect(svg.classes()).toContain('rotate-180')
    })
  })
})

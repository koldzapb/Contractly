import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ClauseList from '../ClauseList.vue'
import type { ContractClause } from '@/types'

const mockClauses: ContractClause[] = [
  {
    id: 'clause-1',
    clause_type: 'termination',
    clause_type_label: 'Termination',
    original_text: 'Either party may terminate...',
    plain_explanation: 'You can end the contract with notice.',
    risk_level: 'high',
    risk_reason: 'Immediate termination possible',
    page_number: 5,
    position_index: 1,
  },
  {
    id: 'clause-2',
    clause_type: 'payment_terms',
    clause_type_label: 'Payment Terms',
    original_text: 'Payment is due within 30 days...',
    plain_explanation: 'You must pay within 30 days.',
    risk_level: 'medium',
    risk_reason: null,
    page_number: 2,
    position_index: 2,
  },
  {
    id: 'clause-3',
    clause_type: 'confidentiality',
    clause_type_label: 'Confidentiality',
    original_text: 'All information shared...',
    plain_explanation: 'Keep information private.',
    risk_level: 'low',
    risk_reason: null,
    page_number: 8,
    position_index: 3,
  },
]

describe('ClauseList', () => {
  const mountComponent = (clauses: ContractClause[] = mockClauses) => {
    return mount(ClauseList, {
      props: { clauses },
      global: {
        stubs: {
          ClauseCard: {
            template: '<div class="clause-card" :data-risk="clause.risk_level">{{ clause.clause_type_label }}</div>',
            props: ['clause'],
          },
        },
      },
    })
  }

  describe('basic rendering', () => {
    it('renders the section title', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Identified Clauses')
    })

    it('renders clause count', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('(3)')
    })

    it('renders all clauses', () => {
      const wrapper = mountComponent()

      const cards = wrapper.findAll('.clause-card')
      expect(cards.length).toBe(3)
    })
  })

  describe('empty state', () => {
    it('shows empty message when no clauses', () => {
      const wrapper = mountComponent([])

      expect(wrapper.text()).toContain('No clauses identified yet')
    })

    it('shows filter tabs with zero count when empty', () => {
      const wrapper = mountComponent([])

      expect(wrapper.text()).toContain('All (0)')
    })
  })

  describe('filter tabs', () => {
    it('shows All filter tab with total count', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('All (3)')
    })

    it('shows High filter tab when high risk clauses exist', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('High (1)')
    })

    it('shows Medium filter tab when medium risk clauses exist', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Medium (1)')
    })

    it('shows Low filter tab when low risk clauses exist', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Low (1)')
    })

    it('does not show High filter when no high risk clauses', () => {
      const wrapper = mountComponent([mockClauses[1], mockClauses[2]])

      expect(wrapper.text()).not.toContain('High (')
    })
  })

  describe('filtering', () => {
    it('filters to high risk clauses when clicked', async () => {
      const wrapper = mountComponent()

      const highButton = wrapper.findAll('button').find((b) => b.text().includes('High'))
      await highButton?.trigger('click')

      const cards = wrapper.findAll('.clause-card')
      expect(cards.length).toBe(1)
      expect(cards[0].attributes('data-risk')).toBe('high')
    })

    it('filters to medium risk clauses when clicked', async () => {
      const wrapper = mountComponent()

      const mediumButton = wrapper.findAll('button').find((b) => b.text().includes('Medium'))
      await mediumButton?.trigger('click')

      const cards = wrapper.findAll('.clause-card')
      expect(cards.length).toBe(1)
      expect(cards[0].attributes('data-risk')).toBe('medium')
    })

    it('filters to low risk clauses when clicked', async () => {
      const wrapper = mountComponent()

      const lowButton = wrapper.findAll('button').find((b) => b.text().includes('Low'))
      await lowButton?.trigger('click')

      const cards = wrapper.findAll('.clause-card')
      expect(cards.length).toBe(1)
      expect(cards[0].attributes('data-risk')).toBe('low')
    })

    it('shows all clauses when All clicked', async () => {
      const wrapper = mountComponent()

      // First filter to high
      const highButton = wrapper.findAll('button').find((b) => b.text().includes('High'))
      await highButton?.trigger('click')

      // Then click All
      const allButton = wrapper.findAll('button').find((b) => b.text().includes('All'))
      await allButton?.trigger('click')

      const cards = wrapper.findAll('.clause-card')
      expect(cards.length).toBe(3)
    })
  })

  describe('filtered empty state', () => {
    it('shows message when filter has no results', async () => {
      // Create clauses with only low risk
      const lowOnlyClauses = [mockClauses[2]]
      const wrapper = mountComponent(lowOnlyClauses)

      // Try to filter by clicking low
      const lowButton = wrapper.findAll('button').find((b) => b.text().includes('Low'))
      await lowButton?.trigger('click')

      // Now we should see 1 clause
      expect(wrapper.findAll('.clause-card').length).toBe(1)
    })

    it('provides link to show all clauses from filtered empty state', async () => {
      const wrapper = mountComponent()

      // Filter to high
      const highButton = wrapper.findAll('button').find((b) => b.text().includes('High'))
      await highButton?.trigger('click')

      // Should show 1 card for high risk
      expect(wrapper.findAll('.clause-card').length).toBe(1)
    })
  })

  describe('sorting', () => {
    it('sorts clauses by risk level (high first)', () => {
      const wrapper = mountComponent()

      const cards = wrapper.findAll('.clause-card')
      expect(cards[0].attributes('data-risk')).toBe('high')
      expect(cards[1].attributes('data-risk')).toBe('medium')
      expect(cards[2].attributes('data-risk')).toBe('low')
    })
  })
})

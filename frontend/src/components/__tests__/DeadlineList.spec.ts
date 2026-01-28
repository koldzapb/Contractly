import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import DeadlineList from '../DeadlineList.vue'
import type { ContractDeadline } from '@/types'

const mockDeadlines: ContractDeadline[] = [
  {
    id: 'deadline-1',
    deadline_type: 'payment_due',
    deadline_type_label: 'Payment Due',
    title: 'First Payment',
    description: 'Initial payment',
    deadline_date: '2024-03-15',
    source_text: null,
    is_recurring: false,
    recurrence_pattern: null,
    days_until: 30,
    urgency: 'medium',
    is_past: false,
  },
  {
    id: 'deadline-2',
    deadline_type: 'renewal_date',
    deadline_type_label: 'Renewal Date',
    title: 'Contract Renewal',
    description: 'Annual renewal',
    deadline_date: '2024-01-10',
    source_text: null,
    is_recurring: true,
    recurrence_pattern: 'Yearly',
    days_until: -35,
    urgency: 'overdue',
    is_past: true,
  },
  {
    id: 'deadline-3',
    deadline_type: 'termination_notice',
    deadline_type_label: 'Termination Notice',
    title: 'Notice Period',
    description: 'Must notify 30 days before',
    deadline_date: '2024-02-20',
    source_text: null,
    is_recurring: false,
    recurrence_pattern: null,
    days_until: 6,
    urgency: 'critical',
    is_past: false,
  },
]

describe('DeadlineList', () => {
  const mountComponent = (deadlines: ContractDeadline[] = mockDeadlines) => {
    return mount(DeadlineList, {
      props: { deadlines },
      global: {
        stubs: {
          DeadlineCard: {
            template:
              '<div class="deadline-card" :data-urgency="deadline.urgency" :data-past="deadline.is_past">{{ deadline.title }}</div>',
            props: ['deadline'],
          },
        },
      },
    })
  }

  describe('basic rendering', () => {
    it('renders section title', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Important Deadlines')
    })

    it('renders deadline count', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('(3)')
    })

    it('renders all deadlines', () => {
      const wrapper = mountComponent()

      const cards = wrapper.findAll('.deadline-card')
      expect(cards.length).toBe(3)
    })
  })

  describe('empty state', () => {
    it('shows empty message when no deadlines', () => {
      const wrapper = mountComponent([])

      expect(wrapper.text()).toContain('No deadlines identified in this contract')
    })

    it('shows calendar icon in empty state', () => {
      const wrapper = mountComponent([])

      expect(wrapper.find('svg').exists()).toBe(true)
    })
  })

  describe('grouping', () => {
    it('renders Upcoming section for future deadlines', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Upcoming')
    })

    it('renders Past section for past deadlines', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Past')
    })

    it('does not render Upcoming section when all deadlines are past', () => {
      const pastOnly = mockDeadlines.map((d) => ({ ...d, is_past: true }))
      const wrapper = mountComponent(pastOnly)

      expect(wrapper.text()).not.toContain('Upcoming')
      expect(wrapper.text()).toContain('Past')
    })

    it('does not render Past section when all deadlines are upcoming', () => {
      const upcomingOnly = mockDeadlines.map((d) => ({ ...d, is_past: false }))
      const wrapper = mountComponent(upcomingOnly)

      expect(wrapper.text()).toContain('Upcoming')
      expect(wrapper.text()).not.toContain('Past')
    })
  })

  describe('sorting', () => {
    it('sorts deadlines by urgency (overdue first)', () => {
      const wrapper = mountComponent()

      const cards = wrapper.findAll('.deadline-card')
      const urgencies = cards.map((c) => c.attributes('data-urgency'))

      // First should be overdue or critical
      expect(['overdue', 'critical']).toContain(urgencies[0])
    })

    it('places overdue before critical', () => {
      const deadlinesWithUrgency: ContractDeadline[] = [
        { ...mockDeadlines[0], urgency: 'critical', is_past: false },
        { ...mockDeadlines[1], urgency: 'overdue', is_past: false },
      ]
      const wrapper = mountComponent(deadlinesWithUrgency)

      const cards = wrapper.findAll('.deadline-card')
      expect(cards[0].attributes('data-urgency')).toBe('overdue')
      expect(cards[1].attributes('data-urgency')).toBe('critical')
    })
  })

  describe('past deadlines styling', () => {
    it('applies opacity to past deadlines section', () => {
      const wrapper = mountComponent()

      const pastSection = wrapper.find('.opacity-60')
      expect(pastSection.exists()).toBe(true)
    })
  })

  describe('deadline separation', () => {
    it('separates upcoming and past deadlines correctly', () => {
      const wrapper = mountComponent()

      // Get upcoming cards (not in opacity-60 container)
      const upcomingSection = wrapper
        .findAll('.space-y-3')
        .filter((el) => !el.classes().includes('opacity-60'))
      const pastSection = wrapper.find('.opacity-60')

      expect(upcomingSection.length).toBeGreaterThan(0)
      expect(pastSection.exists()).toBe(true)
    })
  })
})

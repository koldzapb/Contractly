import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import DeadlineCard from '../DeadlineCard.vue'
import type { ContractDeadline } from '@/types'

const mockDeadline: ContractDeadline = {
  id: 'deadline-123',
  deadline_type: 'payment_due',
  deadline_type_label: 'Payment Due',
  title: 'First Payment Due',
  description: 'Initial payment must be received by this date.',
  deadline_date: '2024-03-15',
  source_text: 'Payment shall be due on March 15, 2024',
  is_recurring: false,
  recurrence_pattern: null,
  days_until: 30,
  urgency: 'medium',
  is_past: false,
}

describe('DeadlineCard', () => {
  beforeEach(() => {
    vi.useFakeTimers()
    vi.setSystemTime(new Date('2024-02-14'))
  })

  afterEach(() => {
    vi.useRealTimers()
  })

  const mountComponent = (deadline: ContractDeadline = mockDeadline) => {
    return mount(DeadlineCard, {
      props: { deadline },
    })
  }

  describe('basic rendering', () => {
    it('renders deadline title', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('First Payment Due')
    })

    it('renders deadline type label', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Payment Due')
    })

    it('renders description', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Initial payment must be received')
    })

    it('does not render description when null', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        description: null,
      })

      expect(wrapper.text()).not.toContain('Initial payment')
    })

    it('renders formatted date', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Mar')
      expect(wrapper.text()).toContain('15')
      expect(wrapper.text()).toContain('2024')
    })

    it('renders "No date specified" when date is null', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        deadline_date: null,
      })

      expect(wrapper.text()).toContain('No date specified')
    })
  })

  describe('urgency styling', () => {
    it('applies overdue styling', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        urgency: 'overdue',
      })

      expect(wrapper.find('.bg-red-50').exists()).toBe(true)
      expect(wrapper.text()).toContain('Overdue')
    })

    it('applies critical styling', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        urgency: 'critical',
      })

      expect(wrapper.find('.bg-orange-50').exists()).toBe(true)
      expect(wrapper.text()).toContain('Critical')
    })

    it('applies high urgency styling', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        urgency: 'high',
      })

      expect(wrapper.find('.bg-yellow-50').exists()).toBe(true)
      expect(wrapper.text()).toContain('Soon')
    })

    it('applies medium urgency styling', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        urgency: 'medium',
      })

      expect(wrapper.find('.bg-blue-50').exists()).toBe(true)
    })

    it('applies low urgency styling', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        urgency: 'low',
      })

      expect(wrapper.find('.border-gray-200').exists()).toBe(true)
    })

    it('handles unknown urgency', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        urgency: 'unknown',
      })

      expect(wrapper.find('.border-gray-200').exists()).toBe(true)
    })
  })

  describe('days until', () => {
    it('shows "Today" for 0 days', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        days_until: 0,
      })

      expect(wrapper.text()).toContain('Today')
    })

    it('shows "Tomorrow" for 1 day', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        days_until: 1,
      })

      expect(wrapper.text()).toContain('Tomorrow')
    })

    it('shows "In X days" for future dates', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        days_until: 30,
      })

      expect(wrapper.text()).toContain('In 30 days')
    })

    it('shows "X days ago" for past dates', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        days_until: -5,
      })

      expect(wrapper.text()).toContain('5 days ago')
    })

    it('does not show days text when null', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        days_until: null,
      })

      expect(wrapper.text()).not.toContain('days')
      expect(wrapper.text()).not.toContain('Today')
    })
  })

  describe('recurring', () => {
    it('shows recurring indicator when is_recurring is true', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        is_recurring: true,
        recurrence_pattern: 'Monthly',
      })

      expect(wrapper.text()).toContain('Monthly')
    })

    it('shows default "Recurring" when no pattern specified', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        is_recurring: true,
        recurrence_pattern: null,
      })

      expect(wrapper.text()).toContain('Recurring')
    })

    it('does not show recurring indicator when false', () => {
      const wrapper = mountComponent({
        ...mockDeadline,
        is_recurring: false,
      })

      expect(wrapper.text()).not.toContain('Recurring')
    })
  })
})

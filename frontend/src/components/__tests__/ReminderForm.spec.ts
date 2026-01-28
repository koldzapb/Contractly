import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ReminderForm from '../ReminderForm.vue'
import type { ContractDeadline, Reminder } from '@/types'

const mockDeadline: ContractDeadline = {
  id: 'deadline-uuid-123',
  deadline_type: 'payment_due',
  deadline_type_label: 'Payment Due',
  title: 'Monthly Payment',
  description: 'Monthly payment for services',
  deadline_date: '2024-03-17T00:00:00Z',
  source_text: null,
  is_recurring: false,
  recurrence_pattern: null,
  days_until: 30,
  urgency: 'medium',
  is_past: false,
}

const mockReminder: Reminder = {
  id: 'reminder-uuid-123',
  title: 'Custom Reminder Title',
  remind_at: '2024-03-10T08:00:00Z',
  days_before: 14,
  channel: 'email',
  status: 'pending',
  status_label: 'Pending',
  sent_at: null,
  is_pending: true,
  is_sent: false,
  is_due: false,
  created_at: '2024-01-15T10:30:00Z',
  updated_at: '2024-01-15T10:30:00Z',
}

describe('ReminderForm', () => {
  const mountComponent = (
    props: {
      deadline?: ContractDeadline
      reminder?: Reminder
      loading?: boolean
    } = {},
  ) => {
    return mount(ReminderForm, {
      props: {
        deadline: props.deadline,
        reminder: props.reminder,
        loading: props.loading ?? false,
      },
    })
  }

  describe('create mode (with deadline)', () => {
    it('renders deadline info', () => {
      const wrapper = mountComponent({ deadline: mockDeadline })

      expect(wrapper.text()).toContain('Monthly Payment')
      expect(wrapper.text()).toContain('Payment Due')
    })

    it('has 7 days as default selection', () => {
      const wrapper = mountComponent({ deadline: mockDeadline })

      const selectedButton = wrapper.find('button.border-indigo-500')
      expect(selectedButton.text()).toBe('7 days')
    })

    it('allows selecting different day options', async () => {
      const wrapper = mountComponent({ deadline: mockDeadline })

      const dayButtons = wrapper.findAll('button').filter((b) => b.text().includes('day'))
      const day14Button = dayButtons.find((b) => b.text() === '14 days')

      await day14Button?.trigger('click')

      const selectedButton = wrapper.find('button.border-indigo-500')
      expect(selectedButton.text()).toBe('14 days')
    })

    it('allows entering custom days', async () => {
      const wrapper = mountComponent({ deadline: mockDeadline })

      const input = wrapper.find('input[type="number"]')
      await input.setValue(21)

      expect(wrapper.vm.daysBefore).toBe(21)
    })

    it('shows estimated remind date', () => {
      const wrapper = mountComponent({ deadline: mockDeadline })

      expect(wrapper.text()).toContain('You will be reminded on')
    })

    it('submits create data', async () => {
      const wrapper = mountComponent({ deadline: mockDeadline })

      const titleInput = wrapper.find('input[type="text"]')
      await titleInput.setValue('My Custom Reminder')

      await wrapper.find('form').trigger('submit')

      expect(wrapper.emitted('submit')).toBeTruthy()
      const submitData = wrapper.emitted('submit')![0][0]
      expect(submitData).toEqual({
        contract_deadline_id: 'deadline-uuid-123',
        days_before: 7,
        title: 'My Custom Reminder',
      })
    })

    it('omits title if empty', async () => {
      const wrapper = mountComponent({ deadline: mockDeadline })

      await wrapper.find('form').trigger('submit')

      const submitData = wrapper.emitted('submit')![0][0]
      expect(submitData).toEqual({
        contract_deadline_id: 'deadline-uuid-123',
        days_before: 7,
      })
    })
  })

  describe('edit mode (with reminder)', () => {
    it('shows existing days_before value', () => {
      const wrapper = mountComponent({ reminder: mockReminder })

      const selectedButton = wrapper.find('button.border-indigo-500')
      expect(selectedButton.text()).toBe('14 days')
    })

    it('shows existing title', () => {
      const wrapper = mountComponent({ reminder: mockReminder })

      const titleInput = wrapper.find('input[type="text"]')
      expect(titleInput.element.value).toBe('Custom Reminder Title')
    })

    it('does not show deadline info', () => {
      const wrapper = mountComponent({ reminder: mockReminder })

      // Should not have the deadline info box
      expect(wrapper.find('.bg-gray-50').exists()).toBe(false)
    })

    it('submits only changed data', async () => {
      const wrapper = mountComponent({ reminder: mockReminder })

      // Change days to something different
      const day30Button = wrapper.findAll('button').find((b) => b.text() === '30 days')
      await day30Button?.trigger('click')

      await wrapper.find('form').trigger('submit')

      const submitData = wrapper.emitted('submit')![0][0]
      expect(submitData).toEqual({
        days_before: 30,
      })
    })

    it('shows Update button text', () => {
      const wrapper = mountComponent({ reminder: mockReminder })

      const submitButton = wrapper.find('button[type="submit"]')
      expect(submitButton.text()).toContain('Update Reminder')
    })
  })

  describe('actions', () => {
    it('emits cancel event when cancel clicked', async () => {
      const wrapper = mountComponent({ deadline: mockDeadline })

      const cancelButton = wrapper.findAll('button').find((b) => b.text() === 'Cancel')
      await cancelButton?.trigger('click')

      expect(wrapper.emitted('cancel')).toBeTruthy()
    })

    it('disables submit button when loading', () => {
      const wrapper = mountComponent({
        deadline: mockDeadline,
        loading: true,
      })

      const submitButton = wrapper.find('button[type="submit"]')
      expect(submitButton.attributes('disabled')).toBeDefined()
    })

    it('disables cancel button when loading', () => {
      const wrapper = mountComponent({
        deadline: mockDeadline,
        loading: true,
      })

      const cancelButton = wrapper.findAll('button').find((b) => b.text() === 'Cancel')
      expect(cancelButton?.attributes('disabled')).toBeDefined()
    })

    it('shows spinner when loading', () => {
      const wrapper = mountComponent({
        deadline: mockDeadline,
        loading: true,
      })

      expect(wrapper.find('svg.animate-spin').exists()).toBe(true)
    })
  })

  describe('validation', () => {
    it('disables submit when days_before is less than 1', async () => {
      const wrapper = mountComponent({ deadline: mockDeadline })

      const input = wrapper.find('input[type="number"]')
      await input.setValue(0)

      const submitButton = wrapper.find('button[type="submit"]')
      expect(submitButton.attributes('disabled')).toBeDefined()
    })

    it('disables submit when days_before is greater than 365', async () => {
      const wrapper = mountComponent({ deadline: mockDeadline })

      const input = wrapper.find('input[type="number"]')
      await input.setValue(400)

      const submitButton = wrapper.find('button[type="submit"]')
      expect(submitButton.attributes('disabled')).toBeDefined()
    })
  })
})

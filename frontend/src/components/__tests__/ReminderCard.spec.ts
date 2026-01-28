import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import ReminderCard from '../ReminderCard.vue'
import type { Reminder } from '@/types'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: { template: '<div>Home</div>' } },
    { path: '/contracts/:id', component: { template: '<div>Contract</div>' } },
  ],
})

const mockReminder: Reminder = {
  id: 'reminder-uuid-123',
  title: 'Payment Due Reminder',
  remind_at: '2024-03-10T08:00:00Z',
  days_before: 7,
  channel: 'email',
  status: 'pending',
  status_label: 'Pending',
  sent_at: null,
  is_pending: true,
  is_sent: false,
  is_due: false,
  created_at: '2024-01-15T10:30:00Z',
  updated_at: '2024-01-15T10:30:00Z',
  contract: {
    id: 'contract-uuid-123',
    title: 'Test Contract',
  },
  deadline: {
    id: 'deadline-uuid-123',
    title: 'Payment Due',
    deadline_date: '2024-03-17T00:00:00Z',
    deadline_type: 'payment_due',
    deadline_type_label: 'Payment Due',
  },
}

describe('ReminderCard', () => {
  beforeEach(async () => {
    router.push('/')
    await router.isReady()
  })

  const mountComponent = (reminder: Reminder = mockReminder) => {
    return mount(ReminderCard, {
      props: { reminder },
      global: {
        plugins: [router],
      },
    })
  }

  describe('basic rendering', () => {
    it('renders reminder title', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Payment Due Reminder')
    })

    it('renders contract link', () => {
      const wrapper = mountComponent()

      const link = wrapper.find('a')
      expect(link.text()).toContain('Test Contract')
      expect(link.attributes('href')).toBe('/contracts/contract-uuid-123')
    })

    it('renders days before badge', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('7 days before')
    })

    it('renders deadline title', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Payment Due')
    })
  })

  describe('status badge', () => {
    it('shows pending status', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Pending')
    })

    it('shows sent status', () => {
      const sentReminder: Reminder = {
        ...mockReminder,
        status: 'sent',
        is_pending: false,
        is_sent: true,
        sent_at: '2024-03-10T08:00:00Z',
      }
      const wrapper = mountComponent(sentReminder)

      expect(wrapper.text()).toContain('Sent')
    })

    it('shows cancelled status', () => {
      const cancelledReminder: Reminder = {
        ...mockReminder,
        status: 'cancelled',
        is_pending: false,
      }
      const wrapper = mountComponent(cancelledReminder)

      expect(wrapper.text()).toContain('Cancelled')
    })

    it('shows failed status', () => {
      const failedReminder: Reminder = {
        ...mockReminder,
        status: 'failed',
        is_pending: false,
      }
      const wrapper = mountComponent(failedReminder)

      expect(wrapper.text()).toContain('Failed')
    })
  })

  describe('actions', () => {
    it('shows cancel button for pending reminders', () => {
      const wrapper = mountComponent()

      const buttons = wrapper.findAll('button')
      const cancelButton = buttons.find((b) => b.attributes('title') === 'Cancel reminder')
      expect(cancelButton).toBeDefined()
    })

    it('does not show cancel button for sent reminders', () => {
      const sentReminder: Reminder = {
        ...mockReminder,
        status: 'sent',
        is_pending: false,
        is_sent: true,
      }
      const wrapper = mountComponent(sentReminder)

      const buttons = wrapper.findAll('button')
      const cancelButton = buttons.find((b) => b.attributes('title') === 'Cancel reminder')
      expect(cancelButton).toBeUndefined()
    })

    it('emits cancel event when cancel button clicked', async () => {
      const wrapper = mountComponent()

      const buttons = wrapper.findAll('button')
      const cancelButton = buttons.find((b) => b.attributes('title') === 'Cancel reminder')
      await cancelButton?.trigger('click')

      expect(wrapper.emitted('cancel')).toBeTruthy()
      expect(wrapper.emitted('cancel')![0]).toEqual(['reminder-uuid-123'])
    })

    it('emits delete event when delete button clicked', async () => {
      const wrapper = mountComponent()

      const buttons = wrapper.findAll('button')
      const deleteButton = buttons.find((b) => b.attributes('title') === 'Delete reminder')
      await deleteButton?.trigger('click')

      expect(wrapper.emitted('delete')).toBeTruthy()
      expect(wrapper.emitted('delete')![0]).toEqual(['reminder-uuid-123'])
    })
  })

  describe('sent timestamp', () => {
    it('does not show sent timestamp for pending reminders', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).not.toContain('Sent:')
    })

    it('shows sent timestamp for sent reminders', () => {
      const sentReminder: Reminder = {
        ...mockReminder,
        status: 'sent',
        is_sent: true,
        sent_at: '2024-03-10T08:00:00Z',
      }
      const wrapper = mountComponent(sentReminder)

      expect(wrapper.text()).toContain('Sent:')
    })
  })

  describe('without contract or deadline', () => {
    it('renders without contract', () => {
      const reminderWithoutContract: Reminder = {
        ...mockReminder,
        contract: undefined,
      }
      const wrapper = mountComponent(reminderWithoutContract)

      expect(wrapper.find('a').exists()).toBe(false)
    })

    it('renders without deadline', () => {
      const reminderWithoutDeadline: Reminder = {
        ...mockReminder,
        deadline: undefined,
      }
      const wrapper = mountComponent(reminderWithoutDeadline)

      expect(wrapper.text()).not.toContain('Deadline:')
    })
  })
})

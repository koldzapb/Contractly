import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import ReminderList from '../ReminderList.vue'
import ReminderCard from '../ReminderCard.vue'
import type { Reminder } from '@/types'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: { template: '<div>Home</div>' } },
    { path: '/contracts/:id', component: { template: '<div>Contract</div>' } },
  ],
})

const createMockReminder = (overrides: Partial<Reminder> = {}): Reminder => ({
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
  ...overrides,
})

describe('ReminderList', () => {
  beforeEach(async () => {
    router.push('/')
    await router.isReady()
  })

  const mountComponent = (props: {
    reminders?: Reminder[]
    loading?: boolean
    pagination?: { currentPage: number; lastPage: number; total: number }
  } = {}) => {
    return mount(ReminderList, {
      props: {
        reminders: props.reminders ?? [],
        loading: props.loading ?? false,
        pagination: props.pagination,
      },
      global: {
        plugins: [router],
      },
    })
  }

  describe('loading state', () => {
    it('shows loading spinner when loading', () => {
      const wrapper = mountComponent({ loading: true })

      expect(wrapper.text()).toContain('Loading reminders...')
      expect(wrapper.find('svg.animate-spin').exists()).toBe(true)
    })

    it('does not show reminders while loading', () => {
      const wrapper = mountComponent({
        loading: true,
        reminders: [createMockReminder()],
      })

      expect(wrapper.findComponent(ReminderCard).exists()).toBe(false)
    })
  })

  describe('empty state', () => {
    it('shows empty message when no reminders', () => {
      const wrapper = mountComponent({ reminders: [] })

      expect(wrapper.text()).toContain('No reminders set up yet')
    })

    it('shows helpful text about creating reminders', () => {
      const wrapper = mountComponent({ reminders: [] })

      expect(wrapper.text()).toContain('Create a reminder from a contract deadline')
    })
  })

  describe('rendering reminders', () => {
    it('renders reminder cards for each reminder', () => {
      const reminders = [
        createMockReminder({ id: '1' }),
        createMockReminder({ id: '2' }),
        createMockReminder({ id: '3' }),
      ]
      const wrapper = mountComponent({ reminders })

      const cards = wrapper.findAllComponents(ReminderCard)
      expect(cards).toHaveLength(3)
    })

    it('passes correct props to reminder cards', () => {
      const reminder = createMockReminder()
      const wrapper = mountComponent({ reminders: [reminder] })

      const card = wrapper.findComponent(ReminderCard)
      expect(card.props('reminder')).toEqual(reminder)
    })
  })

  describe('events', () => {
    it('emits cancel event from child card', async () => {
      const reminder = createMockReminder()
      const wrapper = mountComponent({ reminders: [reminder] })

      const card = wrapper.findComponent(ReminderCard)
      card.vm.$emit('cancel', reminder.id)

      expect(wrapper.emitted('cancel')).toBeTruthy()
      expect(wrapper.emitted('cancel')![0]).toEqual([reminder.id])
    })

    it('emits delete event from child card', async () => {
      const reminder = createMockReminder()
      const wrapper = mountComponent({ reminders: [reminder] })

      const card = wrapper.findComponent(ReminderCard)
      card.vm.$emit('delete', reminder.id)

      expect(wrapper.emitted('delete')).toBeTruthy()
      expect(wrapper.emitted('delete')![0]).toEqual([reminder.id])
    })
  })

  describe('pagination', () => {
    it('does not show pagination when only one page', () => {
      const wrapper = mountComponent({
        reminders: [createMockReminder()],
        pagination: { currentPage: 1, lastPage: 1, total: 1 },
      })

      expect(wrapper.text()).not.toContain('Page 1 of 1')
    })

    it('shows pagination when multiple pages', () => {
      const wrapper = mountComponent({
        reminders: [createMockReminder()],
        pagination: { currentPage: 1, lastPage: 3, total: 45 },
      })

      expect(wrapper.text()).toContain('Page 1 of 3')
      expect(wrapper.text()).toContain('45 total')
    })

    it('disables previous button on first page', () => {
      const wrapper = mountComponent({
        reminders: [createMockReminder()],
        pagination: { currentPage: 1, lastPage: 3, total: 45 },
      })

      const prevButton = wrapper.findAll('button').find((b) => b.text() === 'Previous')
      expect(prevButton?.attributes('disabled')).toBeDefined()
    })

    it('disables next button on last page', () => {
      const wrapper = mountComponent({
        reminders: [createMockReminder()],
        pagination: { currentPage: 3, lastPage: 3, total: 45 },
      })

      const nextButton = wrapper.findAll('button').find((b) => b.text() === 'Next')
      expect(nextButton?.attributes('disabled')).toBeDefined()
    })

    it('emits page-change event when clicking next', async () => {
      const wrapper = mountComponent({
        reminders: [createMockReminder()],
        pagination: { currentPage: 1, lastPage: 3, total: 45 },
      })

      const nextButton = wrapper.findAll('button').find((b) => b.text() === 'Next')
      await nextButton?.trigger('click')

      expect(wrapper.emitted('page-change')).toBeTruthy()
      expect(wrapper.emitted('page-change')![0]).toEqual([2])
    })

    it('emits page-change event when clicking previous', async () => {
      const wrapper = mountComponent({
        reminders: [createMockReminder()],
        pagination: { currentPage: 2, lastPage: 3, total: 45 },
      })

      const prevButton = wrapper.findAll('button').find((b) => b.text() === 'Previous')
      await prevButton?.trigger('click')

      expect(wrapper.emitted('page-change')).toBeTruthy()
      expect(wrapper.emitted('page-change')![0]).toEqual([1])
    })
  })
})

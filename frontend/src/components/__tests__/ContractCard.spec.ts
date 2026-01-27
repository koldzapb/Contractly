import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import ContractCard from '../ContractCard.vue'
import type { Contract } from '@/types'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: { template: '<div>Home</div>' } },
    { path: '/contracts/:id', component: { template: '<div>Contract</div>' } },
  ],
})

const mockContract: Contract = {
  id: 'test-uuid-123',
  title: 'Employment Agreement',
  original_filename: 'employment-agreement.pdf',
  file_size: 1024000,
  file_size_human: '1 MB',
  page_count: 10,
  status: 'pending',
  overall_risk_level: null,
  language_detected: null,
  analyzed_at: null,
  created_at: '2024-01-15T10:30:00Z',
  updated_at: '2024-01-15T10:30:00Z',
}

describe('ContractCard', () => {
  beforeEach(async () => {
    router.push('/')
    await router.isReady()
  })

  const mountComponent = (contract: Contract = mockContract) => {
    return mount(ContractCard, {
      props: { contract },
      global: {
        plugins: [router],
      },
    })
  }

  describe('basic rendering', () => {
    it('renders contract title', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Employment Agreement')
    })

    it('renders original filename', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('employment-agreement.pdf')
    })

    it('renders file size', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('1 MB')
    })

    it('renders formatted date', () => {
      const wrapper = mountComponent()

      // Should show "Jan 15, 2024" format
      expect(wrapper.text()).toContain('Jan')
      expect(wrapper.text()).toContain('15')
      expect(wrapper.text()).toContain('2024')
    })

    it('links to contract detail page', () => {
      const wrapper = mountComponent()

      const link = wrapper.find('a')
      expect(link.attributes('href')).toBe('/contracts/test-uuid-123')
    })
  })

  describe('status badges', () => {
    it('shows pending status badge', () => {
      const wrapper = mountComponent({
        ...mockContract,
        status: 'pending',
      })

      expect(wrapper.text()).toContain('Pending')
    })

    it('shows processing status badge with spinner', () => {
      const wrapper = mountComponent({
        ...mockContract,
        status: 'processing',
      })

      expect(wrapper.text()).toContain('Processing')
      expect(wrapper.find('.animate-spin').exists()).toBe(true)
    })

    it('shows analyzed status badge for completed contracts', () => {
      const wrapper = mountComponent({
        ...mockContract,
        status: 'completed',
      })

      expect(wrapper.text()).toContain('Analyzed')
    })

    it('shows failed status badge', () => {
      const wrapper = mountComponent({
        ...mockContract,
        status: 'failed',
      })

      expect(wrapper.text()).toContain('Failed')
    })
  })

  describe('risk level badges', () => {
    it('shows low risk badge', () => {
      const wrapper = mountComponent({
        ...mockContract,
        status: 'completed',
        overall_risk_level: 'low',
      })

      expect(wrapper.text()).toContain('Low Risk')
    })

    it('shows medium risk badge', () => {
      const wrapper = mountComponent({
        ...mockContract,
        status: 'completed',
        overall_risk_level: 'medium',
      })

      expect(wrapper.text()).toContain('Medium Risk')
    })

    it('shows high risk badge', () => {
      const wrapper = mountComponent({
        ...mockContract,
        status: 'completed',
        overall_risk_level: 'high',
      })

      expect(wrapper.text()).toContain('High Risk')
    })

    it('does not show risk badge when null', () => {
      const wrapper = mountComponent({
        ...mockContract,
        overall_risk_level: null,
      })

      expect(wrapper.text()).not.toContain('Risk')
    })
  })

  describe('error message', () => {
    it('shows error message for failed contracts', () => {
      const wrapper = mountComponent({
        ...mockContract,
        status: 'failed',
        error_message: 'PDF parsing failed',
      })

      expect(wrapper.text()).toContain('PDF parsing failed')
    })

    it('does not show error message for non-failed contracts', () => {
      const wrapper = mountComponent({
        ...mockContract,
        status: 'completed',
        error_message: 'Some old error',
      })

      expect(wrapper.text()).not.toContain('Some old error')
    })
  })

  describe('delete button', () => {
    it('renders delete button', () => {
      const wrapper = mountComponent()

      const deleteButton = wrapper.find('button[title="Delete contract"]')
      expect(deleteButton.exists()).toBe(true)
    })

    it('emits delete event when clicked', async () => {
      const wrapper = mountComponent()

      const deleteButton = wrapper.find('button[title="Delete contract"]')
      await deleteButton.trigger('click')

      expect(wrapper.emitted('delete')).toBeTruthy()
      expect(wrapper.emitted('delete')![0]).toEqual(['test-uuid-123'])
    })
  })
})

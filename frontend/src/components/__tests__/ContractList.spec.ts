import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import ContractList from '../ContractList.vue'
import ContractCard from '../ContractCard.vue'
import type { Contract } from '@/types'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: { template: '<div>Home</div>' } },
    { path: '/contracts/:id', component: { template: '<div>Contract</div>' } },
  ],
})

const mockContracts: Contract[] = [
  {
    id: 'uuid-1',
    title: 'Contract 1',
    original_filename: 'contract1.pdf',
    file_size: 1024,
    file_size_human: '1 KB',
    page_count: 5,
    status: 'completed',
    overall_risk_level: 'low',
    language_detected: 'en',
    analyzed_at: '2024-01-15T10:30:00Z',
    created_at: '2024-01-15T10:30:00Z',
    updated_at: '2024-01-15T10:30:00Z',
  },
  {
    id: 'uuid-2',
    title: 'Contract 2',
    original_filename: 'contract2.pdf',
    file_size: 2048,
    file_size_human: '2 KB',
    page_count: 10,
    status: 'pending',
    overall_risk_level: null,
    language_detected: null,
    analyzed_at: null,
    created_at: '2024-01-14T10:30:00Z',
    updated_at: '2024-01-14T10:30:00Z',
  },
]

describe('ContractList', () => {
  beforeEach(async () => {
    router.push('/')
    await router.isReady()
  })

  const mountComponent = (props: {
    contracts: Contract[]
    loading?: boolean
    pagination?: { currentPage: number; lastPage: number; total: number }
  }) => {
    return mount(ContractList, {
      props,
      global: {
        plugins: [router],
        stubs: {
          ContractCard: false,
        },
      },
    })
  }

  describe('header', () => {
    it('renders title', () => {
      const wrapper = mountComponent({ contracts: [] })

      expect(wrapper.text()).toContain('Your Contracts')
    })
  })

  describe('loading state', () => {
    it('shows loading spinner when loading', () => {
      const wrapper = mountComponent({
        contracts: [],
        loading: true,
      })

      expect(wrapper.find('.animate-spin').exists()).toBe(true)
      expect(wrapper.text()).toContain('Loading contracts...')
    })

    it('does not show contracts when loading', () => {
      const wrapper = mountComponent({
        contracts: mockContracts,
        loading: true,
      })

      expect(wrapper.findAllComponents(ContractCard).length).toBe(0)
    })
  })

  describe('empty state', () => {
    it('shows empty message when no contracts', () => {
      const wrapper = mountComponent({
        contracts: [],
        loading: false,
      })

      expect(wrapper.text()).toContain('No contracts uploaded yet')
      expect(wrapper.text()).toContain('Upload your first contract to get started')
    })

    it('shows empty icon', () => {
      const wrapper = mountComponent({
        contracts: [],
        loading: false,
      })

      expect(wrapper.find('svg').exists()).toBe(true)
    })
  })

  describe('contract list', () => {
    it('renders contract cards', () => {
      const wrapper = mountComponent({
        contracts: mockContracts,
        loading: false,
      })

      const cards = wrapper.findAllComponents(ContractCard)
      expect(cards.length).toBe(2)
    })

    it('passes contract prop to each card', () => {
      const wrapper = mountComponent({
        contracts: mockContracts,
        loading: false,
      })

      const cards = wrapper.findAllComponents(ContractCard)
      expect(cards[0].props('contract')).toEqual(mockContracts[0])
      expect(cards[1].props('contract')).toEqual(mockContracts[1])
    })

    it('emits delete event from card', async () => {
      const wrapper = mountComponent({
        contracts: mockContracts,
        loading: false,
      })

      const firstCard = wrapper.findComponent(ContractCard)
      await firstCard.vm.$emit('delete', 'uuid-1')

      expect(wrapper.emitted('delete')).toBeTruthy()
      expect(wrapper.emitted('delete')![0]).toEqual(['uuid-1'])
    })
  })

  describe('pagination', () => {
    it('does not show pagination when only one page', () => {
      const wrapper = mountComponent({
        contracts: mockContracts,
        loading: false,
        pagination: { currentPage: 1, lastPage: 1, total: 2 },
      })

      expect(wrapper.text()).not.toContain('Previous')
      expect(wrapper.text()).not.toContain('Next')
    })

    it('shows pagination when multiple pages', () => {
      const wrapper = mountComponent({
        contracts: mockContracts,
        loading: false,
        pagination: { currentPage: 1, lastPage: 3, total: 30 },
      })

      expect(wrapper.text()).toContain('Page 1 of 3')
      expect(wrapper.text()).toContain('30 total')
    })

    it('shows previous and next buttons', () => {
      const wrapper = mountComponent({
        contracts: mockContracts,
        loading: false,
        pagination: { currentPage: 2, lastPage: 3, total: 30 },
      })

      expect(wrapper.text()).toContain('Previous')
      expect(wrapper.text()).toContain('Next')
    })

    it('disables previous button on first page', () => {
      const wrapper = mountComponent({
        contracts: mockContracts,
        loading: false,
        pagination: { currentPage: 1, lastPage: 3, total: 30 },
      })

      const buttons = wrapper.findAll('button')
      const prevButton = buttons.find((b) => b.text() === 'Previous')
      expect(prevButton?.attributes('disabled')).toBeDefined()
    })

    it('disables next button on last page', () => {
      const wrapper = mountComponent({
        contracts: mockContracts,
        loading: false,
        pagination: { currentPage: 3, lastPage: 3, total: 30 },
      })

      const buttons = wrapper.findAll('button')
      const nextButton = buttons.find((b) => b.text() === 'Next')
      expect(nextButton?.attributes('disabled')).toBeDefined()
    })

    it('emits page-change event for previous', async () => {
      const wrapper = mountComponent({
        contracts: mockContracts,
        loading: false,
        pagination: { currentPage: 2, lastPage: 3, total: 30 },
      })

      const buttons = wrapper.findAll('button')
      const prevButton = buttons.find((b) => b.text() === 'Previous')
      await prevButton?.trigger('click')

      expect(wrapper.emitted('page-change')).toBeTruthy()
      expect(wrapper.emitted('page-change')![0]).toEqual([1])
    })

    it('emits page-change event for next', async () => {
      const wrapper = mountComponent({
        contracts: mockContracts,
        loading: false,
        pagination: { currentPage: 2, lastPage: 3, total: 30 },
      })

      const buttons = wrapper.findAll('button')
      const nextButton = buttons.find((b) => b.text() === 'Next')
      await nextButton?.trigger('click')

      expect(wrapper.emitted('page-change')).toBeTruthy()
      expect(wrapper.emitted('page-change')![0]).toEqual([3])
    })
  })
})

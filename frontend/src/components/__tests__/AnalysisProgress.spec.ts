import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import AnalysisProgress from '../AnalysisProgress.vue'

describe('AnalysisProgress', () => {
  describe('pending status', () => {
    it('shows waiting message', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'pending' },
      })

      expect(wrapper.text()).toContain('Waiting to start analysis...')
      expect(wrapper.text()).toContain('queued for analysis')
    })

    it('shows spinner', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'pending' },
      })

      expect(wrapper.find('.animate-spin').exists()).toBe(true)
    })

    it('does not show retry button', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'pending' },
      })

      expect(wrapper.find('button').exists()).toBe(false)
    })
  })

  describe('processing status', () => {
    it('shows analyzing message', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'processing' },
      })

      expect(wrapper.text()).toContain('Analyzing contract with AI...')
      expect(wrapper.text()).toContain('extracting clauses')
    })

    it('shows spinner', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'processing' },
      })

      expect(wrapper.find('.animate-spin').exists()).toBe(true)
    })

    it('shows progress steps', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'processing' },
      })

      expect(wrapper.text()).toContain('PDF Parsed')
      expect(wrapper.text()).toContain('AI Analysis')
      expect(wrapper.text()).toContain('Results')
    })
  })

  describe('failed status', () => {
    it('shows failure message', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'failed' },
      })

      expect(wrapper.text()).toContain('Analysis failed')
    })

    it('shows error icon instead of spinner', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'failed' },
      })

      expect(wrapper.find('.animate-spin').exists()).toBe(false)
      expect(wrapper.find('.bg-red-100').exists()).toBe(true)
    })

    it('shows default error message when none provided', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'failed' },
      })

      expect(wrapper.text()).toContain('An error occurred during analysis')
    })

    it('shows custom error message when provided', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'failed', errorMessage: 'PDF is corrupted' },
      })

      expect(wrapper.text()).toContain('PDF is corrupted')
    })

    it('shows retry button', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'failed' },
      })

      const button = wrapper.find('button')
      expect(button.exists()).toBe(true)
      expect(button.text()).toContain('Retry Analysis')
    })

    it('emits retry event when button clicked', async () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'failed' },
      })

      const button = wrapper.find('button')
      await button.trigger('click')

      expect(wrapper.emitted('retry')).toBeTruthy()
      expect(wrapper.emitted('retry')!.length).toBe(1)
    })
  })

  describe('completed status', () => {
    it('renders nothing for completed status', () => {
      const wrapper = mount(AnalysisProgress, {
        props: { status: 'completed' },
      })

      expect(wrapper.find('.animate-spin').exists()).toBe(false)
      expect(wrapper.find('button').exists()).toBe(false)
    })
  })
})

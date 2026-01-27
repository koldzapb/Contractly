import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import UploadProgress from '../UploadProgress.vue'

describe('UploadProgress', () => {
  const mountComponent = (props: { progress: number; filename: string }) => {
    return mount(UploadProgress, {
      props,
    })
  }

  describe('progress display', () => {
    it('renders filename', () => {
      const wrapper = mountComponent({
        progress: 50,
        filename: 'test-contract.pdf',
      })

      expect(wrapper.text()).toContain('test-contract.pdf')
    })

    it('renders progress percentage', () => {
      const wrapper = mountComponent({
        progress: 75,
        filename: 'test.pdf',
      })

      expect(wrapper.text()).toContain('75%')
    })

    it('sets progress bar width correctly', () => {
      const wrapper = mountComponent({
        progress: 60,
        filename: 'test.pdf',
      })

      const progressBar = wrapper.find('.bg-indigo-600')
      expect(progressBar.attributes('style')).toContain('width: 60%')
    })
  })

  describe('uploading state', () => {
    it('shows uploading text when progress < 100', () => {
      const wrapper = mountComponent({
        progress: 50,
        filename: 'test.pdf',
      })

      expect(wrapper.text()).toContain('Uploading...')
    })

    it('shows spinner when progress < 100', () => {
      const wrapper = mountComponent({
        progress: 50,
        filename: 'test.pdf',
      })

      expect(wrapper.find('.animate-spin').exists()).toBe(true)
    })
  })

  describe('complete state', () => {
    it('shows complete text when progress is 100', () => {
      const wrapper = mountComponent({
        progress: 100,
        filename: 'test.pdf',
      })

      expect(wrapper.text()).toContain('Upload complete')
    })

    it('shows checkmark icon when complete', () => {
      const wrapper = mountComponent({
        progress: 100,
        filename: 'test.pdf',
      })

      expect(wrapper.find('.bg-green-100').exists()).toBe(true)
      expect(wrapper.find('.animate-spin').exists()).toBe(false)
    })

    it('sets progress bar to 100%', () => {
      const wrapper = mountComponent({
        progress: 100,
        filename: 'test.pdf',
      })

      const progressBar = wrapper.find('.bg-indigo-600')
      expect(progressBar.attributes('style')).toContain('width: 100%')
    })
  })

  describe('edge cases', () => {
    it('handles 0 progress', () => {
      const wrapper = mountComponent({
        progress: 0,
        filename: 'test.pdf',
      })

      expect(wrapper.text()).toContain('0%')
      expect(wrapper.text()).toContain('Uploading...')
    })

    it('truncates long filenames', () => {
      const wrapper = mountComponent({
        progress: 50,
        filename: 'very-long-filename-that-should-be-truncated-in-the-ui.pdf',
      })

      const filenameElement = wrapper.find('.truncate')
      expect(filenameElement.exists()).toBe(true)
    })
  })
})

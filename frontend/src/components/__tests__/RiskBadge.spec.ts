import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import RiskBadge from '../RiskBadge.vue'

describe('RiskBadge', () => {
  describe('risk levels', () => {
    it('renders low risk badge correctly', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: 'low' },
      })

      expect(wrapper.text()).toContain('Low Risk')
      expect(wrapper.find('.bg-green-100').exists()).toBe(true)
    })

    it('renders medium risk badge correctly', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: 'medium' },
      })

      expect(wrapper.text()).toContain('Medium Risk')
      expect(wrapper.find('.bg-yellow-100').exists()).toBe(true)
    })

    it('renders high risk badge correctly', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: 'high' },
      })

      expect(wrapper.text()).toContain('High Risk')
      expect(wrapper.find('.bg-red-100').exists()).toBe(true)
    })

    it('renders nothing when level is null', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: null },
      })

      expect(wrapper.text()).toBe('')
    })
  })

  describe('sizes', () => {
    it('renders small size', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: 'low', size: 'sm' },
      })

      expect(wrapper.find('.text-xs').exists()).toBe(true)
    })

    it('renders medium size by default', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: 'low' },
      })

      expect(wrapper.find('.text-sm').exists()).toBe(true)
    })

    it('renders large size', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: 'low', size: 'lg' },
      })

      expect(wrapper.find('.text-base').exists()).toBe(true)
    })
  })

  describe('showLabel prop', () => {
    it('shows full label by default', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: 'high' },
      })

      expect(wrapper.text()).toContain('High Risk')
    })

    it('shows short label when showLabel is false', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: 'high', showLabel: false },
      })

      expect(wrapper.text()).toContain('High')
      expect(wrapper.text()).not.toContain('High Risk')
    })
  })

  describe('dot indicator', () => {
    it('shows green dot for low risk', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: 'low' },
      })

      expect(wrapper.find('.bg-green-500').exists()).toBe(true)
    })

    it('shows yellow dot for medium risk', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: 'medium' },
      })

      expect(wrapper.find('.bg-yellow-500').exists()).toBe(true)
    })

    it('shows red dot for high risk', () => {
      const wrapper = mount(RiskBadge, {
        props: { level: 'high' },
      })

      expect(wrapper.find('.bg-red-500').exists()).toBe(true)
    })
  })
})

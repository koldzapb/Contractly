import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import AnalysisSummary from '../AnalysisSummary.vue'
import type { ContractAnalysis } from '@/types'

const mockAnalysis: ContractAnalysis = {
  id: 'analysis-123',
  summary: 'This is a standard employment contract with moderate risk provisions.',
  overall_risk_level: 'medium',
  key_findings: [
    'Non-compete clause extends to 2 years',
    'Intellectual property rights transferred to employer',
    'Limited severance package',
  ],
  ai_model: 'claude-3-sonnet',
  tokens_used: 15000,
  processing_time_ms: 3500,
  created_at: '2024-01-15T10:30:00Z',
}

describe('AnalysisSummary', () => {
  const mountComponent = (analysis: ContractAnalysis = mockAnalysis) => {
    return mount(AnalysisSummary, {
      props: { analysis },
      global: {
        stubs: {
          RiskBadge: {
            template: '<span class="risk-badge">{{ level }} Risk</span>',
            props: ['level', 'size'],
          },
        },
      },
    })
  }

  describe('basic rendering', () => {
    it('renders the summary title', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Analysis Summary')
    })

    it('renders the analysis summary text', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('standard employment contract')
    })

    it('renders the risk badge', () => {
      const wrapper = mountComponent()

      expect(wrapper.find('.risk-badge').text()).toContain('medium Risk')
    })
  })

  describe('key findings', () => {
    it('renders key findings header', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Key Findings')
    })

    it('renders all key findings', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Non-compete clause extends to 2 years')
      expect(wrapper.text()).toContain('Intellectual property rights transferred')
      expect(wrapper.text()).toContain('Limited severance package')
    })

    it('does not render key findings section when empty', () => {
      const wrapper = mountComponent({
        ...mockAnalysis,
        key_findings: [],
      })

      expect(wrapper.text()).not.toContain('Key Findings')
    })
  })

  describe('metadata', () => {
    it('renders AI model', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('claude-3-sonnet')
    })

    it('renders tokens used', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('15,000 tokens')
    })

    it('renders processing time', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('3.5s')
    })

    it('does not render tokens if zero', () => {
      const wrapper = mountComponent({
        ...mockAnalysis,
        tokens_used: 0,
      })

      expect(wrapper.text()).not.toContain('0 tokens')
    })

    it('does not render processing time if zero', () => {
      const wrapper = mountComponent({
        ...mockAnalysis,
        processing_time_ms: 0,
      })

      expect(wrapper.text()).not.toContain('0.0s')
    })
  })

  describe('risk levels', () => {
    it('passes low risk level to badge', () => {
      const wrapper = mountComponent({
        ...mockAnalysis,
        overall_risk_level: 'low',
      })

      expect(wrapper.find('.risk-badge').text()).toContain('low Risk')
    })

    it('passes high risk level to badge', () => {
      const wrapper = mountComponent({
        ...mockAnalysis,
        overall_risk_level: 'high',
      })

      expect(wrapper.find('.risk-badge').text()).toContain('high Risk')
    })
  })
})

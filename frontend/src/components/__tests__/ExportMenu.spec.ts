import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import ExportMenu from '../ExportMenu.vue'
import * as exports from '@/services/exports'

// Mock the export service
vi.mock('@/services/exports', () => ({
  exportPdf: vi.fn(),
  exportClausesCsv: vi.fn(),
  exportDeadlinesCsv: vi.fn(),
  exportAllZip: vi.fn(),
}))

describe('ExportMenu', () => {
  const defaultProps = {
    contractId: 'test-contract-123',
    contractTitle: 'Test Contract',
  }

  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('rendering', () => {
    it('renders export button', () => {
      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      expect(wrapper.text()).toContain('Export')
    })

    it('shows dropdown menu when clicked', async () => {
      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')

      expect(wrapper.text()).toContain('PDF Report')
      expect(wrapper.text()).toContain('Clauses CSV')
      expect(wrapper.text()).toContain('Deadlines CSV')
      expect(wrapper.text()).toContain('Export All (ZIP)')
    })

    it('shows descriptions for each export option', async () => {
      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')

      expect(wrapper.text()).toContain('Full analysis report with formatting')
      expect(wrapper.text()).toContain('All extracted clauses in spreadsheet format')
      expect(wrapper.text()).toContain('All deadlines for calendar import')
      expect(wrapper.text()).toContain('Summary + all CSVs in one download')
    })

    it('closes menu when clicking outside', async () => {
      const wrapper = mount(ExportMenu, {
        props: defaultProps,
        attachTo: document.body,
      })

      // Open menu
      await wrapper.find('button').trigger('click')
      expect(wrapper.text()).toContain('PDF Report')

      // Simulate click outside
      document.dispatchEvent(new MouseEvent('click'))
      await wrapper.vm.$nextTick()

      // Menu should be closed (no PDF Report text visible in dropdown)
      const menuItems = wrapper.findAll('[role="menuitem"]')
      expect(menuItems.length).toBe(0)

      wrapper.unmount()
    })
  })

  describe('disabled state', () => {
    it('disables button when disabled prop is true', () => {
      const wrapper = mount(ExportMenu, {
        props: { ...defaultProps, disabled: true },
      })

      const button = wrapper.find('button')
      expect(button.attributes('disabled')).toBeDefined()
    })

    it('does not open menu when disabled', async () => {
      const wrapper = mount(ExportMenu, {
        props: { ...defaultProps, disabled: true },
      })

      await wrapper.find('button').trigger('click')

      // Menu should not be visible
      expect(wrapper.findAll('[role="menuitem"]').length).toBe(0)
    })
  })

  describe('export actions', () => {
    it('calls exportPdf when PDF Report is clicked', async () => {
      vi.mocked(exports.exportPdf).mockResolvedValue()

      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')
      const menuItems = wrapper.findAll('[role="menuitem"]')
      await menuItems[0].trigger('click')

      expect(exports.exportPdf).toHaveBeenCalledWith('test-contract-123', 'Test Contract')
    })

    it('calls exportClausesCsv when Clauses CSV is clicked', async () => {
      vi.mocked(exports.exportClausesCsv).mockResolvedValue()

      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')
      const menuItems = wrapper.findAll('[role="menuitem"]')
      await menuItems[1].trigger('click')

      expect(exports.exportClausesCsv).toHaveBeenCalledWith('test-contract-123', 'Test Contract')
    })

    it('calls exportDeadlinesCsv when Deadlines CSV is clicked', async () => {
      vi.mocked(exports.exportDeadlinesCsv).mockResolvedValue()

      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')
      const menuItems = wrapper.findAll('[role="menuitem"]')
      await menuItems[2].trigger('click')

      expect(exports.exportDeadlinesCsv).toHaveBeenCalledWith('test-contract-123', 'Test Contract')
    })

    it('calls exportAllZip when Export All is clicked', async () => {
      vi.mocked(exports.exportAllZip).mockResolvedValue()

      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')
      const menuItems = wrapper.findAll('[role="menuitem"]')
      await menuItems[3].trigger('click')

      expect(exports.exportAllZip).toHaveBeenCalledWith('test-contract-123', 'Test Contract')
    })

    it('emits success event after successful export', async () => {
      vi.mocked(exports.exportPdf).mockResolvedValue()

      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')
      const menuItems = wrapper.findAll('[role="menuitem"]')
      await menuItems[0].trigger('click')

      // Wait for async export to complete
      await vi.waitFor(() => {
        expect(wrapper.emitted('success')).toBeTruthy()
      })

      expect(wrapper.emitted('success')?.[0]).toEqual(['pdf'])
    })

    it('emits error event when export fails', async () => {
      const errorMessage = 'Network error'
      vi.mocked(exports.exportPdf).mockRejectedValue(new Error(errorMessage))

      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')
      const menuItems = wrapper.findAll('[role="menuitem"]')
      await menuItems[0].trigger('click')

      // Wait for async export to fail
      await vi.waitFor(() => {
        expect(wrapper.emitted('error')).toBeTruthy()
      })

      expect(wrapper.emitted('error')?.[0]).toEqual([errorMessage])
    })
  })

  describe('loading state', () => {
    it('shows exporting state during export', async () => {
      // Create a promise that we can control
      let resolveExport: () => void
      const exportPromise = new Promise<void>((resolve) => {
        resolveExport = resolve
      })
      vi.mocked(exports.exportPdf).mockReturnValue(exportPromise)

      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')
      const menuItems = wrapper.findAll('[role="menuitem"]')
      await menuItems[0].trigger('click')

      // Should show exporting state
      expect(wrapper.text()).toContain('Exporting...')

      // Resolve the export
      resolveExport!()
      await vi.waitFor(() => {
        expect(wrapper.text()).toContain('Export')
        expect(wrapper.text()).not.toContain('Exporting...')
      })
    })

    it('disables button during export', async () => {
      let resolveExport: () => void
      const exportPromise = new Promise<void>((resolve) => {
        resolveExport = resolve
      })
      vi.mocked(exports.exportPdf).mockReturnValue(exportPromise)

      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')
      const menuItems = wrapper.findAll('[role="menuitem"]')
      await menuItems[0].trigger('click')

      // Button should be disabled
      const button = wrapper.find('button')
      expect(button.classes()).toContain('cursor-not-allowed')

      // Cleanup
      resolveExport!()
      await wrapper.vm.$nextTick()
    })
  })

  describe('accessibility', () => {
    it('has aria-haspopup attribute on trigger button', () => {
      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      const button = wrapper.find('button')
      expect(button.attributes('aria-haspopup')).toBe('true')
    })

    it('has aria-expanded attribute that updates', async () => {
      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      const button = wrapper.find('button')
      expect(button.attributes('aria-expanded')).toBe('false')

      await button.trigger('click')
      expect(button.attributes('aria-expanded')).toBe('true')
    })

    it('has role="menu" on dropdown', async () => {
      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')

      const menu = wrapper.find('[role="menu"]')
      expect(menu.exists()).toBe(true)
    })

    it('has role="menuitem" on each option', async () => {
      const wrapper = mount(ExportMenu, {
        props: defaultProps,
      })

      await wrapper.find('button').trigger('click')

      const menuItems = wrapper.findAll('[role="menuitem"]')
      expect(menuItems.length).toBe(4)
    })
  })
})

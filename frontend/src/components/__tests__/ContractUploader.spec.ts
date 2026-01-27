import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import ContractUploader from '../ContractUploader.vue'
import UploadProgress from '../UploadProgress.vue'
import { useContractsStore } from '@/stores/contracts'
import * as contractsService from '@/services/contracts'

vi.mock('@/services/contracts')

describe('ContractUploader', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  const mountComponent = () => {
    return mount(ContractUploader, {
      global: {
        stubs: {
          UploadProgress: true,
        },
      },
    })
  }

  describe('default state', () => {
    it('renders upload area', () => {
      const wrapper = mountComponent()

      expect(wrapper.text()).toContain('Upload a file')
      expect(wrapper.text()).toContain('or drag and drop')
      expect(wrapper.text()).toContain('PDF files up to 10MB')
    })

    it('has file input accepting PDF files', () => {
      const wrapper = mountComponent()

      const input = wrapper.find('input[type="file"]')
      expect(input.exists()).toBe(true)
      expect(input.attributes('accept')).toContain('pdf')
    })

    it('does not show upload progress initially', () => {
      const wrapper = mountComponent()

      expect(wrapper.findComponent(UploadProgress).exists()).toBe(false)
    })
  })

  describe('drag and drop', () => {
    it('adds highlight class on dragover', async () => {
      const wrapper = mountComponent()

      await wrapper.find('.card').trigger('dragover')

      expect(wrapper.find('.card').classes()).toContain('border-indigo-500')
    })

    it('removes highlight class on dragleave', async () => {
      const wrapper = mountComponent()

      await wrapper.find('.card').trigger('dragover')
      await wrapper.find('.card').trigger('dragleave')

      expect(wrapper.find('.card').classes()).not.toContain('border-indigo-500')
    })

    it('handles file drop', async () => {
      vi.mocked(contractsService.validateFile).mockReturnValue({ valid: true })
      vi.mocked(contractsService.uploadContract).mockResolvedValue({
        id: 'test-uuid',
        title: 'Test',
        original_filename: 'test.pdf',
        file_size: 1024,
        file_size_human: '1 KB',
        page_count: null,
        status: 'pending',
        overall_risk_level: null,
        language_detected: null,
        analyzed_at: null,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
      })

      const wrapper = mountComponent()
      const file = new File(['test'], 'test.pdf', { type: 'application/pdf' })

      // Create a mock FileList-like object
      const mockFileList = {
        0: file,
        length: 1,
        item: (index: number) => (index === 0 ? file : null),
      }

      await wrapper.find('.card').trigger('drop', {
        dataTransfer: {
          files: mockFileList,
        },
      })

      expect(contractsService.uploadContract).toHaveBeenCalled()
    })
  })

  describe('file selection', () => {
    it('handles file selection via input', async () => {
      vi.mocked(contractsService.validateFile).mockReturnValue({ valid: true })
      vi.mocked(contractsService.uploadContract).mockResolvedValue({
        id: 'test-uuid',
        title: 'Test',
        original_filename: 'test.pdf',
        file_size: 1024,
        file_size_human: '1 KB',
        page_count: null,
        status: 'pending',
        overall_risk_level: null,
        language_detected: null,
        analyzed_at: null,
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
      })

      const wrapper = mountComponent()
      const input = wrapper.find('input[type="file"]')
      const file = new File(['test'], 'test.pdf', { type: 'application/pdf' })

      Object.defineProperty(input.element, 'files', {
        value: [file],
        writable: false,
      })

      await input.trigger('change')

      expect(contractsService.uploadContract).toHaveBeenCalled()
    })
  })

  describe('uploading state', () => {
    it('shows upload progress when uploading', async () => {
      const wrapper = mount(ContractUploader, {
        global: {
          stubs: {
            UploadProgress: false,
          },
        },
      })

      const store = useContractsStore()
      store.upload.uploading = true
      store.upload.file = new File(['test'], 'test.pdf')
      store.upload.progress = 50

      await wrapper.vm.$nextTick()

      expect(wrapper.findComponent(UploadProgress).exists()).toBe(true)
    })
  })

  describe('error state', () => {
    it('shows error message on upload failure', async () => {
      const wrapper = mountComponent()
      const store = useContractsStore()

      store.upload.error = 'Upload failed: File too large'
      await wrapper.vm.$nextTick()

      expect(wrapper.text()).toContain('Upload Failed')
      expect(wrapper.text()).toContain('Upload failed: File too large')
    })

    it('shows retry button on error', async () => {
      const wrapper = mountComponent()
      const store = useContractsStore()

      store.upload.error = 'Upload failed'
      await wrapper.vm.$nextTick()

      const retryButton = wrapper.find('button')
      expect(retryButton.exists()).toBe(true)
      expect(retryButton.text()).toContain('Try Again')
    })

    it('resets upload state on retry click', async () => {
      const wrapper = mountComponent()
      const store = useContractsStore()

      store.upload.error = 'Upload failed'
      await wrapper.vm.$nextTick()

      await wrapper.find('button').trigger('click')

      expect(store.upload.error).toBeNull()
    })

    it('adds error border class on error', async () => {
      const wrapper = mountComponent()
      const store = useContractsStore()

      store.upload.error = 'Upload failed'
      await wrapper.vm.$nextTick()

      expect(wrapper.find('.card').classes()).toContain('border-red-500')
    })
  })
})

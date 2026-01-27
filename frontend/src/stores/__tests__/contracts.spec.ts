import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useContractsStore } from '../contracts'
import * as contractsService from '@/services/contracts'
import type { Contract, PaginatedResponse } from '@/types'

vi.mock('@/services/contracts')

const mockContract: Contract = {
  id: 'test-uuid-1',
  title: 'Test Contract',
  original_filename: 'test.pdf',
  file_size: 1024,
  file_size_human: '1 KB',
  page_count: 5,
  status: 'pending',
  overall_risk_level: null,
  language_detected: null,
  analyzed_at: null,
  created_at: '2024-01-01T00:00:00Z',
  updated_at: '2024-01-01T00:00:00Z',
}

const mockPaginatedResponse: PaginatedResponse<Contract> = {
  data: [mockContract],
  meta: {
    current_page: 1,
    from: 1,
    last_page: 1,
    per_page: 15,
    to: 1,
    total: 1,
  },
  links: {
    first: '/api/contracts?page=1',
    last: '/api/contracts?page=1',
    prev: null,
    next: null,
  },
}

describe('contracts store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('initial state', () => {
    it('has empty contracts array', () => {
      const store = useContractsStore()
      expect(store.contracts).toEqual([])
    })

    it('has loading set to false', () => {
      const store = useContractsStore()
      expect(store.loading).toBe(false)
    })

    it('has error set to null', () => {
      const store = useContractsStore()
      expect(store.error).toBeNull()
    })

    it('has upload state initialized', () => {
      const store = useContractsStore()
      expect(store.upload).toEqual({
        file: null,
        progress: 0,
        uploading: false,
        error: null,
      })
    })
  })

  describe('fetchContracts', () => {
    it('fetches contracts successfully', async () => {
      vi.mocked(contractsService.getContracts).mockResolvedValue(mockPaginatedResponse)

      const store = useContractsStore()
      await store.fetchContracts()

      expect(contractsService.getContracts).toHaveBeenCalled()
      expect(store.contracts).toEqual([mockContract])
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })

    it('updates pagination state', async () => {
      vi.mocked(contractsService.getContracts).mockResolvedValue(mockPaginatedResponse)

      const store = useContractsStore()
      await store.fetchContracts()

      expect(store.pagination).toEqual({
        currentPage: 1,
        lastPage: 1,
        perPage: 15,
        total: 1,
      })
    })

    it('sets error on failure', async () => {
      const error = { response: { data: { message: 'Failed to fetch' } } }
      vi.mocked(contractsService.getContracts).mockRejectedValue(error)

      const store = useContractsStore()

      await expect(store.fetchContracts()).rejects.toThrow()
      expect(store.error).toBe('Failed to fetch')
      expect(store.loading).toBe(false)
    })
  })

  describe('fetchContract', () => {
    it('fetches a single contract successfully', async () => {
      vi.mocked(contractsService.getContract).mockResolvedValue(mockContract)

      const store = useContractsStore()
      const result = await store.fetchContract('test-uuid-1')

      expect(contractsService.getContract).toHaveBeenCalledWith('test-uuid-1', false)
      expect(result).toEqual(mockContract)
      expect(store.currentContract).toEqual(mockContract)
    })

    it('fetches contract with analysis when requested', async () => {
      vi.mocked(contractsService.getContract).mockResolvedValue(mockContract)

      const store = useContractsStore()
      await store.fetchContract('test-uuid-1', true)

      expect(contractsService.getContract).toHaveBeenCalledWith('test-uuid-1', true)
    })
  })

  describe('uploadContract', () => {
    it('uploads a contract successfully', async () => {
      vi.mocked(contractsService.validateFile).mockReturnValue({ valid: true })
      vi.mocked(contractsService.uploadContract).mockResolvedValue(mockContract)

      const store = useContractsStore()
      const file = new File(['test'], 'test.pdf', { type: 'application/pdf' })
      const result = await store.uploadContract({ file })

      expect(result).toEqual(mockContract)
      expect(store.contracts[0]).toEqual(mockContract)
      expect(store.upload.uploading).toBe(false)
      expect(store.upload.error).toBeNull()
    })

    it('validates file before upload', async () => {
      vi.mocked(contractsService.validateFile).mockReturnValue({
        valid: false,
        error: 'Invalid file type',
      })

      const store = useContractsStore()
      const file = new File(['test'], 'test.docx', { type: 'application/docx' })

      await expect(store.uploadContract({ file })).rejects.toThrow('Invalid file type')
      expect(store.upload.error).toBe('Invalid file type')
    })

    it('handles upload error', async () => {
      vi.mocked(contractsService.validateFile).mockReturnValue({ valid: true })
      const error = { response: { data: { message: 'Upload failed' } } }
      vi.mocked(contractsService.uploadContract).mockRejectedValue(error)

      const store = useContractsStore()
      const file = new File(['test'], 'test.pdf', { type: 'application/pdf' })

      await expect(store.uploadContract({ file })).rejects.toThrow()
      expect(store.upload.error).toBe('Upload failed')
      expect(store.upload.uploading).toBe(false)
    })
  })

  describe('updateContract', () => {
    it('updates a contract successfully', async () => {
      const updatedContract = { ...mockContract, title: 'Updated Title' }
      vi.mocked(contractsService.updateContract).mockResolvedValue(updatedContract)

      const store = useContractsStore()
      store.contracts = [mockContract]

      const result = await store.updateContract('test-uuid-1', 'Updated Title')

      expect(contractsService.updateContract).toHaveBeenCalledWith('test-uuid-1', {
        title: 'Updated Title',
      })
      expect(result.title).toBe('Updated Title')
      expect(store.contracts[0].title).toBe('Updated Title')
    })

    it('updates currentContract if it matches', async () => {
      const updatedContract = { ...mockContract, title: 'Updated Title' }
      vi.mocked(contractsService.updateContract).mockResolvedValue(updatedContract)

      const store = useContractsStore()
      store.currentContract = mockContract

      await store.updateContract('test-uuid-1', 'Updated Title')

      expect(store.currentContract?.title).toBe('Updated Title')
    })
  })

  describe('deleteContract', () => {
    it('deletes a contract successfully', async () => {
      vi.mocked(contractsService.deleteContract).mockResolvedValue(undefined)

      const store = useContractsStore()
      store.contracts = [mockContract]
      store.pagination.total = 1

      await store.deleteContract('test-uuid-1')

      expect(contractsService.deleteContract).toHaveBeenCalledWith('test-uuid-1')
      expect(store.contracts).toEqual([])
      expect(store.pagination.total).toBe(0)
    })

    it('clears currentContract if it matches', async () => {
      vi.mocked(contractsService.deleteContract).mockResolvedValue(undefined)

      const store = useContractsStore()
      store.currentContract = mockContract

      await store.deleteContract('test-uuid-1')

      expect(store.currentContract).toBeNull()
    })
  })

  describe('pollContractStatus', () => {
    it('updates contract status in list', async () => {
      vi.mocked(contractsService.getContractStatus).mockResolvedValue({
        id: 'test-uuid-1',
        status: 'completed',
      })

      const store = useContractsStore()
      store.contracts = [mockContract]

      await store.pollContractStatus('test-uuid-1')

      expect(store.contracts[0].status).toBe('completed')
    })

    it('updates currentContract status if it matches', async () => {
      vi.mocked(contractsService.getContractStatus).mockResolvedValue({
        id: 'test-uuid-1',
        status: 'completed',
      })

      const store = useContractsStore()
      store.currentContract = mockContract

      await store.pollContractStatus('test-uuid-1')

      expect(store.currentContract?.status).toBe('completed')
    })
  })

  describe('computed properties', () => {
    it('hasContracts returns true when contracts exist', () => {
      const store = useContractsStore()
      store.contracts = [mockContract]
      expect(store.hasContracts).toBe(true)
    })

    it('hasContracts returns false when no contracts', () => {
      const store = useContractsStore()
      expect(store.hasContracts).toBe(false)
    })

    it('hasMorePages returns true when more pages exist', () => {
      const store = useContractsStore()
      store.pagination.currentPage = 1
      store.pagination.lastPage = 3
      expect(store.hasMorePages).toBe(true)
    })

    it('hasMorePages returns false on last page', () => {
      const store = useContractsStore()
      store.pagination.currentPage = 3
      store.pagination.lastPage = 3
      expect(store.hasMorePages).toBe(false)
    })

    it('isUploading returns upload state', () => {
      const store = useContractsStore()
      expect(store.isUploading).toBe(false)
      store.upload.uploading = true
      expect(store.isUploading).toBe(true)
    })
  })

  describe('helper methods', () => {
    it('resetUpload clears upload state', () => {
      const store = useContractsStore()
      store.upload = {
        file: new File(['test'], 'test.pdf'),
        progress: 50,
        uploading: true,
        error: 'Some error',
      }

      store.resetUpload()

      expect(store.upload).toEqual({
        file: null,
        progress: 0,
        uploading: false,
        error: null,
      })
    })

    it('clearError clears error state', () => {
      const store = useContractsStore()
      store.error = 'Some error'

      store.clearError()

      expect(store.error).toBeNull()
    })

    it('goToPage fetches contracts for specific page', async () => {
      vi.mocked(contractsService.getContracts).mockResolvedValue(mockPaginatedResponse)

      const store = useContractsStore()
      await store.goToPage(2)

      expect(contractsService.getContracts).toHaveBeenCalledWith({
        page: 2,
        per_page: 15,
      })
    })
  })
})

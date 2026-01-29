import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { Contract, PaginatedResponse, PiiDetectionResult } from '@/types'
import * as contractsService from '@/services/contracts'
import type { UploadContractData, ContractListParams } from '@/services/contracts'

export interface UploadState {
  file: File | null
  progress: number
  uploading: boolean
  error: string | null
}

export const useContractsStore = defineStore('contracts', () => {
  // State
  const contracts = ref<Contract[]>([])
  const currentContract = ref<Contract | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Pagination state
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  })

  // Upload state
  const upload = ref<UploadState>({
    file: null,
    progress: 0,
    uploading: false,
    error: null,
  })

  // Computed
  const hasContracts = computed(() => contracts.value.length > 0)
  const hasMorePages = computed(() => pagination.value.currentPage < pagination.value.lastPage)
  const isUploading = computed(() => upload.value.uploading)

  /**
   * Fetch paginated contracts
   */
  async function fetchContracts(params: ContractListParams = {}): Promise<void> {
    loading.value = true
    error.value = null

    try {
      const response: PaginatedResponse<Contract> = await contractsService.getContracts({
        page: params.page || pagination.value.currentPage,
        per_page: params.per_page || pagination.value.perPage,
      })

      contracts.value = response.data
      pagination.value = {
        currentPage: response.meta.current_page,
        lastPage: response.meta.last_page,
        perPage: response.meta.per_page,
        total: response.meta.total,
      }
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to load contracts'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch a single contract
   */
  async function fetchContract(id: string, withAnalysis = false): Promise<Contract> {
    loading.value = true
    error.value = null

    try {
      currentContract.value = await contractsService.getContract(id, withAnalysis)
      return currentContract.value
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to load contract'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Upload a new contract
   */
  async function uploadContract(data: UploadContractData): Promise<Contract> {
    upload.value = {
      file: data.file,
      progress: 0,
      uploading: true,
      error: null,
    }

    try {
      // Validate file first
      const validation = contractsService.validateFile(data.file)
      if (!validation.valid) {
        throw new Error(validation.error)
      }

      const contract = await contractsService.uploadContract(data, (progress) => {
        upload.value.progress = progress
      })

      // Add to the beginning of the list
      contracts.value.unshift(contract)
      pagination.value.total += 1

      return contract
    } catch (e: unknown) {
      const err = e as {
        response?: { data?: { message?: string; errors?: Record<string, string[]> } }
        message?: string
      }
      const message = err.response?.data?.message ?? err.message ?? 'Failed to upload contract'
      upload.value.error = message
      throw e
    } finally {
      upload.value.uploading = false
    }
  }

  /**
   * Update a contract
   */
  async function updateContract(id: string, title: string): Promise<Contract> {
    loading.value = true
    error.value = null

    try {
      const updated = await contractsService.updateContract(id, { title })

      // Update in list
      const index = contracts.value.findIndex((c) => c.id === id)
      if (index !== -1) {
        contracts.value[index] = updated
      }

      // Update current if it matches
      if (currentContract.value?.id === id) {
        currentContract.value = updated
      }

      return updated
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to update contract'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete a contract
   */
  async function deleteContract(id: string): Promise<void> {
    loading.value = true
    error.value = null

    try {
      await contractsService.deleteContract(id)

      // Remove from list
      contracts.value = contracts.value.filter((c) => c.id !== id)
      pagination.value.total = Math.max(0, pagination.value.total - 1)

      // Clear current if it matches
      if (currentContract.value?.id === id) {
        currentContract.value = null
      }
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to delete contract'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Poll contract status
   */
  async function pollContractStatus(id: string): Promise<Contract> {
    const status = await contractsService.getContractStatus(id)

    // Update contract in list
    const index = contracts.value.findIndex((c) => c.id === id)
    if (index !== -1) {
      const updatedContract = {
        ...contracts.value[index],
        status: status.status as Contract['status'],
      }
      if (status.error_message) {
        updatedContract.error_message = status.error_message
      } else {
        delete updatedContract.error_message
      }
      contracts.value[index] = updatedContract
    }

    // Update current if it matches
    if (currentContract.value?.id === id) {
      const updatedCurrent = {
        ...currentContract.value,
        status: status.status as Contract['status'],
      }
      if (status.error_message) {
        updatedCurrent.error_message = status.error_message
      } else {
        delete updatedCurrent.error_message
      }
      currentContract.value = updatedCurrent
    }

    return contracts.value[index] ?? currentContract.value!
  }

  /**
   * Retry analysis for a failed contract
   */
  async function retryAnalysis(id: string): Promise<void> {
    loading.value = true
    error.value = null

    try {
      const result = await contractsService.retryAnalysis(id)

      // Update contract in list
      const index = contracts.value.findIndex((c) => c.id === id)
      if (index !== -1) {
        const updatedContract = {
          ...contracts.value[index],
          status: result.status as Contract['status'],
        }
        delete updatedContract.error_message
        contracts.value[index] = updatedContract
      }

      // Update current if it matches
      if (currentContract.value?.id === id) {
        const updatedCurrent = {
          ...currentContract.value,
          status: result.status as Contract['status'],
        }
        delete updatedCurrent.error_message
        currentContract.value = updatedCurrent
      }
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to retry analysis'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Analyze a document even if classified as non-legal
   */
  async function analyzeAnyway(id: string): Promise<Contract> {
    loading.value = true
    error.value = null

    try {
      const updated = await contractsService.analyzeAnyway(id)

      // Update contract in list
      const index = contracts.value.findIndex((c) => c.id === id)
      if (index !== -1) {
        contracts.value[index] = updated
      }

      // Update current if it matches
      if (currentContract.value?.id === id) {
        currentContract.value = updated
      }

      return updated
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to analyze document'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Detect PII in a contract
   */
  async function detectPii(id: string): Promise<PiiDetectionResult> {
    loading.value = true
    error.value = null

    try {
      return await contractsService.detectPii(id)
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to detect PII'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Apply redactions to selected PII items
   */
  async function applyRedactions(id: string, itemIds: string[]): Promise<Contract> {
    loading.value = true
    error.value = null

    try {
      const updated = await contractsService.applyRedactions(id, itemIds)

      // Update contract in list
      const index = contracts.value.findIndex((c) => c.id === id)
      if (index !== -1) {
        contracts.value[index] = updated
      }

      // Update current if it matches
      if (currentContract.value?.id === id) {
        currentContract.value = updated
      }

      return updated
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to apply redactions'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Skip redaction and proceed with analysis
   */
  async function skipRedaction(id: string): Promise<Contract> {
    loading.value = true
    error.value = null

    try {
      const updated = await contractsService.skipRedaction(id)

      // Update contract in list
      const index = contracts.value.findIndex((c) => c.id === id)
      if (index !== -1) {
        contracts.value[index] = updated
      }

      // Update current if it matches
      if (currentContract.value?.id === id) {
        currentContract.value = updated
      }

      return updated
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to skip redaction'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Reset upload state
   */
  function resetUpload(): void {
    upload.value = {
      file: null,
      progress: 0,
      uploading: false,
      error: null,
    }
  }

  /**
   * Clear error
   */
  function clearError(): void {
    error.value = null
  }

  /**
   * Go to specific page
   */
  async function goToPage(page: number): Promise<void> {
    await fetchContracts({ page })
  }

  return {
    // State
    contracts,
    currentContract,
    loading,
    error,
    pagination,
    upload,

    // Computed
    hasContracts,
    hasMorePages,
    isUploading,

    // Actions
    fetchContracts,
    fetchContract,
    uploadContract,
    updateContract,
    deleteContract,
    pollContractStatus,
    retryAnalysis,
    analyzeAnyway,
    detectPii,
    applyRedactions,
    skipRedaction,
    resetUpload,
    clearError,
    goToPage,
  }
})

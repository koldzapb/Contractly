import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type {
  Contract,
  PiiDetectionResult,
  ContractSearchFilters,
  ContractSearchMeta,
  ContractStatus,
  RiskLevel,
  FileType,
  SortField,
  SortOrder,
} from '@/types'
import * as contractsService from '@/services/contracts'
import type { UploadContractData } from '@/services/contracts'

export interface UploadState {
  file: File | null
  progress: number
  uploading: boolean
  error: string | null
}

const DEFAULT_FILTERS: ContractSearchFilters = {
  q: undefined,
  status: undefined,
  risk_level: undefined,
  file_type: undefined,
  date_from: undefined,
  date_to: undefined,
  has_deadlines: undefined,
  sort_by: 'created_at',
  sort_order: 'desc',
  per_page: 15,
  page: 1,
}

export const useContractsStore = defineStore('contracts', () => {
  // State
  const contracts = ref<Contract[]>([])
  const currentContract = ref<Contract | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Search and filter state
  const filters = ref<ContractSearchFilters>({ ...DEFAULT_FILTERS })
  const filterMeta = ref<ContractSearchMeta | null>(null)

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
  const hasActiveFilters = computed(() => filterMeta.value?.has_filters ?? false)
  const activeFilterCount = computed(() => {
    let count = 0
    if (filters.value.q) count++
    if (filters.value.status?.length) count++
    if (filters.value.risk_level?.length) count++
    if (filters.value.file_type?.length) count++
    if (filters.value.date_from || filters.value.date_to) count++
    if (filters.value.has_deadlines !== undefined) count++
    return count
  })

  /**
   * Fetch paginated contracts with search and filters
   */
  async function fetchContracts(searchFilters?: Partial<ContractSearchFilters>): Promise<void> {
    loading.value = true
    error.value = null

    // Merge provided filters with current state
    if (searchFilters) {
      filters.value = { ...filters.value, ...searchFilters }
    }

    try {
      const response = await contractsService.getContracts(filters.value)

      contracts.value = response.data
      filterMeta.value = response.filters
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
   * Set search query and fetch
   */
  async function search(query: string): Promise<void> {
    filters.value.q = query || undefined
    filters.value.page = 1
    await fetchContracts()
  }

  /**
   * Set filter values and fetch
   */
  async function setFilters(newFilters: Partial<ContractSearchFilters>): Promise<void> {
    filters.value = { ...filters.value, ...newFilters, page: 1 }
    await fetchContracts()
  }

  /**
   * Clear all filters and fetch
   */
  async function clearFilters(): Promise<void> {
    filters.value = { ...DEFAULT_FILTERS }
    await fetchContracts()
  }

  /**
   * Set sort and fetch
   */
  async function setSort(sortBy: SortField, sortOrder: SortOrder = 'desc'): Promise<void> {
    filters.value.sort_by = sortBy
    filters.value.sort_order = sortOrder
    filters.value.page = 1
    await fetchContracts()
  }

  /**
   * Initialize filters from URL query params
   */
  function initFiltersFromQuery(query: Record<string, string>): void {
    const newFilters: ContractSearchFilters = { ...DEFAULT_FILTERS }

    if (query.q) newFilters.q = query.q
    if (query.status) newFilters.status = query.status.split(',') as ContractStatus[]
    if (query.risk_level) newFilters.risk_level = query.risk_level.split(',') as RiskLevel[]
    if (query.file_type) newFilters.file_type = query.file_type.split(',') as FileType[]
    if (query.date_from) newFilters.date_from = query.date_from
    if (query.date_to) newFilters.date_to = query.date_to
    if (query.has_deadlines) newFilters.has_deadlines = query.has_deadlines === 'true'
    if (query.sort_by) newFilters.sort_by = query.sort_by as SortField
    if (query.sort_order) newFilters.sort_order = query.sort_order as SortOrder
    if (query.page) newFilters.page = parseInt(query.page, 10)
    if (query.per_page) newFilters.per_page = parseInt(query.per_page, 10)

    filters.value = newFilters
  }

  /**
   * Get current filters as URL query params
   */
  function getFiltersAsQuery(): Record<string, string> {
    const query: Record<string, string> = {}

    if (filters.value.q) query.q = filters.value.q
    if (filters.value.status?.length) query.status = filters.value.status.join(',')
    if (filters.value.risk_level?.length) query.risk_level = filters.value.risk_level.join(',')
    if (filters.value.file_type?.length) query.file_type = filters.value.file_type.join(',')
    if (filters.value.date_from) query.date_from = filters.value.date_from
    if (filters.value.date_to) query.date_to = filters.value.date_to
    if (filters.value.has_deadlines !== undefined)
      query.has_deadlines = String(filters.value.has_deadlines)
    if (filters.value.sort_by && filters.value.sort_by !== 'created_at')
      query.sort_by = filters.value.sort_by
    if (filters.value.sort_order && filters.value.sort_order !== 'desc')
      query.sort_order = filters.value.sort_order
    if (filters.value.page && filters.value.page > 1) query.page = String(filters.value.page)

    return query
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
    filters.value.page = page
    await fetchContracts()
  }

  return {
    // State
    contracts,
    currentContract,
    loading,
    error,
    pagination,
    upload,
    filters,
    filterMeta,

    // Computed
    hasContracts,
    hasMorePages,
    isUploading,
    hasActiveFilters,
    activeFilterCount,

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
    search,
    setFilters,
    clearFilters,
    setSort,
    initFiltersFromQuery,
    getFiltersAsQuery,
  }
})

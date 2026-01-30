import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { ContractComparisonResult, Contract } from '@/types'
import * as comparisonService from '@/services/comparison'
import * as contractsService from '@/services/contracts'

export const useComparisonStore = defineStore('comparison', () => {
  // State
  const availableContracts = ref<Contract[]>([])
  const contractIdA = ref<string | null>(null)
  const contractIdB = ref<string | null>(null)
  const result = ref<ContractComparisonResult | null>(null)
  const loading = ref(false)
  const loadingContracts = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const canCompare = computed(
    () => contractIdA.value !== null && contractIdB.value !== null && !loading.value,
  )

  const hasResult = computed(() => result.value !== null)

  const similarityPercent = computed(() => {
    if (!result.value) return 0
    return Math.round(result.value.similarity_score * 100)
  })

  const completedContracts = computed(() =>
    availableContracts.value.filter((c) => c.status === 'completed'),
  )

  /**
   * Fetch available contracts for comparison
   */
  async function fetchAvailableContracts(): Promise<void> {
    loadingContracts.value = true
    error.value = null

    try {
      // Fetch all completed contracts
      const response = await contractsService.getContracts({
        status: ['completed'],
        per_page: 100,
        sort_by: 'created_at',
        sort_order: 'desc',
      })
      availableContracts.value = response.data
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to load contracts'
      throw e
    } finally {
      loadingContracts.value = false
    }
  }

  /**
   * Select contract A
   */
  function selectContractA(id: string | null): void {
    contractIdA.value = id
    // Clear result when selection changes
    result.value = null
    error.value = null
  }

  /**
   * Select contract B
   */
  function selectContractB(id: string | null): void {
    contractIdB.value = id
    // Clear result when selection changes
    result.value = null
    error.value = null
  }

  /**
   * Swap selected contracts
   */
  function swapContracts(): void {
    const temp = contractIdA.value
    contractIdA.value = contractIdB.value
    contractIdB.value = temp
    // Clear result when swapping
    result.value = null
    error.value = null
  }

  /**
   * Compare the selected contracts
   */
  async function compare(): Promise<void> {
    if (!contractIdA.value || !contractIdB.value) {
      error.value = 'Please select two contracts to compare'
      return
    }

    loading.value = true
    error.value = null

    try {
      result.value = await comparisonService.compareContracts({
        contract_id_a: contractIdA.value,
        contract_id_b: contractIdB.value,
      })
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Failed to compare contracts'
      result.value = null
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Clear comparison result
   */
  function clearResult(): void {
    result.value = null
    error.value = null
  }

  /**
   * Reset all state
   */
  function reset(): void {
    contractIdA.value = null
    contractIdB.value = null
    result.value = null
    error.value = null
  }

  /**
   * Get contract by ID from available contracts
   */
  function getContractById(id: string): Contract | undefined {
    return availableContracts.value.find((c) => c.id === id)
  }

  return {
    // State
    availableContracts,
    contractIdA,
    contractIdB,
    result,
    loading,
    loadingContracts,
    error,

    // Computed
    canCompare,
    hasResult,
    similarityPercent,
    completedContracts,

    // Actions
    fetchAvailableContracts,
    selectContractA,
    selectContractB,
    swapContracts,
    compare,
    clearResult,
    reset,
    getContractById,
  }
})

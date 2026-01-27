import { ref } from 'vue'
import { defineStore } from 'pinia'
import type { Contract } from '@/types'

export const useContractsStore = defineStore('contracts', () => {
  const contracts = ref<Contract[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  function setContracts(newContracts: Contract[]): void {
    contracts.value = newContracts
  }

  function addContract(contract: Contract): void {
    contracts.value.unshift(contract)
  }

  function updateContract(id: number, data: Partial<Contract>): void {
    const index = contracts.value.findIndex((c) => c.id === id)
    if (index !== -1) {
      contracts.value[index] = { ...contracts.value[index], ...data }
    }
  }

  function removeContract(id: number): void {
    contracts.value = contracts.value.filter((c) => c.id !== id)
  }

  return {
    contracts,
    loading,
    error,
    setContracts,
    addContract,
    updateContract,
    removeContract,
  }
})

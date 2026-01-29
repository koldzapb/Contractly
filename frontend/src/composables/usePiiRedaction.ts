import { ref, computed } from 'vue'
import type { DetectedPii, PiiType } from '@/types'

export function usePiiRedaction(initialItems: DetectedPii[] = []) {
  // State
  const selectedIds = ref<Set<string>>(new Set())
  const items = ref<DetectedPii[]>(initialItems)

  // Initialize with all items selected
  if (initialItems.length > 0) {
    selectedIds.value = new Set(initialItems.map((item) => item.id))
  }

  // Computed
  const selectedCount = computed(() => selectedIds.value.size)

  const totalCount = computed(() => items.value.length)

  const allSelected = computed(() => {
    return selectedIds.value.size === items.value.length && items.value.length > 0
  })

  const noneSelected = computed(() => {
    return selectedIds.value.size === 0
  })

  const selectedItems = computed(() => {
    return items.value.filter((item) => selectedIds.value.has(item.id))
  })

  const selectedCountsByType = computed(() => {
    const counts: Record<PiiType, number> = {
      ssn: 0,
      email: 0,
      phone: 0,
      credit_card: 0,
      bank_routing: 0,
      bank_account: 0,
    }

    for (const item of items.value) {
      if (selectedIds.value.has(item.id)) {
        counts[item.type]++
      }
    }

    return counts
  })

  const countsByType = computed(() => {
    const counts: Record<PiiType, number> = {
      ssn: 0,
      email: 0,
      phone: 0,
      credit_card: 0,
      bank_routing: 0,
      bank_account: 0,
    }

    for (const item of items.value) {
      counts[item.type]++
    }

    return counts
  })

  const redactionOptions = computed(() => {
    return Array.from(selectedIds.value)
  })

  // Actions
  function setItems(newItems: DetectedPii[]): void {
    items.value = newItems
    selectedIds.value = new Set(newItems.map((item) => item.id))
  }

  function toggleItem(id: string): void {
    const newSet = new Set(selectedIds.value)
    if (newSet.has(id)) {
      newSet.delete(id)
    } else {
      newSet.add(id)
    }
    selectedIds.value = newSet
  }

  function selectAll(): void {
    selectedIds.value = new Set(items.value.map((item) => item.id))
  }

  function clearAll(): void {
    selectedIds.value = new Set()
  }

  function toggleType(type: PiiType): void {
    const typeItems = items.value.filter((item) => item.type === type)
    const allTypeSelected = typeItems.every((item) => selectedIds.value.has(item.id))

    const newSet = new Set(selectedIds.value)
    for (const item of typeItems) {
      if (allTypeSelected) {
        newSet.delete(item.id)
      } else {
        newSet.add(item.id)
      }
    }
    selectedIds.value = newSet
  }

  function selectType(type: PiiType): void {
    const newSet = new Set(selectedIds.value)
    for (const item of items.value) {
      if (item.type === type) {
        newSet.add(item.id)
      }
    }
    selectedIds.value = newSet
  }

  function deselectType(type: PiiType): void {
    const newSet = new Set(selectedIds.value)
    for (const item of items.value) {
      if (item.type === type) {
        newSet.delete(item.id)
      }
    }
    selectedIds.value = newSet
  }

  function isSelected(id: string): boolean {
    return selectedIds.value.has(id)
  }

  function isTypeFullySelected(type: PiiType): boolean {
    const typeItems = items.value.filter((item) => item.type === type)
    if (typeItems.length === 0) return false
    return typeItems.every((item) => selectedIds.value.has(item.id))
  }

  function reset(): void {
    items.value = []
    selectedIds.value = new Set()
  }

  return {
    // State
    items,
    selectedIds,

    // Computed
    selectedCount,
    totalCount,
    allSelected,
    noneSelected,
    selectedItems,
    selectedCountsByType,
    countsByType,
    redactionOptions,

    // Actions
    setItems,
    toggleItem,
    selectAll,
    clearAll,
    toggleType,
    selectType,
    deselectType,
    isSelected,
    isTypeFullySelected,
    reset,
  }
}

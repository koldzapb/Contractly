<script setup lang="ts">
import { computed } from 'vue'
import type { Contract } from '@/types'

interface Props {
  contracts: Contract[]
  modelValue: string | null
  label: string
  excludeId?: string | null
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  excludeId: null,
  loading: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | null]
}>()

const filteredContracts = computed(() => {
  if (!props.excludeId) return props.contracts
  return props.contracts.filter((c) => c.id !== props.excludeId)
})

const selectedContract = computed(() => {
  if (!props.modelValue) return null
  return props.contracts.find((c) => c.id === props.modelValue) ?? null
})

function handleChange(event: Event): void {
  const target = event.target as HTMLSelectElement
  emit('update:modelValue', target.value || null)
}

function getRiskBadgeClass(riskLevel: string | null): string {
  if (!riskLevel) return ''
  const classes: Record<string, string> = {
    low: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    high: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  }
  return classes[riskLevel] ?? ''
}
</script>

<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
      {{ label }}
    </label>
    <div class="relative">
      <select
        :value="modelValue ?? ''"
        :disabled="loading"
        class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 py-2.5 pl-3 pr-10 text-gray-900 dark:text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:bg-gray-100 dark:disabled:bg-gray-900 disabled:cursor-not-allowed"
        @change="handleChange"
      >
        <option value="">Select a contract...</option>
        <option v-for="contract in filteredContracts" :key="contract.id" :value="contract.id">
          {{ contract.title }}
        </option>
      </select>
      <div
        v-if="loading"
        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
      >
        <svg
          class="animate-spin h-5 w-5 text-gray-400"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          />
        </svg>
      </div>
    </div>

    <!-- Selected contract info -->
    <div v-if="selectedContract" class="mt-2 flex items-center gap-2 text-sm">
      <span class="text-gray-500 dark:text-gray-400"> Uploaded: </span>
      <span class="text-gray-700 dark:text-gray-300">
        {{ new Date(selectedContract.created_at).toLocaleDateString() }}
      </span>
      <span
        v-if="selectedContract.overall_risk_level"
        :class="getRiskBadgeClass(selectedContract.overall_risk_level)"
        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
      >
        {{ selectedContract.overall_risk_level }}
      </span>
    </div>
  </div>
</template>

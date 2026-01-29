<script setup lang="ts">
import { computed } from 'vue'
import type { PiiType } from '@/types'
import PiiTypeBadge from './PiiTypeBadge.vue'

interface Props {
  countsByType: Record<PiiType, number>
  selectedCountsByType: Record<PiiType, number>
  totalCount: number
  selectedCount: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'select-all': []
  'clear-all': []
  'toggle-type': [type: PiiType]
}>()

const piiTypes = computed(() => {
  return (Object.entries(props.countsByType) as [PiiType, number][])
    .filter(([, count]) => count > 0)
    .sort(([, a], [, b]) => b - a)
})

const allSelected = computed(() => {
  return props.selectedCount === props.totalCount && props.totalCount > 0
})

const noneSelected = computed(() => {
  return props.selectedCount === 0
})

function isTypeFullySelected(type: PiiType): boolean {
  return (props.selectedCountsByType[type] || 0) === (props.countsByType[type] || 0)
}

function handleSelectAll(): void {
  emit('select-all')
}

function handleClearAll(): void {
  emit('clear-all')
}

function handleToggleType(type: PiiType): void {
  emit('toggle-type', type)
}
</script>

<template>
  <div class="space-y-4">
    <!-- Summary -->
    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
      <span> {{ selectedCount }} of {{ totalCount }} items selected for redaction </span>
    </div>

    <!-- Bulk Actions -->
    <div class="flex flex-wrap gap-2">
      <button
        type="button"
        class="btn-secondary text-xs"
        :disabled="allSelected"
        @click="handleSelectAll"
      >
        <svg
          class="h-3.5 w-3.5 mr-1"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        Redact All
      </button>
      <button
        type="button"
        class="btn-secondary text-xs"
        :disabled="noneSelected"
        @click="handleClearAll"
      >
        <svg
          class="h-3.5 w-3.5 mr-1"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
        Clear All
      </button>
    </div>

    <!-- Type Controls -->
    <div v-if="piiTypes.length > 0" class="space-y-2">
      <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
        By Type
      </p>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="[type, count] in piiTypes"
          :key="type"
          type="button"
          class="group flex items-center gap-1.5 px-2 py-1 rounded-lg border transition-colors"
          :class="[
            isTypeFullySelected(type)
              ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
              : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
          ]"
          @click="handleToggleType(type)"
        >
          <PiiTypeBadge :type="type" size="sm" />
          <span class="text-xs text-gray-500 dark:text-gray-400">
            {{ selectedCountsByType[type] || 0 }}/{{ count }}
          </span>
          <svg
            v-if="isTypeFullySelected(type)"
            class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 13l4 4L19 7"
            />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

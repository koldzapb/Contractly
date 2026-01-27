<script setup lang="ts">
import { ref, computed } from 'vue'
import type { ContractClause } from '@/types'
import RiskBadge from './RiskBadge.vue'

interface Props {
  clause: ContractClause
}

const props = defineProps<Props>()

const expanded = ref(false)

const borderClass = computed(() => {
  const classes = {
    low: 'border-l-green-500',
    medium: 'border-l-yellow-500',
    high: 'border-l-red-500',
  }
  return classes[props.clause.risk_level]
})

const hasOriginalText = computed(() => !!props.clause.original_text)
</script>

<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 border-l-4 overflow-hidden"
    :class="borderClass"
  >
    <div class="p-4">
      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span
              class="text-xs font-medium px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400"
            >
              {{ clause.clause_type_label }}
            </span>
            <RiskBadge :level="clause.risk_level" size="sm" :show-label="false" />
            <span v-if="clause.page_number" class="text-xs text-gray-400 dark:text-gray-500">
              Page {{ clause.page_number }}
            </span>
          </div>
        </div>
        <button
          v-if="hasOriginalText"
          type="button"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1"
          @click="expanded = !expanded"
        >
          <svg
            class="h-5 w-5 transition-transform"
            :class="{ 'rotate-180': expanded }"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 9l-7 7-7-7"
            />
          </svg>
        </button>
      </div>

      <!-- Plain Explanation -->
      <p class="mt-3 text-gray-700 dark:text-gray-300">
        {{ clause.plain_explanation }}
      </p>

      <!-- Risk Reason -->
      <p v-if="clause.risk_reason" class="mt-2 text-sm text-gray-500 dark:text-gray-400 italic">
        {{ clause.risk_reason }}
      </p>
    </div>

    <!-- Expandable Original Text -->
    <div
      v-if="hasOriginalText && expanded"
      class="px-4 pb-4 pt-2 border-t border-gray-100 dark:border-gray-700"
    >
      <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
        Original Text
      </p>
      <p
        class="text-sm text-gray-600 dark:text-gray-400 font-mono bg-gray-50 dark:bg-gray-900 p-3 rounded"
      >
        {{ clause.original_text }}
      </p>
    </div>
  </div>
</template>

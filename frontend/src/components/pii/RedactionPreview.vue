<script setup lang="ts">
import { computed } from 'vue'
import type { DetectedPii } from '@/types'

interface Props {
  originalText: string
  items: DetectedPii[]
  selectedIds: Set<string>
}

const props = defineProps<Props>()

const redactedText = computed(() => {
  if (props.selectedIds.size === 0) {
    return props.originalText
  }

  // Sort items by position (descending) to replace from end to start
  const sortedItems = [...props.items]
    .filter((item) => props.selectedIds.has(item.id))
    .sort((a, b) => b.start_position - a.start_position)

  let result = props.originalText
  for (const item of sortedItems) {
    result =
      result.slice(0, item.start_position) + item.redacted_value + result.slice(item.end_position)
  }

  return result
})

const hasChanges = computed(() => {
  return props.selectedIds.size > 0
})
</script>

<template>
  <div class="space-y-4">
    <!-- Before/After Toggle View -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Original -->
      <div class="space-y-2">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500" aria-hidden="true" />
          Original
        </h4>
        <div
          class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 max-h-64 overflow-y-auto"
        >
          <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{
            originalText
          }}</pre>
        </div>
      </div>

      <!-- Redacted -->
      <div class="space-y-2">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
          <span
            class="w-2 h-2 rounded-full"
            :class="[hasChanges ? 'bg-green-500' : 'bg-gray-400 dark:bg-gray-500']"
            aria-hidden="true"
          />
          {{ hasChanges ? 'After Redaction' : 'No Changes' }}
        </h4>
        <div
          class="p-4 rounded-lg border max-h-64 overflow-y-auto"
          :class="[
            hasChanges
              ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'
              : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700',
          ]"
        >
          <pre
            class="text-sm whitespace-pre-wrap font-mono"
            :class="[
              hasChanges
                ? 'text-green-800 dark:text-green-300'
                : 'text-gray-700 dark:text-gray-300',
            ]"
            >{{ redactedText }}</pre
          >
        </div>
      </div>
    </div>

    <!-- Change Summary -->
    <div v-if="hasChanges" class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
        />
      </svg>
      {{ selectedIds.size }} item{{ selectedIds.size === 1 ? '' : 's' }} will be redacted
    </div>
  </div>
</template>

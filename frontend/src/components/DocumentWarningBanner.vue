<script setup lang="ts">
import { computed } from 'vue'
import type { DocumentClassification } from '@/types'

interface Props {
  classification: DocumentClassification
}

const props = defineProps<Props>()

const emit = defineEmits<{
  dismiss: []
}>()

const warningMessage = computed(() => {
  const typeMessages: Record<string, string> = {
    mou: 'This appears to be a Memorandum of Understanding (MOU), which is typically non-binding.',
    loi: 'This appears to be a Letter of Intent (LOI), which may not be legally enforceable.',
    term_sheet:
      'This appears to be a Term Sheet, which outlines preliminary terms but may not be binding.',
  }

  return (
    typeMessages[props.classification.document_type] ||
    'This document may be pre-contractual and not legally binding.'
  )
})

function handleDismiss(): void {
  emit('dismiss')
}
</script>

<template>
  <div
    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4"
    role="alert"
  >
    <div class="flex items-start gap-3">
      <svg
        class="h-5 w-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        aria-hidden="true"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
        />
      </svg>
      <div class="flex-1 min-w-0">
        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
          Pre-Contractual Document
        </h3>
        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-400">
          {{ warningMessage }}
        </p>
        <ul
          v-if="classification.warnings.length > 0"
          class="mt-2 text-sm text-yellow-700 dark:text-yellow-400 list-disc list-inside"
        >
          <li v-for="warning in classification.warnings" :key="warning">
            {{ warning }}
          </li>
        </ul>
      </div>
      <button
        type="button"
        class="flex-shrink-0 p-1 rounded-md text-yellow-600 dark:text-yellow-400 hover:bg-yellow-100 dark:hover:bg-yellow-900/40 focus:outline-none focus:ring-2 focus:ring-yellow-500"
        aria-label="Dismiss warning"
        @click="handleDismiss"
      >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
    </div>
  </div>
</template>

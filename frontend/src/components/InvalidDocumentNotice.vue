<script setup lang="ts">
import { computed } from 'vue'
import type { DocumentClassification } from '@/types'

interface Props {
  classification: DocumentClassification
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

const emit = defineEmits<{
  'analyze-anyway': []
  cancel: []
}>()

const documentTypeLabel = computed(() => {
  return props.classification.document_type_label || 'Unknown Document'
})

const rejectionMessage = computed(() => {
  if (props.classification.rejection_reason) {
    return props.classification.rejection_reason
  }

  const typeMessages: Record<string, string> = {
    invoice: 'This appears to be an invoice, not a legal contract.',
    receipt: 'This appears to be a receipt, not a legal contract.',
    letter: 'This appears to be a general letter, not a legal contract.',
    report: 'This appears to be a report, not a legal contract.',
    other: 'This document does not appear to be a legal contract.',
    unknown: 'We could not determine the document type.',
  }

  return (
    typeMessages[props.classification.document_type] ||
    'This document does not appear to be a legal contract.'
  )
})

function handleAnalyzeAnyway(): void {
  emit('analyze-anyway')
}

function handleCancel(): void {
  emit('cancel')
}
</script>

<template>
  <div
    class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6"
    role="alert"
    aria-labelledby="invalid-doc-title"
  >
    <div
      class="flex flex-col items-center text-center sm:flex-row sm:items-start sm:text-left gap-4"
    >
      <div
        class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center"
      >
        <svg
          class="h-6 w-6 text-red-600 dark:text-red-400"
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
      </div>
      <div class="flex-1">
        <h3 id="invalid-doc-title" class="text-lg font-semibold text-red-800 dark:text-red-300">
          Document Type: {{ documentTypeLabel }}
        </h3>
        <p class="mt-2 text-sm text-red-700 dark:text-red-400">
          {{ rejectionMessage }}
        </p>
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">
          Contractly is designed to analyze legal contracts. Non-contract documents may not produce
          useful analysis results.
        </p>

        <div class="mt-4 flex flex-col sm:flex-row gap-3">
          <button
            type="button"
            class="btn-secondary text-sm text-red-600 dark:text-red-400 border-red-300 dark:border-red-700 hover:bg-red-100 dark:hover:bg-red-900/40"
            :disabled="loading"
            @click="handleAnalyzeAnyway"
          >
            <svg
              v-if="loading"
              class="animate-spin -ml-1 mr-2 h-4 w-4"
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
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
              />
            </svg>
            {{ loading ? 'Analyzing...' : 'Analyze Anyway' }}
          </button>
          <button
            type="button"
            class="btn-secondary text-sm"
            :disabled="loading"
            @click="handleCancel"
          >
            Go Back
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

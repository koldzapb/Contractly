<script setup lang="ts">
import { computed } from 'vue'
import type { ContractStatus } from '@/types'

interface Props {
  status: ContractStatus
  errorMessage?: string | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  retry: []
}>()

const isPending = computed(() => props.status === 'pending')
const isProcessing = computed(() => props.status === 'processing')
const isFailed = computed(() => props.status === 'failed')
const isInProgress = computed(() => isPending.value || isProcessing.value)

const statusMessage = computed(() => {
  if (isPending.value) return 'Waiting to start analysis...'
  if (isProcessing.value) return 'Analyzing contract with AI...'
  if (isFailed.value) return 'Analysis failed'
  return ''
})

const statusDescription = computed(() => {
  if (isPending.value)
    return 'Your contract is queued for analysis. This usually starts within a few seconds.'
  if (isProcessing.value)
    return 'Our AI is extracting clauses, identifying risks, and finding important deadlines.'
  if (isFailed.value)
    return props.errorMessage || 'An error occurred during analysis. Please try again.'
  return ''
})
</script>

<template>
  <div class="card">
    <div class="text-center py-8">
      <!-- Spinner for in-progress states -->
      <div v-if="isInProgress" class="mb-4">
        <div class="relative inline-flex">
          <div class="h-16 w-16 rounded-full border-4 border-indigo-200 dark:border-indigo-900" />
          <div
            class="absolute inset-0 h-16 w-16 rounded-full border-4 border-indigo-600 dark:border-indigo-400 border-t-transparent animate-spin"
          />
        </div>
      </div>

      <!-- Error icon for failed state -->
      <div v-else-if="isFailed" class="mb-4">
        <div
          class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900/30"
        >
          <svg
            class="h-8 w-8 text-red-600 dark:text-red-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
        </div>
      </div>

      <h3
        class="text-lg font-semibold mb-2"
        :class="isFailed ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'"
      >
        {{ statusMessage }}
      </h3>

      <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
        {{ statusDescription }}
      </p>

      <!-- Progress steps for processing state -->
      <div v-if="isProcessing" class="mt-6 flex justify-center gap-8 text-sm">
        <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
          <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
              clip-rule="evenodd"
            />
          </svg>
          <span>PDF Parsed</span>
        </div>
        <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
          <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
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
          <span>AI Analysis</span>
        </div>
        <div class="flex items-center gap-2 text-gray-400 dark:text-gray-500">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <circle cx="12" cy="12" r="10" stroke-width="2" />
          </svg>
          <span>Results</span>
        </div>
      </div>

      <!-- Retry button for failed state -->
      <button v-if="isFailed" type="button" class="btn-primary mt-6" @click="emit('retry')">
        Retry Analysis
      </button>
    </div>
  </div>
</template>

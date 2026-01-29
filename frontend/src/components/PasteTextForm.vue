<script setup lang="ts">
import { ref, computed } from 'vue'
import { analyzeText } from '@/services/quickAnalysis'
import type { QuickAnalysisResult } from '@/types/quickAnalysis'
import type { AxiosError } from 'axios'

const emit = defineEmits<{
  analyze: [result: QuickAnalysisResult]
}>()

const MIN_CHARS = 100
const MAX_CHARS = 500000

const text = ref('')
const title = ref('')
const isLoading = ref(false)
const error = ref<string | null>(null)

const charCount = computed(() => text.value.length)
const isValidLength = computed(() => charCount.value >= MIN_CHARS && charCount.value <= MAX_CHARS)
const canSubmit = computed(() => isValidLength.value && !isLoading.value)

const charCountClass = computed(() => {
  if (charCount.value === 0) return 'text-gray-500 dark:text-gray-400'
  if (charCount.value < MIN_CHARS) return 'text-amber-600 dark:text-amber-400'
  if (charCount.value > MAX_CHARS) return 'text-red-600 dark:text-red-400'
  return 'text-green-600 dark:text-green-400'
})

async function handleSubmit(): Promise<void> {
  if (!canSubmit.value) return

  isLoading.value = true
  error.value = null

  try {
    const result = await analyzeText({
      text: text.value,
      title: title.value || undefined,
    })
    emit('analyze', result)
  } catch (err) {
    const axiosError = err as AxiosError<{ message?: string; errors?: Record<string, string[]> }>
    if (axiosError.response?.data?.message) {
      error.value = axiosError.response.data.message
    } else if (axiosError.response?.data?.errors?.text?.[0]) {
      error.value = axiosError.response.data.errors.text[0]
    } else {
      error.value = 'Analysis failed. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}

function handleClear(): void {
  text.value = ''
  title.value = ''
  error.value = null
}
</script>

<template>
  <div class="space-y-4">
    <!-- Optional Title -->
    <div>
      <label
        for="analysis-title"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      >
        Title (optional)
      </label>
      <input
        id="analysis-title"
        v-model="title"
        type="text"
        maxlength="255"
        placeholder="e.g., Service Agreement Draft"
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
        :disabled="isLoading"
      />
    </div>

    <!-- Contract Text Textarea -->
    <div>
      <label
        for="contract-text"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      >
        Contract Text
      </label>
      <textarea
        id="contract-text"
        v-model="text"
        rows="12"
        placeholder="Paste your contract text here..."
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-y font-mono text-sm"
        :disabled="isLoading"
      ></textarea>

      <!-- Character Count -->
      <div class="flex justify-between items-center mt-1">
        <span :class="charCountClass" class="text-xs">
          {{ charCount.toLocaleString() }} / {{ MAX_CHARS.toLocaleString() }} characters
          <span v-if="charCount > 0 && charCount < MIN_CHARS"> (minimum {{ MIN_CHARS }}) </span>
        </span>
        <button
          v-if="charCount > 0"
          type="button"
          class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
          @click="handleClear"
        >
          Clear
        </button>
      </div>
    </div>

    <!-- Error Message -->
    <div
      v-if="error"
      class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-3"
    >
      <div class="flex items-center gap-2">
        <svg
          class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0"
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
        <p class="text-sm text-red-700 dark:text-red-300">{{ error }}</p>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="flex justify-end">
      <button
        type="button"
        :disabled="!canSubmit"
        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        @click="handleSubmit"
      >
        <template v-if="isLoading">
          <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            ></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
          </svg>
          <span>Analyzing...</span>
        </template>
        <template v-else>
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
            />
          </svg>
          <span>Analyze Text</span>
        </template>
      </button>
    </div>

    <!-- Info Notice -->
    <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
      Analysis results are temporary and will not be saved. Upload a file for permanent storage.
    </p>
  </div>
</template>

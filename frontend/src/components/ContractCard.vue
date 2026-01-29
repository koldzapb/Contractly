<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import type { Contract } from '@/types'

interface Props {
  contract: Contract
}

const props = defineProps<Props>()

const emit = defineEmits<{
  delete: [id: string]
}>()

const statusConfig = computed(() => {
  const configs = {
    pending: {
      label: 'Pending',
      bgClass: 'bg-yellow-100 dark:bg-yellow-900/30',
      textClass: 'text-yellow-800 dark:text-yellow-300',
    },
    processing: {
      label: 'Processing',
      bgClass: 'bg-blue-100 dark:bg-blue-900/30',
      textClass: 'text-blue-800 dark:text-blue-300',
    },
    completed: {
      label: 'Analyzed',
      bgClass: 'bg-green-100 dark:bg-green-900/30',
      textClass: 'text-green-800 dark:text-green-300',
    },
    failed: {
      label: 'Failed',
      bgClass: 'bg-red-100 dark:bg-red-900/30',
      textClass: 'text-red-800 dark:text-red-300',
    },
  }
  return configs[props.contract.status] || configs.pending
})

const riskConfig = computed(() => {
  if (!props.contract.overall_risk_level) return null

  const configs = {
    low: {
      label: 'Low Risk',
      bgClass: 'bg-green-100 dark:bg-green-900/30',
      textClass: 'text-green-800 dark:text-green-300',
    },
    medium: {
      label: 'Medium Risk',
      bgClass: 'bg-yellow-100 dark:bg-yellow-900/30',
      textClass: 'text-yellow-800 dark:text-yellow-300',
    },
    high: {
      label: 'High Risk',
      bgClass: 'bg-red-100 dark:bg-red-900/30',
      textClass: 'text-red-800 dark:text-red-300',
    },
  }
  return configs[props.contract.overall_risk_level]
})

const formattedDate = computed(() => {
  const date = new Date(props.contract.created_at)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
})

const fileTypeConfig = computed(() => {
  const fileType = props.contract.file_type || 'pdf'
  const configs = {
    pdf: {
      icon: 'document',
      label: 'PDF',
      colorClass: 'text-red-500 dark:text-red-400',
    },
    image: {
      icon: 'photograph',
      label: 'Image',
      colorClass: 'text-blue-500 dark:text-blue-400',
    },
    text: {
      icon: 'document-text',
      label: 'Text',
      colorClass: 'text-gray-500 dark:text-gray-400',
    },
  }
  return configs[fileType] || configs.pdf
})

function handleDelete(): void {
  emit('delete', props.contract.id)
}
</script>

<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-indigo-500 dark:hover:border-indigo-400 transition-colors"
  >
    <div class="flex items-start justify-between">
      <div class="flex items-start gap-3">
        <!-- File type icon -->
        <div :class="fileTypeConfig.colorClass" class="flex-shrink-0 mt-0.5" :title="fileTypeConfig.label">
          <!-- PDF icon -->
          <svg
            v-if="fileTypeConfig.icon === 'document'"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
            />
          </svg>
          <!-- Image icon -->
          <svg
            v-else-if="fileTypeConfig.icon === 'photograph'"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
            />
          </svg>
          <!-- Text icon -->
          <svg
            v-else
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
        </div>

        <div class="flex-1 min-w-0">
          <RouterLink
            :to="`/contracts/${contract.id}`"
            class="block text-gray-900 dark:text-white font-medium hover:text-indigo-600 dark:hover:text-indigo-400 truncate"
          >
            {{ contract.title }}
          </RouterLink>
          <p class="text-sm text-gray-500 dark:text-gray-400 truncate mt-1">
            {{ contract.original_filename }}
          </p>
        </div>
      </div>

      <!-- Delete button -->
      <button
        type="button"
        class="ml-4 p-2 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
        title="Delete contract"
        aria-label="Delete contract"
        @click="handleDelete"
      >
        <svg
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
          />
        </svg>
      </button>
    </div>

    <div class="mt-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <!-- Status badge -->
        <span
          :class="[statusConfig.bgClass, statusConfig.textClass]"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
        >
          <!-- Processing spinner -->
          <svg
            v-if="contract.status === 'processing'"
            class="animate-spin -ml-0.5 mr-1.5 h-3 w-3"
            fill="none"
            viewBox="0 0 24 24"
            aria-hidden="true"
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
          {{ statusConfig.label }}
        </span>

        <!-- Risk level badge -->
        <span
          v-if="riskConfig"
          :class="[riskConfig.bgClass, riskConfig.textClass]"
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
        >
          {{ riskConfig.label }}
        </span>
      </div>

      <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
        <span>{{ contract.file_size_human }}</span>
        <span>{{ formattedDate }}</span>
      </div>
    </div>

    <!-- Error message for failed contracts -->
    <div
      v-if="contract.status === 'failed' && contract.error_message"
      class="mt-3 p-2 bg-red-50 dark:bg-red-900/20 rounded text-sm text-red-600 dark:text-red-400"
    >
      {{ contract.error_message }}
    </div>
  </div>
</template>

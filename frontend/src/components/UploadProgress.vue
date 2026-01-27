<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  progress: number
  filename: string
}

const props = defineProps<Props>()

const progressWidth = computed(() => `${props.progress}%`)
const isComplete = computed(() => props.progress >= 100)
</script>

<template>
  <div class="py-8 px-4">
    <div class="flex items-center justify-center mb-4">
      <!-- Spinner when uploading -->
      <div v-if="!isComplete" class="relative">
        <svg
          class="animate-spin h-10 w-10 text-indigo-600 dark:text-indigo-400"
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
      <!-- Checkmark when complete -->
      <div
        v-else
        class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center"
      >
        <svg
          class="h-6 w-6 text-green-600 dark:text-green-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M5 13l4 4L19 7"
          />
        </svg>
      </div>
    </div>

    <div class="text-center mb-4">
      <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-xs mx-auto">
        {{ filename }}
      </p>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ isComplete ? 'Upload complete' : 'Uploading...' }}
      </p>
    </div>

    <!-- Progress bar -->
    <div class="max-w-xs mx-auto">
      <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
        <span>Progress</span>
        <span>{{ progress }}%</span>
      </div>
      <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
        <div
          class="h-full bg-indigo-600 dark:bg-indigo-500 rounded-full transition-all duration-300 ease-out"
          :style="{ width: progressWidth }"
        />
      </div>
    </div>
  </div>
</template>

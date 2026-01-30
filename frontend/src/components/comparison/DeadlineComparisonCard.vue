<script setup lang="ts">
import { computed } from 'vue'
import type { DeadlineComparison } from '@/types'

interface Props {
  comparison: DeadlineComparison
}

const props = defineProps<Props>()

const deadlineType = computed(() => {
  return (
    props.comparison.deadline_a?.deadline_type_label ??
    props.comparison.deadline_b?.deadline_type_label ??
    ''
  )
})

const borderClass = computed(() => {
  if (props.comparison.match_type === 'matched') {
    if (props.comparison.has_date_difference) {
      return 'border-l-yellow-500'
    }
    return 'border-l-green-500'
  }
  if (props.comparison.match_type === 'only_in_a') {
    return 'border-l-indigo-500'
  }
  return 'border-l-purple-500'
})

const matchBadgeClass = computed(() => {
  if (props.comparison.match_type === 'matched') {
    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
  }
  if (props.comparison.match_type === 'only_in_a') {
    return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400'
  }
  return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400'
})

const matchLabel = computed(() => {
  if (props.comparison.match_type === 'matched') return 'Matched'
  if (props.comparison.match_type === 'only_in_a') return 'Only in A'
  return 'Only in B'
})

function formatDate(dateString: string | null): string {
  if (!dateString) return 'No date specified'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const daysDifferenceText = computed(() => {
  if (props.comparison.days_difference === null) return null
  const days = Math.abs(props.comparison.days_difference)
  if (days === 0) return 'Same date'
  const direction = props.comparison.days_difference > 0 ? 'later' : 'earlier'
  return `${days} day${days === 1 ? '' : 's'} ${direction} in B`
})
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
              {{ deadlineType }}
            </span>
            <span :class="matchBadgeClass" class="text-xs font-medium px-2 py-0.5 rounded">
              {{ matchLabel }}
            </span>
            <span
              v-if="comparison.has_date_difference"
              class="text-xs font-medium px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400"
            >
              Date Changed
            </span>
          </div>
        </div>
      </div>

      <!-- Title -->
      <h4 class="mt-2 font-medium text-gray-900 dark:text-white">
        {{ comparison.deadline_a?.title ?? comparison.deadline_b?.title }}
      </h4>

      <!-- Dates comparison for matched -->
      <div v-if="comparison.match_type === 'matched'" class="mt-3 grid grid-cols-2 gap-4">
        <div>
          <span class="text-xs text-gray-500 dark:text-gray-400 block">Contract A</span>
          <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
            {{ formatDate(comparison.deadline_a?.deadline_date ?? null) }}
          </span>
        </div>
        <div>
          <span class="text-xs text-gray-500 dark:text-gray-400 block">Contract B</span>
          <span class="text-sm font-medium text-purple-600 dark:text-purple-400">
            {{ formatDate(comparison.deadline_b?.deadline_date ?? null) }}
          </span>
        </div>
      </div>

      <!-- Single deadline date for only_in_a or only_in_b -->
      <div v-else class="mt-3">
        <span class="text-xs text-gray-500 dark:text-gray-400 block">Date</span>
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {{
            formatDate(
              comparison.deadline_a?.deadline_date ?? comparison.deadline_b?.deadline_date ?? null,
            )
          }}
        </span>
      </div>

      <!-- Days difference -->
      <p
        v-if="daysDifferenceText"
        class="mt-2 text-sm"
        :class="
          comparison.has_date_difference
            ? 'text-yellow-600 dark:text-yellow-400'
            : 'text-gray-500 dark:text-gray-400'
        "
      >
        {{ daysDifferenceText }}
      </p>

      <!-- Description -->
      <p
        v-if="comparison.deadline_a?.description || comparison.deadline_b?.description"
        class="mt-3 text-sm text-gray-600 dark:text-gray-400"
      >
        {{ comparison.deadline_a?.description ?? comparison.deadline_b?.description }}
      </p>

      <!-- Recurring badges -->
      <div
        v-if="comparison.deadline_a?.is_recurring || comparison.deadline_b?.is_recurring"
        class="mt-3 flex flex-wrap gap-2"
      >
        <span
          v-if="comparison.deadline_a?.is_recurring"
          class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400"
        >
          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
          A: {{ comparison.deadline_a.recurrence_pattern || 'Recurring' }}
        </span>
        <span
          v-if="comparison.deadline_b?.is_recurring"
          class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400"
        >
          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
          B: {{ comparison.deadline_b.recurrence_pattern || 'Recurring' }}
        </span>
      </div>
    </div>
  </div>
</template>

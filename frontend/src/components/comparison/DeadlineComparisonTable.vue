<script setup lang="ts">
import { computed } from 'vue'
import type { DeadlineComparison } from '@/types'
import DeadlineComparisonCard from './DeadlineComparisonCard.vue'

interface Props {
  matched: DeadlineComparison[]
  onlyInA: DeadlineComparison[]
  onlyInB: DeadlineComparison[]
}

const props = defineProps<Props>()

const totalDeadlines = computed(() => {
  return props.matched.length + props.onlyInA.length + props.onlyInB.length
})

const hasDateDifferences = computed(() => {
  return props.matched.some((d) => d.has_date_difference)
})
</script>

<template>
  <div class="space-y-6">
    <!-- Summary stats -->
    <div class="flex flex-wrap items-center gap-4 text-sm">
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-green-500" />
        <span class="text-gray-600 dark:text-gray-400"> {{ matched.length }} matched </span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-indigo-500" />
        <span class="text-gray-600 dark:text-gray-400"> {{ onlyInA.length }} only in A </span>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-purple-500" />
        <span class="text-gray-600 dark:text-gray-400"> {{ onlyInB.length }} only in B </span>
      </div>
      <div v-if="hasDateDifferences" class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-yellow-500" />
        <span class="text-yellow-600 dark:text-yellow-400 font-medium">
          Date differences detected
        </span>
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="totalDeadlines === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      <svg
        class="mx-auto h-12 w-12 text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
        />
      </svg>
      <p class="mt-2">No deadlines to compare</p>
    </div>

    <!-- Matched deadlines -->
    <div v-if="matched.length > 0">
      <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3 flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-green-500" />
        Matched Deadlines ({{ matched.length }})
      </h4>
      <div class="space-y-3">
        <DeadlineComparisonCard
          v-for="(comparison, index) in matched"
          :key="`matched-${index}`"
          :comparison="comparison"
        />
      </div>
    </div>

    <!-- Only in A -->
    <div v-if="onlyInA.length > 0">
      <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3 flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-indigo-500" />
        Only in Contract A ({{ onlyInA.length }})
      </h4>
      <div class="space-y-3">
        <DeadlineComparisonCard
          v-for="(comparison, index) in onlyInA"
          :key="`only-a-${index}`"
          :comparison="comparison"
        />
      </div>
    </div>

    <!-- Only in B -->
    <div v-if="onlyInB.length > 0">
      <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3 flex items-center gap-2">
        <span class="w-3 h-3 rounded-full bg-purple-500" />
        Only in Contract B ({{ onlyInB.length }})
      </h4>
      <div class="space-y-3">
        <DeadlineComparisonCard
          v-for="(comparison, index) in onlyInB"
          :key="`only-b-${index}`"
          :comparison="comparison"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { ContractDeadline } from '@/types'
import DeadlineCard from './DeadlineCard.vue'

interface Props {
  deadlines: ContractDeadline[]
}

const props = defineProps<Props>()

const sortedDeadlines = computed(() => {
  return [...props.deadlines].sort((a, b) => {
    // Sort by urgency priority, then by date
    const urgencyOrder = { overdue: 0, urgent: 1, soon: 2, normal: 3, far: 4 }
    const urgencyA = urgencyOrder[a.urgency || 'normal']
    const urgencyB = urgencyOrder[b.urgency || 'normal']

    if (urgencyA !== urgencyB) {
      return urgencyA - urgencyB
    }

    // Sort by date if same urgency
    if (a.deadline_date && b.deadline_date) {
      return new Date(a.deadline_date).getTime() - new Date(b.deadline_date).getTime()
    }
    return 0
  })
})

const upcomingDeadlines = computed(() => {
  return sortedDeadlines.value.filter((d) => !d.is_past)
})

const pastDeadlines = computed(() => {
  return sortedDeadlines.value.filter((d) => d.is_past)
})

const hasUpcoming = computed(() => upcomingDeadlines.value.length > 0)
const hasPast = computed(() => pastDeadlines.value.length > 0)
</script>

<template>
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        Important Deadlines
        <span class="text-gray-400 dark:text-gray-500 font-normal">({{ deadlines.length }})</span>
      </h2>
    </div>

    <!-- Empty State -->
    <div v-if="deadlines.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
      <svg
        class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4"
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
      <p>No deadlines identified in this contract.</p>
    </div>

    <div v-else class="space-y-6">
      <!-- Upcoming Deadlines -->
      <div v-if="hasUpcoming">
        <h3
          class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide mb-3"
        >
          Upcoming
        </h3>
        <div class="space-y-3">
          <DeadlineCard
            v-for="deadline in upcomingDeadlines"
            :key="deadline.id"
            :deadline="deadline"
          />
        </div>
      </div>

      <!-- Past Deadlines -->
      <div v-if="hasPast">
        <h3
          class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3"
        >
          Past
        </h3>
        <div class="space-y-3 opacity-60">
          <DeadlineCard v-for="deadline in pastDeadlines" :key="deadline.id" :deadline="deadline" />
        </div>
      </div>
    </div>
  </div>
</template>

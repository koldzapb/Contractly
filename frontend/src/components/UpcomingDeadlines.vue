<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import type { DashboardDeadline } from '@/types'

interface Props {
  deadlines: DashboardDeadline[]
  loading?: boolean
}

const props = defineProps<Props>()

const hasDeadlines = computed(() => props.deadlines.length > 0)

interface UrgencyConfig {
  bgClass: string
  textClass: string
  label: string
}

const defaultUrgencyConfig: UrgencyConfig = {
  bgClass: 'bg-gray-100 dark:bg-gray-700',
  textClass: 'text-gray-600 dark:text-gray-400',
  label: '',
}

const urgencyConfigs: Record<string, UrgencyConfig> = {
  overdue: {
    bgClass: 'bg-red-100 dark:bg-red-900/50',
    textClass: 'text-red-800 dark:text-red-400',
    label: 'Overdue',
  },
  critical: {
    bgClass: 'bg-orange-100 dark:bg-orange-900/50',
    textClass: 'text-orange-800 dark:text-orange-400',
    label: 'Critical',
  },
  high: {
    bgClass: 'bg-yellow-100 dark:bg-yellow-900/50',
    textClass: 'text-yellow-800 dark:text-yellow-400',
    label: 'Soon',
  },
  medium: {
    bgClass: 'bg-blue-100 dark:bg-blue-900/50',
    textClass: 'text-blue-800 dark:text-blue-400',
    label: '',
  },
  low: defaultUrgencyConfig,
}

function getUrgencyConfig(urgency: string | null): UrgencyConfig {
  return urgencyConfigs[urgency ?? 'low'] ?? defaultUrgencyConfig
}

function formatDate(dateString: string | null): string {
  if (!dateString) return 'No date'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  })
}

function getDaysText(daysUntil: number | null): string {
  if (daysUntil === null) return ''
  if (daysUntil < 0) return `${Math.abs(daysUntil)}d ago`
  if (daysUntil === 0) return 'Today'
  if (daysUntil === 1) return 'Tomorrow'
  return `In ${daysUntil}d`
}
</script>

<template>
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming Deadlines</h2>
      <RouterLink
        v-if="hasDeadlines"
        to="/contracts"
        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300"
      >
        View all
      </RouterLink>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="animate-pulse">
        <div class="h-16 bg-gray-100 dark:bg-gray-700 rounded-lg"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!hasDeadlines" class="text-center py-8 text-gray-500 dark:text-gray-400">
      <svg
        class="h-12 w-12 mx-auto text-gray-300 dark:text-gray-600 mb-3"
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
      <p>No upcoming deadlines</p>
    </div>

    <!-- Deadlines List -->
    <div v-else class="space-y-3">
      <RouterLink
        v-for="deadline in deadlines"
        :key="deadline.id"
        :to="`/contracts/${deadline.contract.id}`"
        class="block p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-400 transition-colors"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-900 dark:text-white truncate">
              {{ deadline.title }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
              {{ deadline.contract.title }}
            </p>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <span
              v-if="getUrgencyConfig(deadline.urgency).label"
              :class="[
                getUrgencyConfig(deadline.urgency).bgClass,
                getUrgencyConfig(deadline.urgency).textClass,
              ]"
              class="text-xs font-medium px-2 py-0.5 rounded-full"
            >
              {{ getUrgencyConfig(deadline.urgency).label }}
            </span>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900 dark:text-white">
                {{ formatDate(deadline.deadline_date) }}
              </p>
              <p
                v-if="deadline.days_until !== null"
                class="text-xs"
                :class="getUrgencyConfig(deadline.urgency).textClass"
              >
                {{ getDaysText(deadline.days_until) }}
              </p>
            </div>
          </div>
        </div>
      </RouterLink>
    </div>
  </div>
</template>

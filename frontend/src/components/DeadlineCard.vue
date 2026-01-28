<script setup lang="ts">
import { computed } from 'vue'
import type { ContractDeadline } from '@/types'

interface Props {
  deadline: ContractDeadline
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'set-reminder': [deadline: ContractDeadline]
}>()

const canSetReminder = computed(() => {
  // Can set reminder for any deadline that's not past and doesn't already have one
  // If no date, the form will ask user to provide one
  return !props.deadline.is_past && !props.deadline.has_reminder
})

const hasReminder = computed(() => props.deadline.has_reminder)

function handleSetReminder(): void {
  emit('set-reminder', props.deadline)
}

const formattedDate = computed(() => {
  if (!props.deadline.deadline_date) return 'No date specified'
  const date = new Date(props.deadline.deadline_date)
  return date.toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
})

const urgencyConfig = computed(() => {
  const configs: Record<
    string,
    { bgClass: string; borderClass: string; iconClass: string; badgeClass: string; label: string }
  > = {
    overdue: {
      bgClass: 'bg-red-50 dark:bg-red-900/20',
      borderClass: 'border-red-200 dark:border-red-800',
      iconClass: 'text-red-500',
      badgeClass: 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-400',
      label: 'Overdue',
    },
    critical: {
      bgClass: 'bg-orange-50 dark:bg-orange-900/20',
      borderClass: 'border-orange-200 dark:border-orange-800',
      iconClass: 'text-orange-500',
      badgeClass: 'bg-orange-100 dark:bg-orange-900/50 text-orange-800 dark:text-orange-400',
      label: 'Critical',
    },
    high: {
      bgClass: 'bg-yellow-50 dark:bg-yellow-900/20',
      borderClass: 'border-yellow-200 dark:border-yellow-800',
      iconClass: 'text-yellow-500',
      badgeClass: 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-400',
      label: 'Soon',
    },
    medium: {
      bgClass: 'bg-blue-50 dark:bg-blue-900/20',
      borderClass: 'border-blue-200 dark:border-blue-800',
      iconClass: 'text-blue-500',
      badgeClass: 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-400',
      label: '',
    },
    low: {
      bgClass: 'bg-white dark:bg-gray-800',
      borderClass: 'border-gray-200 dark:border-gray-700',
      iconClass: 'text-gray-400',
      badgeClass: 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
      label: '',
    },
    unknown: {
      bgClass: 'bg-white dark:bg-gray-800',
      borderClass: 'border-gray-200 dark:border-gray-700',
      iconClass: 'text-gray-400',
      badgeClass: 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
      label: '',
    },
  }
  return configs[props.deadline.urgency || 'low'] || configs.low
})

const daysText = computed(() => {
  if (props.deadline.days_until === null) return ''
  if (props.deadline.days_until < 0) {
    return `${Math.abs(props.deadline.days_until)} days ago`
  }
  if (props.deadline.days_until === 0) return 'Today'
  if (props.deadline.days_until === 1) return 'Tomorrow'
  return `In ${props.deadline.days_until} days`
})
</script>

<template>
  <div class="rounded-lg border p-4" :class="[urgencyConfig.bgClass, urgencyConfig.borderClass]">
    <div class="flex items-start gap-3">
      <!-- Calendar Icon -->
      <div class="flex-shrink-0">
        <svg
          class="h-6 w-6"
          :class="urgencyConfig.iconClass"
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
      </div>

      <div class="flex-1 min-w-0">
        <!-- Header -->
        <div class="flex items-start justify-between gap-2">
          <div>
            <h4 class="font-medium text-gray-900 dark:text-white">{{ deadline.title }}</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ formattedDate }}</p>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <span
              v-if="urgencyConfig.label"
              :class="urgencyConfig.badgeClass"
              class="text-xs font-medium px-2 py-0.5 rounded-full"
            >
              {{ urgencyConfig.label }}
            </span>
            <span
              class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400"
            >
              {{ deadline.deadline_type_label }}
            </span>
          </div>
        </div>

        <!-- Description -->
        <p v-if="deadline.description" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          {{ deadline.description }}
        </p>

        <!-- Days countdown -->
        <p v-if="daysText" class="mt-2 text-sm font-medium" :class="urgencyConfig.iconClass">
          {{ daysText }}
        </p>

        <!-- Recurring badge -->
        <div
          v-if="deadline.is_recurring"
          class="mt-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-100 dark:bg-gray-700/50 text-xs text-gray-600 dark:text-gray-400"
        >
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
          <span>{{ deadline.recurrence_pattern || 'Recurring' }}</span>
        </div>

        <!-- Reminder status -->
        <div
          v-if="hasReminder"
          class="mt-4 inline-flex items-center gap-1.5 text-sm text-green-600 dark:text-green-400 font-medium"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M5 13l4 4L19 7"
            />
          </svg>
          Reminder Set
        </div>

        <!-- Set Reminder button -->
        <button
          v-else-if="canSetReminder"
          type="button"
          class="mt-4 inline-flex items-center gap-1.5 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium"
          @click="handleSetReminder"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            />
          </svg>
          Set Reminder
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import type { Reminder } from '@/types'

interface Props {
  reminder: Reminder
}

const props = defineProps<Props>()

const emit = defineEmits<{
  cancel: [id: string]
  delete: [id: string]
}>()

interface StatusConfig {
  label: string
  bgClass: string
  textClass: string
}

const defaultStatusConfig: StatusConfig = {
  label: 'Pending',
  bgClass: 'bg-yellow-100 dark:bg-yellow-900/30',
  textClass: 'text-yellow-800 dark:text-yellow-300',
}

const statusConfigs: Record<string, StatusConfig> = {
  pending: defaultStatusConfig,
  sent: {
    label: 'Sent',
    bgClass: 'bg-green-100 dark:bg-green-900/30',
    textClass: 'text-green-800 dark:text-green-300',
  },
  failed: {
    label: 'Failed',
    bgClass: 'bg-red-100 dark:bg-red-900/30',
    textClass: 'text-red-800 dark:text-red-300',
  },
  cancelled: {
    label: 'Cancelled',
    bgClass: 'bg-gray-100 dark:bg-gray-700',
    textClass: 'text-gray-600 dark:text-gray-400',
  },
}

const statusConfig = computed((): StatusConfig => {
  return statusConfigs[props.reminder.status] ?? defaultStatusConfig
})

const formattedRemindAt = computed(() => {
  const date = new Date(props.reminder.remind_at)
  return date.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
})

const formattedDeadlineDate = computed(() => {
  if (!props.reminder.deadline?.deadline_date) return null
  const date = new Date(props.reminder.deadline.deadline_date)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
})

function handleCancel(): void {
  emit('cancel', props.reminder.id)
}

function handleDelete(): void {
  emit('delete', props.reminder.id)
}
</script>

<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-indigo-500 dark:hover:border-indigo-400 transition-colors"
  >
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1 min-w-0">
        <!-- Title -->
        <h4 class="font-medium text-gray-900 dark:text-white truncate">
          {{ reminder.title }}
        </h4>

        <!-- Contract link -->
        <RouterLink
          v-if="reminder.contract"
          :to="`/contracts/${reminder.contract.id}`"
          class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline truncate block mt-1"
        >
          {{ reminder.contract.title }}
        </RouterLink>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-1 flex-shrink-0">
        <button
          v-if="reminder.status === 'pending'"
          type="button"
          class="p-2 text-gray-400 hover:text-orange-500 dark:hover:text-orange-400 transition-colors rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
          title="Cancel reminder"
          aria-label="Cancel reminder"
          @click="handleCancel"
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
              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
            />
          </svg>
        </button>
        <button
          type="button"
          class="p-2 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
          title="Delete reminder"
          aria-label="Delete reminder"
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
    </div>

    <!-- Meta info -->
    <div class="mt-3 flex flex-wrap items-center gap-2">
      <!-- Status badge -->
      <span
        :class="[statusConfig.bgClass, statusConfig.textClass]"
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
      >
        {{ statusConfig.label }}
      </span>

      <!-- Days before -->
      <span
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300"
      >
        {{ reminder.days_before }} days before
      </span>
    </div>

    <!-- Reminder and deadline dates -->
    <div class="mt-3 space-y-1 text-sm text-gray-500 dark:text-gray-400">
      <div class="flex items-center gap-2">
        <svg
          class="h-4 w-4"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
          />
        </svg>
        <span>Remind: {{ formattedRemindAt }}</span>
      </div>
      <div v-if="reminder.deadline" class="flex items-center gap-2">
        <svg
          class="h-4 w-4"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
          />
        </svg>
        <span>
          Deadline: {{ reminder.deadline.title }}
          <span v-if="formattedDeadlineDate">({{ formattedDeadlineDate }})</span>
        </span>
      </div>
    </div>

    <!-- Sent timestamp -->
    <p v-if="reminder.sent_at" class="mt-2 text-xs text-gray-400 dark:text-gray-500">
      Sent: {{ new Date(reminder.sent_at).toLocaleString() }}
    </p>
  </div>
</template>

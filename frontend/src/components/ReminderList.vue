<script setup lang="ts">
import { computed } from 'vue'
import type { Reminder } from '@/types'
import ReminderCard from './ReminderCard.vue'

interface PaginationMeta {
  currentPage: number
  lastPage: number
  total: number
}

interface Props {
  reminders: Reminder[]
  loading?: boolean
  pagination?: PaginationMeta
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

const emit = defineEmits<{
  cancel: [id: string]
  delete: [id: string]
  'page-change': [page: number]
}>()

const hasReminders = computed(() => props.reminders.length > 0)

function handleCancel(id: string): void {
  emit('cancel', id)
}

function handleDelete(id: string): void {
  emit('delete', id)
}

function handlePageChange(page: number): void {
  emit('page-change', page)
}
</script>

<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="py-12 text-center">
      <svg
        class="animate-spin h-8 w-8 text-indigo-600 dark:text-indigo-400 mx-auto"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        />
      </svg>
      <p class="mt-2 text-gray-500 dark:text-gray-400">Loading reminders...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!hasReminders" class="text-center py-12 text-gray-500 dark:text-gray-400">
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
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
        />
      </svg>
      <p>No reminders set up yet.</p>
      <p class="text-sm">Create a reminder from a contract deadline to get notified.</p>
    </div>

    <!-- Reminder List -->
    <div v-else class="space-y-3">
      <ReminderCard
        v-for="reminder in reminders"
        :key="reminder.id"
        :reminder="reminder"
        @cancel="handleCancel"
        @delete="handleDelete"
      />

      <!-- Pagination -->
      <div
        v-if="pagination && pagination.lastPage > 1"
        class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700"
      >
        <div class="text-sm text-gray-500 dark:text-gray-400">
          Page {{ pagination.currentPage }} of {{ pagination.lastPage }}
          <span class="ml-2">({{ pagination.total }} total)</span>
        </div>
        <div class="flex gap-2">
          <button
            type="button"
            class="btn-secondary text-sm"
            :disabled="pagination.currentPage <= 1"
            @click="handlePageChange(pagination.currentPage - 1)"
          >
            Previous
          </button>
          <button
            type="button"
            class="btn-secondary text-sm"
            :disabled="pagination.currentPage >= pagination.lastPage"
            @click="handlePageChange(pagination.currentPage + 1)"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

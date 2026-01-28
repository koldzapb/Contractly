<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { ContractDeadline, CreateReminderData, UpdateReminderData, Reminder } from '@/types'

interface Props {
  deadline?: ContractDeadline
  reminder?: Reminder
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

const emit = defineEmits<{
  submit: [data: CreateReminderData | UpdateReminderData]
  cancel: []
}>()

const daysBefore = ref(props.reminder?.days_before || 7)
const title = ref(props.reminder?.title || '')
const deadlineDate = ref(props.deadline?.deadline_date?.split('T')[0] || '')

const isEditMode = computed(() => !!props.reminder)
const needsDate = computed(() => !props.deadline?.deadline_date && !isEditMode.value)

const daysOptions = [1, 3, 7, 14, 30, 60, 90]

// Get minimum date (today)
const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

const formattedDeadlineDate = computed(() => {
  const dateStr = props.deadline?.deadline_date || deadlineDate.value
  if (!dateStr) return null
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
    year: 'numeric',
  })
})

const estimatedRemindDate = computed(() => {
  const dateStr = props.deadline?.deadline_date || deadlineDate.value
  if (!dateStr) return null
  const targetDate = new Date(dateStr)
  const remindDate = new Date(targetDate)
  remindDate.setDate(remindDate.getDate() - daysBefore.value)
  return remindDate.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
  })
})

const isFormValid = computed(() => {
  if (daysBefore.value < 1 || daysBefore.value > 365) return false
  if (needsDate.value && !deadlineDate.value) return false
  return true
})

// Reset form when deadline changes
watch(
  () => props.deadline,
  () => {
    if (!isEditMode.value) {
      daysBefore.value = 7
      title.value = ''
      deadlineDate.value = props.deadline?.deadline_date?.split('T')[0] || ''
    }
  },
)

function handleSubmit(): void {
  if (isEditMode.value) {
    const data: UpdateReminderData = {}
    if (daysBefore.value !== props.reminder?.days_before) {
      data.days_before = daysBefore.value
    }
    if (title.value && title.value !== props.reminder?.title) {
      data.title = title.value
    }
    emit('submit', data)
  } else if (props.deadline) {
    const data: CreateReminderData = {
      contract_deadline_id: props.deadline.id,
      days_before: daysBefore.value,
    }
    if (title.value) {
      data.title = title.value
    }
    // Include deadline_date if user provided one
    if (needsDate.value && deadlineDate.value) {
      data.deadline_date = deadlineDate.value
    }
    emit('submit', data)
  }
}

function handleCancel(): void {
  emit('cancel')
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-4">
    <!-- Deadline info (for create mode) -->
    <div v-if="deadline && !isEditMode" class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
      <h4 class="font-medium text-gray-900 dark:text-white">{{ deadline.title }}</h4>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        {{ deadline.deadline_type_label }}
      </p>
      <p
        v-if="formattedDeadlineDate && !needsDate"
        class="text-sm text-gray-600 dark:text-gray-300 mt-2"
      >
        Due: {{ formattedDeadlineDate }}
      </p>
      <p v-if="needsDate" class="text-sm text-amber-600 dark:text-amber-400 mt-2">
        This deadline doesn't have a specific date. Please enter one below.
      </p>
    </div>

    <!-- Deadline date input (when no date exists) -->
    <div v-if="needsDate">
      <label
        for="deadline-date"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      >
        When is this deadline? <span class="text-red-500">*</span>
      </label>
      <input
        id="deadline-date"
        v-model="deadlineDate"
        type="date"
        :min="minDate"
        class="input w-full"
        required
      />
      <p v-if="formattedDeadlineDate" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
        {{ formattedDeadlineDate }}
      </p>
    </div>

    <!-- Days before selection -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Remind me
      </label>
      <div class="grid grid-cols-4 gap-2">
        <button
          v-for="days in daysOptions"
          :key="days"
          type="button"
          :class="[
            'px-3 py-2 text-sm rounded-md border transition-colors',
            daysBefore === days
              ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300'
              : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-indigo-400',
          ]"
          @click="daysBefore = days"
        >
          {{ days }} day{{ days !== 1 ? 's' : '' }}
        </button>
      </div>
      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">before the deadline</p>
    </div>

    <!-- Custom days input -->
    <div>
      <label
        for="custom-days"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      >
        Or enter custom days
      </label>
      <input
        id="custom-days"
        v-model.number="daysBefore"
        type="number"
        min="1"
        max="365"
        class="input w-full"
        placeholder="Enter number of days"
      />
    </div>

    <!-- Estimated remind date -->
    <div v-if="estimatedRemindDate" class="text-sm text-gray-600 dark:text-gray-400">
      You will be reminded on <span class="font-medium">{{ estimatedRemindDate }}</span>
    </div>

    <!-- Custom title (optional) -->
    <div>
      <label
        for="reminder-title"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      >
        Custom title (optional)
      </label>
      <input
        id="reminder-title"
        v-model="title"
        type="text"
        class="input w-full"
        :placeholder="deadline ? `Reminder: ${deadline.title}` : 'Enter reminder title'"
        maxlength="255"
      />
      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
        Leave empty to use the default title
      </p>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-2">
      <button type="button" class="btn-secondary" :disabled="loading" @click="handleCancel">
        Cancel
      </button>
      <button type="submit" class="btn-primary" :disabled="loading || !isFormValid">
        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
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
        {{ isEditMode ? 'Update' : 'Create' }} Reminder
      </button>
    </div>
  </form>
</template>

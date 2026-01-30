<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useNotificationsStore } from '@/stores/notifications'

const notificationsStore = useNotificationsStore()
const { preferences, loading, saving, error } = storeToRefs(notificationsStore)

const showSuccess = ref(false)

onMounted(async () => {
  await notificationsStore.fetchPreferences()
})

async function handleToggle(
  field: 'analysis_complete' | 'deadline_reminder' | 'weekly_digest' | 'contract_expiring',
): Promise<void> {
  if (!preferences.value) return

  try {
    await notificationsStore.updatePreferences({
      [field]: !preferences.value[field],
    })
    showSuccessMessage()
  } catch {
    // Error is handled by the store
  }
}

async function handleDaysBeforeChange(event: Event): Promise<void> {
  const target = event.target as HTMLSelectElement
  const days = parseInt(target.value, 10)

  if (isNaN(days)) return

  try {
    await notificationsStore.updatePreferences({
      deadline_days_before: days,
    })
    showSuccessMessage()
  } catch {
    // Error is handled by the store
  }
}

async function handleReset(): Promise<void> {
  if (!confirm('Reset all notification preferences to defaults?')) return

  try {
    await notificationsStore.resetPreferences()
    showSuccessMessage()
  } catch {
    // Error is handled by the store
  }
}

function showSuccessMessage(): void {
  showSuccess.value = true
  setTimeout(() => {
    showSuccess.value = false
  }, 2000)
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white">Email Notifications</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Choose which email notifications you want to receive.
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-8">
      <svg
        class="animate-spin h-8 w-8 text-indigo-600"
        xmlns="http://www.w3.org/2000/svg"
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
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800"
    >
      <div class="flex">
        <svg
          class="h-5 w-5 text-red-400"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
            clip-rule="evenodd"
          />
        </svg>
        <div class="ml-3">
          <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Settings -->
    <div v-else-if="preferences" class="space-y-4">
      <!-- Success Message -->
      <div
        v-if="showSuccess"
        class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800"
      >
        <div class="flex">
          <svg
            class="h-5 w-5 text-green-400"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
              clip-rule="evenodd"
            />
          </svg>
          <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-200">
            Preferences saved
          </p>
        </div>
      </div>

      <!-- Notification Options -->
      <div class="divide-y divide-gray-200 dark:divide-gray-700">
        <!-- Analysis Complete -->
        <div class="py-4 flex items-center justify-between">
          <div class="flex-1">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Analysis Complete</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Receive an email when your contract analysis is finished.
            </p>
          </div>
          <button
            type="button"
            :class="[
              preferences.analysis_complete ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700',
              'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
            ]"
            role="switch"
            :aria-checked="preferences.analysis_complete"
            :disabled="saving"
            @click="handleToggle('analysis_complete')"
          >
            <span class="sr-only">Toggle analysis complete notifications</span>
            <span
              :class="[
                preferences.analysis_complete ? 'translate-x-5' : 'translate-x-0',
                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
              ]"
            />
          </button>
        </div>

        <!-- Deadline Reminders -->
        <div class="py-4 space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white">Deadline Reminders</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Receive reminders before important deadlines.
              </p>
            </div>
            <button
              type="button"
              :class="[
                preferences.deadline_reminder ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700',
                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
              ]"
              role="switch"
              :aria-checked="preferences.deadline_reminder"
              :disabled="saving"
              @click="handleToggle('deadline_reminder')"
            >
              <span class="sr-only">Toggle deadline reminder notifications</span>
              <span
                :class="[
                  preferences.deadline_reminder ? 'translate-x-5' : 'translate-x-0',
                  'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                ]"
              />
            </button>
          </div>

          <!-- Days Before Select -->
          <div v-if="preferences.deadline_reminder" class="pl-0 sm:pl-4">
            <label for="days-before" class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
              Remind me
            </label>
            <select
              id="days-before"
              :value="preferences.deadline_days_before"
              :disabled="saving"
              class="block w-full sm:w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 pl-3 pr-10 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
              @change="handleDaysBeforeChange"
            >
              <option :value="1">1 day before</option>
              <option :value="3">3 days before</option>
              <option :value="7">7 days before</option>
              <option :value="14">14 days before</option>
              <option :value="30">30 days before</option>
            </select>
          </div>
        </div>

        <!-- Weekly Digest -->
        <div class="py-4 flex items-center justify-between">
          <div class="flex-1">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Weekly Digest</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Receive a weekly summary of your contracts and deadlines (Monday 9 AM).
            </p>
          </div>
          <button
            type="button"
            :class="[
              preferences.weekly_digest ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700',
              'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
            ]"
            role="switch"
            :aria-checked="preferences.weekly_digest"
            :disabled="saving"
            @click="handleToggle('weekly_digest')"
          >
            <span class="sr-only">Toggle weekly digest notifications</span>
            <span
              :class="[
                preferences.weekly_digest ? 'translate-x-5' : 'translate-x-0',
                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
              ]"
            />
          </button>
        </div>

        <!-- Contract Expiring -->
        <div class="py-4 flex items-center justify-between">
          <div class="flex-1">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Contract Expiring</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Receive notifications when contracts are about to expire.
            </p>
          </div>
          <button
            type="button"
            :class="[
              preferences.contract_expiring ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700',
              'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
            ]"
            role="switch"
            :aria-checked="preferences.contract_expiring"
            :disabled="saving"
            @click="handleToggle('contract_expiring')"
          >
            <span class="sr-only">Toggle contract expiring notifications</span>
            <span
              :class="[
                preferences.contract_expiring ? 'translate-x-5' : 'translate-x-0',
                'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
              ]"
            />
          </button>
        </div>
      </div>

      <!-- Reset Button -->
      <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
        <button
          type="button"
          :disabled="saving"
          class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-none focus:underline disabled:opacity-50"
          @click="handleReset"
        >
          Reset to defaults
        </button>
      </div>
    </div>
  </div>
</template>

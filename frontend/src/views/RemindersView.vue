<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'
import ThemeToggle from '@/components/ThemeToggle.vue'
import ReminderList from '@/components/ReminderList.vue'
import { useRemindersStore } from '@/stores/reminders'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const remindersStore = useRemindersStore()
const authStore = useAuthStore()

const { reminders, loading, error, pagination, statusFilter } = storeToRefs(remindersStore)

const showDeleteConfirm = ref(false)
const showCancelConfirm = ref(false)
const reminderToDelete = ref<string | null>(null)
const reminderToCancel = ref<string | null>(null)

const filterOptions = [
  { value: undefined, label: 'All' },
  { value: 'pending', label: 'Pending' },
  { value: 'sent', label: 'Sent' },
  { value: 'cancelled', label: 'Cancelled' },
]

const currentFilter = computed(() => statusFilter.value)

onMounted(async () => {
  await remindersStore.fetchReminders()
})

async function handleFilterChange(status: string | undefined): Promise<void> {
  remindersStore.setStatusFilter(status)
  await remindersStore.fetchReminders({ page: 1, status })
}

function handleDeleteRequest(id: string): void {
  reminderToDelete.value = id
  showDeleteConfirm.value = true
}

function handleCancelRequest(id: string): void {
  reminderToCancel.value = id
  showCancelConfirm.value = true
}

async function confirmDelete(): Promise<void> {
  if (reminderToDelete.value) {
    try {
      await remindersStore.deleteReminder(reminderToDelete.value)
    } catch {
      // Error is handled by the store
    }
  }
  showDeleteConfirm.value = false
  reminderToDelete.value = null
}

async function confirmCancel(): Promise<void> {
  if (reminderToCancel.value) {
    try {
      await remindersStore.cancelReminder(reminderToCancel.value)
    } catch {
      // Error is handled by the store
    }
  }
  showCancelConfirm.value = false
  reminderToCancel.value = null
}

function closeDeleteModal(): void {
  showDeleteConfirm.value = false
  reminderToDelete.value = null
}

function closeCancelModal(): void {
  showCancelConfirm.value = false
  reminderToCancel.value = null
}

async function handlePageChange(page: number): Promise<void> {
  await remindersStore.goToPage(page)
}

async function handleLogout(): Promise<void> {
  try {
    await authStore.logout()
    await router.push({ name: 'login' })
  } catch {
    // Error handled by store
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-8">
          <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">Contractly</span>
          <nav class="hidden md:flex items-center space-x-6">
            <RouterLink
              to="/dashboard"
              class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
            >
              Dashboard
            </RouterLink>
            <RouterLink
              to="/contracts"
              class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
            >
              Contracts
            </RouterLink>
            <RouterLink
              to="/reminders"
              class="text-gray-900 dark:text-white font-medium"
              active-class="text-indigo-600 dark:text-indigo-400"
            >
              Reminders
            </RouterLink>
          </nav>
        </div>
        <div class="flex items-center gap-4">
          <ThemeToggle />
          <span class="text-sm text-gray-600 dark:text-gray-300">{{ authStore.user?.name }}</span>
          <button
            type="button"
            class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
            @click="handleLogout"
          >
            <ArrowRightOnRectangleIcon class="h-5 w-5" />
            <span class="hidden sm:inline">Logout</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reminders</h1>
        <p class="text-gray-600 dark:text-gray-400">
          Manage your deadline reminders. Create reminders from contract deadlines to get notified.
        </p>
      </div>

      <!-- Error Banner -->
      <div
        v-if="error"
        class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
      >
        <div class="flex items-center">
          <svg
            class="h-5 w-5 text-red-400 mr-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
          <button
            type="button"
            class="ml-auto text-red-400 hover:text-red-500"
            @click="remindersStore.clearError"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="-mb-px flex space-x-8">
            <button
              v-for="option in filterOptions"
              :key="option.value ?? 'all'"
              type="button"
              :class="[
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                currentFilter === option.value
                  ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
              ]"
              @click="handleFilterChange(option.value)"
            >
              {{ option.label }}
            </button>
          </nav>
        </div>
      </div>

      <!-- Reminder List -->
      <div class="card">
        <ReminderList
          :reminders="reminders"
          :loading="loading"
          :pagination="pagination"
          @cancel="handleCancelRequest"
          @delete="handleDeleteRequest"
          @page-change="handlePageChange"
        />
      </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div
        v-if="showDeleteConfirm"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="delete-modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div
          class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
        >
          <div
            class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"
            aria-hidden="true"
            @click="closeDeleteModal"
          />
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"
            >&#8203;</span
          >
          <div
            class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
          >
            <div class="sm:flex sm:items-start">
              <div
                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10"
              >
                <svg
                  class="h-6 w-6 text-red-600 dark:text-red-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3
                  id="delete-modal-title"
                  class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                >
                  Delete Reminder
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Are you sure you want to delete this reminder? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
              <button type="button" class="btn-danger" @click="confirmDelete">Delete</button>
              <button type="button" class="btn-secondary" @click="closeDeleteModal">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Cancel Confirmation Modal -->
    <Teleport to="body">
      <div
        v-if="showCancelConfirm"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="cancel-modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div
          class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
        >
          <div
            class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"
            aria-hidden="true"
            @click="closeCancelModal"
          />
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"
            >&#8203;</span
          >
          <div
            class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
          >
            <div class="sm:flex sm:items-start">
              <div
                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 dark:bg-orange-900/30 sm:mx-0 sm:h-10 sm:w-10"
              >
                <svg
                  class="h-6 w-6 text-orange-600 dark:text-orange-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                  />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3
                  id="cancel-modal-title"
                  class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                >
                  Cancel Reminder
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Are you sure you want to cancel this reminder? You will not receive a
                    notification for this deadline.
                  </p>
                </div>
              </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
              <button
                type="button"
                class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:text-sm"
                @click="confirmCancel"
              >
                Cancel Reminder
              </button>
              <button type="button" class="btn-secondary" @click="closeCancelModal">Go Back</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

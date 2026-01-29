<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useContractsStore } from '@/stores/contracts'
import { useAuthStore } from '@/stores/auth'
import { useRemindersStore } from '@/stores/reminders'
import ThemeToggle from '@/components/ThemeToggle.vue'
import MobileNav from '@/components/MobileNav.vue'
import RiskBadge from '@/components/RiskBadge.vue'
import AnalysisProgress from '@/components/AnalysisProgress.vue'
import AnalysisSummary from '@/components/AnalysisSummary.vue'
import ClauseList from '@/components/ClauseList.vue'
import DeadlineList from '@/components/DeadlineList.vue'
import ReminderForm from '@/components/ReminderForm.vue'
import { ContractChat } from '@/components/chat'
import type { ContractDeadline, CreateReminderData } from '@/types'

const route = useRoute()
const router = useRouter()
const contractsStore = useContractsStore()
const authStore = useAuthStore()
const remindersStore = useRemindersStore()

const { currentContract, loading, error } = storeToRefs(contractsStore)
const { user } = storeToRefs(authStore)

const contractId = computed(() => route.params.id as string)
const pollingInterval = ref<ReturnType<typeof setInterval> | null>(null)

// Reminder modal state
const showReminderModal = ref(false)
const selectedDeadline = ref<ContractDeadline | null>(null)
const creatingReminder = ref(false)
const reminderError = ref<string | null>(null)

// Chat panel state - persisted in localStorage
const CHAT_PANEL_STORAGE_KEY = 'contractly-chat-panel-visible'

function getStoredChatPanelState(): boolean {
  const stored = localStorage.getItem(CHAT_PANEL_STORAGE_KEY)
  return stored === 'true'
}

const showChatPanel = ref(getStoredChatPanelState())

function toggleChatPanel(): void {
  showChatPanel.value = !showChatPanel.value
  localStorage.setItem(CHAT_PANEL_STORAGE_KEY, String(showChatPanel.value))
}

const isAnalyzing = computed(() => {
  return (
    currentContract.value?.status === 'pending' || currentContract.value?.status === 'processing'
  )
})

const isCompleted = computed(() => currentContract.value?.status === 'completed')
const isFailed = computed(() => currentContract.value?.status === 'failed')

const statusConfig = computed(() => {
  if (!currentContract.value) return null

  const configs = {
    pending: {
      label: 'Pending',
      bgClass: 'bg-yellow-100 dark:bg-yellow-900/30',
      textClass: 'text-yellow-800 dark:text-yellow-400',
    },
    processing: {
      label: 'Processing',
      bgClass: 'bg-blue-100 dark:bg-blue-900/30',
      textClass: 'text-blue-800 dark:text-blue-400',
    },
    completed: {
      label: 'Analyzed',
      bgClass: 'bg-green-100 dark:bg-green-900/30',
      textClass: 'text-green-800 dark:text-green-400',
    },
    failed: {
      label: 'Failed',
      bgClass: 'bg-red-100 dark:bg-red-900/30',
      textClass: 'text-red-800 dark:text-red-400',
    },
  }

  return configs[currentContract.value.status]
})

const formattedDate = computed(() => {
  if (!currentContract.value) return ''
  const date = new Date(currentContract.value.created_at)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

async function fetchContract(): Promise<void> {
  try {
    await contractsStore.fetchContract(contractId.value, true)
  } catch {
    // Error is handled by the store
  }
}

async function pollStatus(): Promise<void> {
  if (!isAnalyzing.value) {
    stopPolling()
    return
  }

  try {
    await contractsStore.pollContractStatus(contractId.value)

    // If status changed to completed, fetch full contract with analysis
    if (currentContract.value?.status === 'completed') {
      await fetchContract()
      stopPolling()
    }
  } catch {
    // Silently ignore polling errors
  }
}

function startPolling(): void {
  if (pollingInterval.value) return
  pollingInterval.value = setInterval(pollStatus, 3000) // Poll every 3 seconds
}

function stopPolling(): void {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value)
    pollingInterval.value = null
  }
}

async function handleRetryAnalysis(): Promise<void> {
  if (!currentContract.value) return

  try {
    await contractsStore.retryAnalysis(currentContract.value.id)
    // Start polling after retry
    startPolling()
  } catch {
    // Error is handled by the store
  }
}

async function handleLogout(): Promise<void> {
  await authStore.logout()
  router.push('/login')
}

async function handleDelete(): Promise<void> {
  if (!currentContract.value) return

  if (confirm('Are you sure you want to delete this contract?')) {
    try {
      await contractsStore.deleteContract(currentContract.value.id)
      router.push('/contracts')
    } catch {
      // Error is handled by the store
    }
  }
}

function handleSetReminder(deadline: ContractDeadline): void {
  selectedDeadline.value = deadline
  reminderError.value = null
  showReminderModal.value = true
}

async function handleCreateReminder(data: CreateReminderData): Promise<void> {
  creatingReminder.value = true
  reminderError.value = null

  try {
    await remindersStore.createReminder(data)
    showReminderModal.value = false
    selectedDeadline.value = null
    // Refresh contract to update deadline reminder status
    await fetchContract()
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    reminderError.value = err.response?.data?.message ?? 'Failed to create reminder'
  } finally {
    creatingReminder.value = false
  }
}

function closeReminderModal(): void {
  showReminderModal.value = false
  selectedDeadline.value = null
  reminderError.value = null
}

// Watch for status changes to start/stop polling
watch(
  () => currentContract.value?.status,
  (status) => {
    if (status === 'pending' || status === 'processing') {
      startPolling()
    } else {
      stopPolling()
    }
  },
)

onMounted(async () => {
  await fetchContract()
  if (isAnalyzing.value) {
    startPolling()
  }
})

onUnmounted(() => {
  stopPolling()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Skip Link for Accessibility -->
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-indigo-600 focus:text-white focus:rounded-lg focus:outline-none"
    >
      Skip to main content
    </a>

    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm">
      <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 sm:h-16 flex items-center justify-between"
      >
        <div class="flex items-center gap-4 md:gap-8">
          <MobileNav />
          <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">Contractly</span>
          <nav class="hidden md:flex items-center space-x-6" aria-label="Main navigation">
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
              class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
            >
              Reminders
            </RouterLink>
          </nav>
        </div>
        <div class="flex items-center gap-2 sm:gap-4">
          <ThemeToggle />
          <span v-if="user" class="hidden sm:inline text-sm text-gray-600 dark:text-gray-300">
            {{ user.name }}
          </span>
          <button
            type="button"
            class="btn-secondary text-sm"
            aria-label="Logout"
            @click="handleLogout"
          >
            Logout
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Breadcrumb -->
      <nav class="mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
          <li>
            <RouterLink to="/contracts" class="hover:text-gray-700 dark:hover:text-gray-300">
              Contracts
            </RouterLink>
          </li>
          <li aria-hidden="true">/</li>
          <li
            class="text-gray-900 dark:text-white truncate max-w-[60vw] sm:max-w-xs"
            aria-current="page"
          >
            {{ currentContract?.title || 'Loading...' }}
          </li>
        </ol>
      </nav>

      <!-- Loading State -->
      <div
        v-if="loading && !currentContract"
        class="text-center py-12"
        aria-live="polite"
        aria-busy="true"
      >
        <div class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400">
          <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
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
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
            />
          </svg>
          <span>Loading contract...</span>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error && !currentContract" class="text-center py-12">
        <div class="inline-flex flex-col items-center gap-4 text-gray-500 dark:text-gray-400">
          <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          <p>{{ error }}</p>
          <RouterLink to="/contracts" class="btn-primary">Back to Contracts</RouterLink>
        </div>
      </div>

      <!-- Contract Content -->
      <div v-else-if="currentContract">
        <!-- Contract Header -->
        <div class="card mb-6">
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ currentContract.title }}
                </h1>
                <span
                  v-if="statusConfig"
                  :class="[statusConfig.bgClass, statusConfig.textClass]"
                  class="px-3 py-1 text-sm font-medium rounded-full"
                >
                  {{ statusConfig.label }}
                </span>
                <RiskBadge
                  v-if="currentContract.overall_risk_level"
                  :level="currentContract.overall_risk_level"
                />
              </div>
              <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">
                {{ currentContract.original_filename }}
                <span class="mx-2">-</span>
                {{ currentContract.file_size_human }}
                <span v-if="currentContract.page_count" class="mx-2">-</span>
                <span v-if="currentContract.page_count">
                  {{ currentContract.page_count }} pages
                </span>
              </p>
              <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">
                Uploaded {{ formattedDate }}
              </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 flex-shrink-0 ml-4">
              <button
                v-if="isCompleted"
                type="button"
                class="btn-secondary text-sm flex items-center gap-2"
                :class="{ 'bg-indigo-100 dark:bg-indigo-900/30': showChatPanel }"
                @click="toggleChatPanel"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                  />
                </svg>
                {{ showChatPanel ? 'Hide Chat' : 'Ask AI' }}
              </button>
              <button
                type="button"
                class="btn-secondary text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                @click="handleDelete"
              >
                Delete
              </button>
            </div>
          </div>
        </div>

        <!-- Analysis Progress (for pending/processing/failed) -->
        <AnalysisProgress
          v-if="isAnalyzing || isFailed"
          :status="currentContract.status"
          :error-message="currentContract.error_message"
          class="mb-6"
          @retry="handleRetryAnalysis"
        />

        <!-- Analysis Results (for completed) -->
        <template v-if="isCompleted && currentContract.analysis">
          <div class="flex gap-6">
            <!-- Main content area -->
            <div :class="[showChatPanel ? 'flex-1 min-w-0' : 'w-full']">
              <!-- Summary -->
              <AnalysisSummary :analysis="currentContract.analysis" class="mb-6" />

              <!-- Two Column Layout for Clauses and Deadlines -->
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Clauses -->
                <ClauseList :clauses="currentContract.analysis.clauses || []" />

                <!-- Deadlines -->
                <DeadlineList
                  :deadlines="currentContract.analysis.deadlines || []"
                  @set-reminder="handleSetReminder"
                />
              </div>
            </div>

            <!-- Chat Panel -->
            <div v-if="showChatPanel" class="w-96 flex-shrink-0 hidden lg:block">
              <div class="sticky top-4 h-[calc(100vh-8rem)]">
                <ContractChat :contract-id="contractId" :contract-completed="isCompleted" />
              </div>
            </div>
          </div>

          <!-- Mobile Chat Panel (full width below content) -->
          <div v-if="showChatPanel" class="mt-6 lg:hidden h-[500px]">
            <ContractChat :contract-id="contractId" :contract-completed="isCompleted" />
          </div>
        </template>

        <!-- No Analysis Yet (shouldn't normally show, but fallback) -->
        <div
          v-else-if="isCompleted && !currentContract.analysis"
          class="card text-center py-12 text-gray-500 dark:text-gray-400"
        >
          <p>Analysis data not available. Please try refreshing the page.</p>
          <button type="button" class="btn-primary mt-4" @click="fetchContract">Refresh</button>
        </div>
      </div>
    </main>

    <!-- Set Reminder Modal -->
    <Teleport to="body">
      <div
        v-if="showReminderModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="reminder-modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div
          class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
        >
          <div
            class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"
            aria-hidden="true"
            @click="closeReminderModal"
          />
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true"
            >&#8203;</span
          >
          <div
            class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
          >
            <div class="mb-4">
              <h3
                id="reminder-modal-title"
                class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
              >
                Set Reminder
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Get notified before this deadline.
              </p>
            </div>

            <!-- Error message -->
            <div
              v-if="reminderError"
              class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
            >
              <p class="text-sm text-red-600 dark:text-red-400">{{ reminderError }}</p>
            </div>

            <ReminderForm
              v-if="selectedDeadline"
              :deadline="selectedDeadline"
              :loading="creatingReminder"
              @submit="handleCreateReminder"
              @cancel="closeReminderModal"
            />
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

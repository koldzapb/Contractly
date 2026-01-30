<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'
import ThemeToggle from '@/components/ThemeToggle.vue'
import MobileNav from '@/components/MobileNav.vue'
import ContractSelector from '@/components/comparison/ContractSelector.vue'
import ComparisonSummary from '@/components/comparison/ComparisonSummary.vue'
import ClauseComparisonTable from '@/components/comparison/ClauseComparisonTable.vue'
import DeadlineComparisonTable from '@/components/comparison/DeadlineComparisonTable.vue'
import { useComparisonStore } from '@/stores/comparison'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const comparisonStore = useComparisonStore()
const authStore = useAuthStore()

const {
  completedContracts,
  contractIdA,
  contractIdB,
  result,
  loading,
  loadingContracts,
  error,
  canCompare,
  hasResult,
} = storeToRefs(comparisonStore)

const activeTab = ref<'clauses' | 'deadlines'>('clauses')

onMounted(async () => {
  await comparisonStore.fetchAvailableContracts()
})

async function handleCompare(): Promise<void> {
  try {
    await comparisonStore.compare()
  } catch {
    // Error is handled by the store
  }
}

function handleSwap(): void {
  comparisonStore.swapContracts()
}

function handleReset(): void {
  comparisonStore.reset()
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
              to="/contracts/compare"
              class="text-gray-900 dark:text-white font-medium"
              active-class="text-indigo-600 dark:text-indigo-400"
              aria-current="page"
            >
              Compare
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
          <span class="hidden sm:inline text-sm text-gray-600 dark:text-gray-300">{{
            authStore.user?.name
          }}</span>
          <button
            type="button"
            class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            aria-label="Logout"
            @click="handleLogout"
          >
            <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
            <span class="hidden sm:inline">Logout</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Compare Contracts</h1>
        <p class="text-gray-600 dark:text-gray-400">
          Select two contracts to compare their clauses, risks, and deadlines.
        </p>
      </div>

      <!-- Error Banner -->
      <div
        v-if="error"
        role="alert"
        class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg"
      >
        <div class="flex items-center">
          <svg
            class="h-5 w-5 text-red-400 mr-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
        </div>
      </div>

      <!-- Contract Selection -->
      <div
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6"
      >
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
          Select Contracts to Compare
        </h2>

        <!-- Empty state if no contracts -->
        <div v-if="!loadingContracts && completedContracts.length < 2" class="text-center py-8">
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
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
            Not enough contracts
          </h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            You need at least two analyzed contracts to compare.
          </p>
          <div class="mt-6">
            <RouterLink
              to="/contracts"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Upload Contracts
            </RouterLink>
          </div>
        </div>

        <!-- Contract selectors -->
        <div v-else class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <ContractSelector
              v-model="contractIdA"
              :contracts="completedContracts"
              :exclude-id="contractIdB"
              :loading="loadingContracts"
              label="Contract A"
              @update:model-value="comparisonStore.selectContractA"
            />
            <ContractSelector
              v-model="contractIdB"
              :contracts="completedContracts"
              :exclude-id="contractIdA"
              :loading="loadingContracts"
              label="Contract B"
              @update:model-value="comparisonStore.selectContractB"
            />
          </div>

          <!-- Actions -->
          <div class="flex flex-wrap items-center gap-3">
            <button
              type="button"
              :disabled="!canCompare"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
              @click="handleCompare"
            >
              <svg
                v-if="loading"
                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
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
              {{ loading ? 'Comparing...' : 'Compare' }}
            </button>

            <button
              v-if="contractIdA && contractIdB"
              type="button"
              class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              @click="handleSwap"
            >
              <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                />
              </svg>
              Swap
            </button>

            <button
              v-if="hasResult"
              type="button"
              class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              @click="handleReset"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Comparison Results -->
      <div v-if="result" class="space-y-6">
        <!-- Summary -->
        <ComparisonSummary :result="result" />

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
          <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button
              type="button"
              :class="[
                activeTab === 'clauses'
                  ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
              @click="activeTab = 'clauses'"
            >
              Clauses
              <span
                class="ml-2 py-0.5 px-2 rounded-full text-xs"
                :class="
                  activeTab === 'clauses'
                    ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400'
                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                "
              >
                {{
                  result.stats.total_clauses_a +
                  result.stats.total_clauses_b -
                  result.stats.matched_clauses
                }}
              </span>
            </button>
            <button
              type="button"
              :class="[
                activeTab === 'deadlines'
                  ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
              @click="activeTab = 'deadlines'"
            >
              Deadlines
              <span
                class="ml-2 py-0.5 px-2 rounded-full text-xs"
                :class="
                  activeTab === 'deadlines'
                    ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400'
                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                "
              >
                {{
                  result.stats.total_deadlines_a +
                  result.stats.total_deadlines_b -
                  result.stats.matched_deadlines
                }}
              </span>
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div
          class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6"
        >
          <ClauseComparisonTable
            v-if="activeTab === 'clauses'"
            :matched="result.clauses.matched"
            :only-in-a="result.clauses.only_in_a"
            :only-in-b="result.clauses.only_in_b"
          />
          <DeadlineComparisonTable
            v-if="activeTab === 'deadlines'"
            :matched="result.deadlines.matched"
            :only-in-a="result.deadlines.only_in_a"
            :only-in-b="result.deadlines.only_in_b"
          />
        </div>
      </div>

      <!-- Empty state when no result -->
      <div
        v-else-if="completedContracts.length >= 2 && !loading"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-12 text-center"
      >
        <svg
          class="mx-auto h-16 w-16 text-gray-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
          />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Ready to Compare</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
          Select two contracts above and click "Compare" to see a side-by-side analysis of their
          clauses, risk levels, and deadlines.
        </p>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { storeToRefs } from 'pinia'
import ThemeToggle from '@/components/ThemeToggle.vue'
import ContractUploader from '@/components/ContractUploader.vue'
import ContractList from '@/components/ContractList.vue'
import { useContractsStore } from '@/stores/contracts'
import { useAuthStore } from '@/stores/auth'

const contractsStore = useContractsStore()
const authStore = useAuthStore()

const { contracts, loading, error, pagination } = storeToRefs(contractsStore)
const { user } = storeToRefs(authStore)

const showDeleteConfirm = ref(false)
const contractToDelete = ref<string | null>(null)

onMounted(async () => {
  await contractsStore.fetchContracts()
})

function handleDeleteRequest(id: string): void {
  contractToDelete.value = id
  showDeleteConfirm.value = true
}

async function confirmDelete(): Promise<void> {
  if (contractToDelete.value) {
    try {
      await contractsStore.deleteContract(contractToDelete.value)
    } catch {
      // Error is handled by the store
    }
  }
  showDeleteConfirm.value = false
  contractToDelete.value = null
}

function cancelDelete(): void {
  showDeleteConfirm.value = false
  contractToDelete.value = null
}

async function handlePageChange(page: number): Promise<void> {
  await contractsStore.goToPage(page)
}

async function handleLogout(): Promise<void> {
  await authStore.logout()
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
            <RouterLink to="/contracts" class="text-indigo-600 dark:text-indigo-400 font-medium">
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
        <div class="flex items-center gap-4">
          <ThemeToggle />
          <span v-if="user" class="text-sm text-gray-600 dark:text-gray-300">
            {{ user.name }}
          </span>
          <button type="button" class="btn-secondary text-sm" @click="handleLogout">Logout</button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Contracts</h1>
        <p class="text-gray-600 dark:text-gray-400">Upload and manage your contracts.</p>
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
            @click="contractsStore.clearError"
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

      <!-- Upload Area -->
      <div class="mb-8">
        <ContractUploader />
      </div>

      <!-- Contract List -->
      <ContractList
        :contracts="contracts"
        :loading="loading"
        :pagination="pagination"
        @delete="handleDeleteRequest"
        @page-change="handlePageChange"
      />
    </main>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div
        v-if="showDeleteConfirm"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div
          class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
        >
          <!-- Background overlay -->
          <div
            class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"
            aria-hidden="true"
            @click="cancelDelete"
          />

          <!-- Modal panel -->
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
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                  />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3
                  id="modal-title"
                  class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                >
                  Delete Contract
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Are you sure you want to delete this contract? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
              <button type="button" class="btn-danger" @click="confirmDelete">Delete</button>
              <button type="button" class="btn-secondary" @click="cancelDelete">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

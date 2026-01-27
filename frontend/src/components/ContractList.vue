<script setup lang="ts">
import { computed } from 'vue'
import type { Contract } from '@/types'
import ContractCard from './ContractCard.vue'

interface PaginationMeta {
  currentPage: number
  lastPage: number
  total: number
}

interface Props {
  contracts: Contract[]
  loading?: boolean
  pagination?: PaginationMeta
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

const emit = defineEmits<{
  delete: [id: string]
  'page-change': [page: number]
}>()

const hasContracts = computed(() => props.contracts.length > 0)

function handleDelete(id: string): void {
  emit('delete', id)
}

function handlePageChange(page: number): void {
  emit('page-change', page)
}
</script>

<template>
  <div class="card">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Your Contracts</h2>

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
      <p class="mt-2 text-gray-500 dark:text-gray-400">Loading contracts...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!hasContracts" class="text-center py-12 text-gray-500 dark:text-gray-400">
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
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        />
      </svg>
      <p>No contracts uploaded yet.</p>
      <p class="text-sm">Upload your first contract to get started.</p>
    </div>

    <!-- Contract List -->
    <div v-else class="space-y-3">
      <ContractCard
        v-for="contract in contracts"
        :key="contract.id"
        :contract="contract"
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

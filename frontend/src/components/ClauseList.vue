<script setup lang="ts">
import { ref, computed } from 'vue'
import type { ContractClause, RiskLevel } from '@/types'
import ClauseCard from './ClauseCard.vue'

interface Props {
  clauses: ContractClause[]
}

const props = defineProps<Props>()

const filterRisk = ref<RiskLevel | 'all'>('all')

const filteredClauses = computed(() => {
  if (filterRisk.value === 'all') {
    return props.clauses
  }
  return props.clauses.filter((c) => c.risk_level === filterRisk.value)
})

const riskCounts = computed(() => {
  return {
    all: props.clauses.length,
    high: props.clauses.filter((c) => c.risk_level === 'high').length,
    medium: props.clauses.filter((c) => c.risk_level === 'medium').length,
    low: props.clauses.filter((c) => c.risk_level === 'low').length,
  }
})

const sortedClauses = computed(() => {
  const riskOrder = { high: 0, medium: 1, low: 2 }
  return [...filteredClauses.value].sort((a, b) => {
    return riskOrder[a.risk_level] - riskOrder[b.risk_level]
  })
})
</script>

<template>
  <div class="card">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        Identified Clauses
        <span class="text-gray-400 dark:text-gray-500 font-normal">({{ clauses.length }})</span>
      </h2>

      <!-- Filter Tabs -->
      <div
        class="flex flex-wrap gap-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-1"
        role="group"
        aria-label="Filter clauses by risk level"
      >
        <button
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
          :class="
            filterRisk === 'all'
              ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
          "
          :aria-pressed="filterRisk === 'all'"
          @click="filterRisk = 'all'"
        >
          All ({{ riskCounts.all }})
        </button>
        <button
          v-if="riskCounts.high > 0"
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-red-500"
          :class="
            filterRisk === 'high'
              ? 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-400'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
          "
          :aria-pressed="filterRisk === 'high'"
          @click="filterRisk = 'high'"
        >
          High ({{ riskCounts.high }})
        </button>
        <button
          v-if="riskCounts.medium > 0"
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-yellow-500"
          :class="
            filterRisk === 'medium'
              ? 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-400'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
          "
          :aria-pressed="filterRisk === 'medium'"
          @click="filterRisk = 'medium'"
        >
          Medium ({{ riskCounts.medium }})
        </button>
        <button
          v-if="riskCounts.low > 0"
          type="button"
          class="px-3 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-green-500"
          :class="
            filterRisk === 'low'
              ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-400'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
          "
          :aria-pressed="filterRisk === 'low'"
          @click="filterRisk = 'low'"
        >
          Low ({{ riskCounts.low }})
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="clauses.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
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
      <p>No clauses identified yet.</p>
    </div>

    <!-- Filtered Empty State -->
    <div
      v-else-if="filteredClauses.length === 0"
      class="text-center py-8 text-gray-500 dark:text-gray-400"
    >
      <p>No {{ filterRisk }} risk clauses found.</p>
      <button
        type="button"
        class="text-indigo-600 dark:text-indigo-400 hover:underline mt-2"
        @click="filterRisk = 'all'"
      >
        Show all clauses
      </button>
    </div>

    <!-- Clause List -->
    <div v-else class="space-y-3">
      <ClauseCard v-for="clause in sortedClauses" :key="clause.id" :clause="clause" />
    </div>
  </div>
</template>

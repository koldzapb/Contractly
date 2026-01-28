<script setup lang="ts">
import { DocumentTextIcon, ClockIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import type { DashboardStats } from '@/types'

interface Props {
  stats: DashboardStats | null
  loading?: boolean
}

defineProps<Props>()
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Total Contracts -->
    <div class="card flex items-center gap-4">
      <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
        <DocumentTextIcon class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
      </div>
      <div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Contracts</p>
        <p v-if="loading" class="h-8 w-12 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></p>
        <p v-else class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ stats?.total_contracts ?? 0 }}
        </p>
      </div>
    </div>

    <!-- Pending Analysis -->
    <div class="card flex items-center gap-4">
      <div class="p-3 bg-yellow-100 dark:bg-yellow-900/50 rounded-lg">
        <ClockIcon class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
      </div>
      <div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Analysis</p>
        <p v-if="loading" class="h-8 w-12 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></p>
        <p v-else class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
          {{ (stats?.pending_analysis ?? 0) + (stats?.processing_analysis ?? 0) }}
        </p>
      </div>
    </div>

    <!-- High Risk Items -->
    <div class="card flex items-center gap-4">
      <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-lg">
        <ExclamationTriangleIcon class="h-6 w-6 text-red-600 dark:text-red-400" />
      </div>
      <div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">High Risk Items</p>
        <p v-if="loading" class="h-8 w-12 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></p>
        <p v-else class="text-2xl font-bold text-red-600 dark:text-red-400">
          {{ stats?.high_risk_clauses ?? 0 }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import type { DashboardContract } from '@/types'

interface Props {
  contracts: DashboardContract[]
  loading?: boolean
}

const props = defineProps<Props>()

const hasContracts = computed(() => props.contracts.length > 0)

interface BadgeConfig {
  bgClass: string
  textClass: string
  label: string
}

const defaultStatusConfig: BadgeConfig = {
  bgClass: 'bg-yellow-100 dark:bg-yellow-900/30',
  textClass: 'text-yellow-800 dark:text-yellow-300',
  label: 'Pending',
}

const statusConfigs: Record<string, BadgeConfig> = {
  pending: defaultStatusConfig,
  processing: {
    bgClass: 'bg-blue-100 dark:bg-blue-900/30',
    textClass: 'text-blue-800 dark:text-blue-300',
    label: 'Processing',
  },
  completed: {
    bgClass: 'bg-green-100 dark:bg-green-900/30',
    textClass: 'text-green-800 dark:text-green-300',
    label: 'Analyzed',
  },
  failed: {
    bgClass: 'bg-red-100 dark:bg-red-900/30',
    textClass: 'text-red-800 dark:text-red-300',
    label: 'Failed',
  },
}

const riskConfigs: Record<string, BadgeConfig> = {
  low: {
    bgClass: 'bg-green-100 dark:bg-green-900/30',
    textClass: 'text-green-800 dark:text-green-300',
    label: 'Low',
  },
  medium: {
    bgClass: 'bg-yellow-100 dark:bg-yellow-900/30',
    textClass: 'text-yellow-800 dark:text-yellow-300',
    label: 'Medium',
  },
  high: {
    bgClass: 'bg-red-100 dark:bg-red-900/30',
    textClass: 'text-red-800 dark:text-red-300',
    label: 'High',
  },
}

function getStatusConfig(status: string): BadgeConfig {
  return statusConfigs[status] ?? defaultStatusConfig
}

function getRiskConfig(riskLevel: string | null): BadgeConfig | null {
  if (!riskLevel) return null
  return riskConfigs[riskLevel] ?? null
}

function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  })
}
</script>

<template>
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Contracts</h2>
      <RouterLink
        v-if="hasContracts"
        to="/contracts"
        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300"
      >
        View all
      </RouterLink>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="animate-pulse">
        <div class="h-14 bg-gray-100 dark:bg-gray-700 rounded-lg"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!hasContracts" class="text-center py-8 text-gray-500 dark:text-gray-400">
      <svg
        class="h-12 w-12 mx-auto text-gray-300 dark:text-gray-600 mb-3"
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
      <p>No contracts yet</p>
      <RouterLink
        to="/contracts"
        class="inline-block mt-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300"
      >
        Upload your first contract
      </RouterLink>
    </div>

    <!-- Contracts List -->
    <div v-else class="space-y-3">
      <RouterLink
        v-for="contract in contracts"
        :key="contract.id"
        :to="`/contracts/${contract.id}`"
        class="block p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-400 transition-colors"
      >
        <div class="flex items-center justify-between gap-3">
          <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-900 dark:text-white truncate">
              {{ contract.title }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ formatDate(contract.created_at) }}
            </p>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <!-- Status Badge -->
            <span
              :class="[
                getStatusConfig(contract.status).bgClass,
                getStatusConfig(contract.status).textClass,
              ]"
              class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
            >
              <!-- Processing spinner -->
              <svg
                v-if="contract.status === 'processing'"
                class="animate-spin -ml-0.5 mr-1 h-3 w-3"
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
              {{ getStatusConfig(contract.status).label }}
            </span>

            <!-- Risk Badge -->
            <span
              v-if="getRiskConfig(contract.overall_risk_level)"
              :class="[
                getRiskConfig(contract.overall_risk_level)!.bgClass,
                getRiskConfig(contract.overall_risk_level)!.textClass,
              ]"
              class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
            >
              {{ getRiskConfig(contract.overall_risk_level)!.label }}
            </span>
          </div>
        </div>
      </RouterLink>
    </div>
  </div>
</template>

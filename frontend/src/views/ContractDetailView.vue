<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import type { Contract } from '@/types'
import ThemeToggle from '@/components/ThemeToggle.vue'

const route = useRoute()
const contractId = route.params.id
const contract = ref<Contract | null>(null)
const loading = ref(true)

// TODO: Fetch contract details
loading.value = false
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
          </nav>
        </div>
        <div class="flex items-center gap-4">
          <ThemeToggle />
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Breadcrumb -->
      <nav class="mb-4">
        <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
          <li>
            <RouterLink to="/contracts" class="hover:text-gray-700 dark:hover:text-gray-300"
              >Contracts</RouterLink
            >
          </li>
          <li>/</li>
          <li class="text-gray-900 dark:text-white">Contract #{{ contractId }}</li>
        </ol>
      </nav>

      <div v-if="loading" class="text-center py-12">
        <p class="text-gray-500 dark:text-gray-400">Loading contract...</p>
      </div>

      <div v-else-if="!contract" class="text-center py-12">
        <p class="text-gray-500 dark:text-gray-400">Contract not found.</p>
        <RouterLink to="/contracts" class="btn-primary mt-4 inline-block">
          Back to Contracts
        </RouterLink>
      </div>

      <div v-else>
        <!-- Contract Header -->
        <div class="card mb-6">
          <div class="flex items-start justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ contract.title }}</h1>
              <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                {{ contract.original_filename }}
              </p>
            </div>
            <span
              class="px-3 py-1 text-sm font-medium rounded-full"
              :class="{
                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300':
                  contract.status === 'draft',
                'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400':
                  contract.status === 'processing',
                'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400':
                  contract.status === 'analyzed',
                'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400':
                  contract.status === 'failed',
              }"
            >
              {{ contract.status }}
            </span>
          </div>
        </div>

        <!-- Analysis Section -->
        <div class="card mb-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Analysis</h2>
          <div v-if="contract.analysis" class="space-y-4">
            <div>
              <h3 class="font-medium text-gray-700 dark:text-gray-300">Summary</h3>
              <p class="text-gray-600 dark:text-gray-400">{{ contract.analysis.summary }}</p>
            </div>
            <div>
              <h3 class="font-medium text-gray-700 dark:text-gray-300">Risk Level</h3>
              <span
                class="px-2 py-1 text-sm font-medium rounded"
                :class="{
                  'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400':
                    contract.analysis.overall_risk_level === 'low',
                  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400':
                    contract.analysis.overall_risk_level === 'medium',
                  'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400':
                    contract.analysis.overall_risk_level === 'high',
                }"
              >
                {{ contract.analysis.overall_risk_level }}
              </span>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>Analysis pending...</p>
          </div>
        </div>

        <!-- Clauses Section -->
        <div class="card">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Identified Clauses
          </h2>
          <div v-if="contract.clauses && contract.clauses.length > 0" class="space-y-4">
            <div
              v-for="clause in contract.clauses"
              :key="clause.id"
              class="border-l-4 pl-4 py-2"
              :class="{
                'border-green-500': clause.risk_level === 'low',
                'border-yellow-500': clause.risk_level === 'medium',
                'border-red-500': clause.risk_level === 'high',
              }"
            >
              <div class="flex items-center justify-between">
                <h3 class="font-medium text-gray-900 dark:text-white">{{ clause.title }}</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{
                  clause.clause_type
                }}</span>
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ clause.explanation }}</p>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>No clauses identified yet.</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

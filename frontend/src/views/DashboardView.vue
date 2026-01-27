<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  ArrowRightOnRectangleIcon,
  DocumentTextIcon,
  ExclamationTriangleIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline'
import ThemeToggle from '@/components/ThemeToggle.vue'

const router = useRouter()
const authStore = useAuthStore()

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
              class="text-gray-900 dark:text-white font-medium"
              active-class="text-indigo-600 dark:text-indigo-400"
            >
              Dashboard
            </RouterLink>
            <RouterLink to="/contracts" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
              Contracts
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
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400">Welcome back, {{ authStore.user?.name }}!</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card flex items-center gap-4">
          <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
            <DocumentTextIcon class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Contracts</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
          </div>
        </div>
        <div class="card flex items-center gap-4">
          <div class="p-3 bg-yellow-100 dark:bg-yellow-900/50 rounded-lg">
            <ClockIcon class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Analysis</p>
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">0</p>
          </div>
        </div>
        <div class="card flex items-center gap-4">
          <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-lg">
            <ExclamationTriangleIcon class="h-6 w-6 text-red-600 dark:text-red-400" />
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">High Risk Items</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">0</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-4">
          <RouterLink to="/contracts" class="btn-primary"> Upload Contract </RouterLink>
          <RouterLink to="/contracts" class="btn-secondary"> View All Contracts </RouterLink>
        </div>
      </div>
    </main>
  </div>
</template>

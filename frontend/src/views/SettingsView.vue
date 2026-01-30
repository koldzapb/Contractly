<script setup lang="ts">
import { useRouter } from 'vue-router'
import { ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'
import ThemeToggle from '@/components/ThemeToggle.vue'
import MobileNav from '@/components/MobileNav.vue'
import NotificationSettings from '@/components/settings/NotificationSettings.vue'
import { useAuthStore } from '@/stores/auth'

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
              class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
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
    <main id="main-content" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>
        <p class="text-gray-600 dark:text-gray-400">
          Manage your account preferences and notification settings.
        </p>
      </div>

      <!-- Notification Settings Card -->
      <div
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6"
      >
        <NotificationSettings />
      </div>
    </main>
  </div>
</template>

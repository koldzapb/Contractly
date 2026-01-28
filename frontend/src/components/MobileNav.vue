<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const isOpen = ref(false)

function toggleMenu(): void {
  isOpen.value = !isOpen.value
}

function closeMenu(): void {
  isOpen.value = false
}
</script>

<template>
  <div class="md:hidden">
    <!-- Hamburger Button -->
    <button
      type="button"
      class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
      :aria-expanded="isOpen"
      aria-controls="mobile-menu"
      aria-label="Toggle navigation menu"
      @click="toggleMenu"
    >
      <Bars3Icon v-if="!isOpen" class="h-6 w-6" aria-hidden="true" />
      <XMarkIcon v-else class="h-6 w-6" aria-hidden="true" />
    </button>

    <!-- Mobile Menu Overlay -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 bg-black/50 z-40"
        aria-hidden="true"
        @click="closeMenu"
      />
    </Transition>

    <!-- Mobile Menu Panel -->
    <Transition
      enter-active-class="transition-transform duration-200"
      enter-from-class="-translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-200"
      leave-from-class="translate-x-0"
      leave-to-class="-translate-x-full"
    >
      <nav
        v-if="isOpen"
        id="mobile-menu"
        class="fixed top-0 left-0 bottom-0 w-64 bg-white dark:bg-gray-800 shadow-xl z-50 p-4"
        aria-label="Mobile navigation"
      >
        <div class="flex items-center justify-between mb-6">
          <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">Contractly</span>
          <button
            type="button"
            class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
            aria-label="Close navigation menu"
            @click="closeMenu"
          >
            <XMarkIcon class="h-6 w-6" aria-hidden="true" />
          </button>
        </div>

        <ul class="space-y-2" role="list">
          <li>
            <RouterLink
              to="/dashboard"
              class="block px-4 py-3 rounded-lg text-base font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
              :class="
                route.path === '/dashboard'
                  ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
              "
              :aria-current="route.path === '/dashboard' ? 'page' : undefined"
              @click="closeMenu"
            >
              Dashboard
            </RouterLink>
          </li>
          <li>
            <RouterLink
              to="/contracts"
              class="block px-4 py-3 rounded-lg text-base font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
              :class="
                route.path.startsWith('/contracts')
                  ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
              "
              :aria-current="route.path.startsWith('/contracts') ? 'page' : undefined"
              @click="closeMenu"
            >
              Contracts
            </RouterLink>
          </li>
          <li>
            <RouterLink
              to="/reminders"
              class="block px-4 py-3 rounded-lg text-base font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
              :class="
                route.path === '/reminders'
                  ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
              "
              :aria-current="route.path === '/reminders' ? 'page' : undefined"
              @click="closeMenu"
            >
              Reminders
            </RouterLink>
          </li>
        </ul>
      </nav>
    </Transition>
  </div>
</template>

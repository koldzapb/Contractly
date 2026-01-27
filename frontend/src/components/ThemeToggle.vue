<script setup lang="ts">
import { useThemeStore } from '@/stores/theme'
import { SunIcon, MoonIcon, ComputerDesktopIcon } from '@heroicons/vue/24/outline'

const themeStore = useThemeStore()
</script>

<template>
  <button
    type="button"
    class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800 transition-colors"
    :title="`Theme: ${themeStore.theme}`"
    @click="themeStore.toggleTheme"
  >
    <SunIcon v-if="themeStore.theme === 'light'" class="h-5 w-5" />
    <MoonIcon v-else-if="themeStore.theme === 'dark'" class="h-5 w-5" />
    <ComputerDesktopIcon v-else class="h-5 w-5" />
  </button>
</template>

import { ref, watch } from 'vue'
import { defineStore } from 'pinia'

export type Theme = 'light' | 'dark' | 'system'

export const useThemeStore = defineStore('theme', () => {
  const theme = ref<Theme>(getStoredTheme())
  const isDark = ref(false)

  function getStoredTheme(): Theme {
    const stored = localStorage.getItem('theme')
    if (stored === 'light' || stored === 'dark' || stored === 'system') {
      return stored
    }
    return 'system'
  }

  function getSystemTheme(): boolean {
    return window.matchMedia('(prefers-color-scheme: dark)').matches
  }

  function applyTheme(): void {
    if (theme.value === 'system') {
      isDark.value = getSystemTheme()
    } else {
      isDark.value = theme.value === 'dark'
    }

    if (isDark.value) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }

  function setTheme(newTheme: Theme): void {
    theme.value = newTheme
    localStorage.setItem('theme', newTheme)
    applyTheme()
  }

  function toggleTheme(): void {
    if (theme.value === 'light') {
      setTheme('dark')
    } else if (theme.value === 'dark') {
      setTheme('system')
    } else {
      setTheme('light')
    }
  }

  // Watch for system theme changes
  function initSystemThemeListener(): void {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    mediaQuery.addEventListener('change', () => {
      if (theme.value === 'system') {
        applyTheme()
      }
    })
  }

  // Initialize theme on store creation
  function initialize(): void {
    applyTheme()
    initSystemThemeListener()
  }

  watch(theme, applyTheme)

  return {
    theme,
    isDark,
    setTheme,
    toggleTheme,
    initialize,
  }
})

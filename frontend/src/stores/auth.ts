import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { User } from '@/types'
import * as authService from '@/services/auth'
import type { LoginCredentials, RegisterData } from '@/services/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const initialized = ref(false)

  const isAuthenticated = computed(() => !!user.value)

  /**
   * Initialize auth state - check if user is already logged in
   */
  async function initialize(): Promise<void> {
    if (initialized.value) return

    loading.value = true
    error.value = null

    try {
      user.value = await authService.checkAuth()
    } catch {
      user.value = null
    } finally {
      loading.value = false
      initialized.value = true
    }
  }

  /**
   * Register a new user
   */
  async function register(data: RegisterData): Promise<User> {
    loading.value = true
    error.value = null

    try {
      user.value = await authService.register(data)
      return user.value
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Registration failed. Please try again.'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Login user
   */
  async function login(credentials: LoginCredentials): Promise<User> {
    loading.value = true
    error.value = null

    try {
      user.value = await authService.login(credentials)
      return user.value
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Invalid credentials. Please try again.'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Logout user
   */
  async function logout(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      await authService.logout()
      user.value = null
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } }
      error.value = err.response?.data?.message ?? 'Logout failed.'
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * Clear error state
   */
  function clearError(): void {
    error.value = null
  }

  return {
    user,
    loading,
    error,
    initialized,
    isAuthenticated,
    initialize,
    register,
    login,
    logout,
    clearError,
  }
})

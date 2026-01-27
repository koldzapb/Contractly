import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)

  const isAuthenticated = computed(() => !!user.value)

  function setUser(newUser: User | null): void {
    user.value = newUser
  }

  function setToken(newToken: string | null): void {
    token.value = newToken
  }

  function logout(): void {
    user.value = null
    token.value = null
  }

  return {
    user,
    token,
    isAuthenticated,
    setUser,
    setToken,
    logout,
  }
})

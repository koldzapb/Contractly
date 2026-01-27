import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../auth'
import * as authService from '@/services/auth'

// Mock the auth service
vi.mock('@/services/auth', () => ({
  login: vi.fn(),
  register: vi.fn(),
  logout: vi.fn(),
  checkAuth: vi.fn(),
}))

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  describe('initial state', () => {
    it('should have null user initially', () => {
      const store = useAuthStore()
      expect(store.user).toBeNull()
    })

    it('should not be authenticated initially', () => {
      const store = useAuthStore()
      expect(store.isAuthenticated).toBe(false)
    })

    it('should not be loading initially', () => {
      const store = useAuthStore()
      expect(store.loading).toBe(false)
    })

    it('should have no error initially', () => {
      const store = useAuthStore()
      expect(store.error).toBeNull()
    })

    it('should not be initialized initially', () => {
      const store = useAuthStore()
      expect(store.initialized).toBe(false)
    })
  })

  describe('initialize', () => {
    it('should set user when authenticated', async () => {
      const mockUser = { id: 1, name: 'Test User', email: 'test@example.com' }
      vi.mocked(authService.checkAuth).mockResolvedValue(mockUser)

      const store = useAuthStore()
      await store.initialize()

      expect(store.user).toEqual(mockUser)
      expect(store.isAuthenticated).toBe(true)
      expect(store.initialized).toBe(true)
    })

    it('should set user to null when not authenticated', async () => {
      vi.mocked(authService.checkAuth).mockResolvedValue(null)

      const store = useAuthStore()
      await store.initialize()

      expect(store.user).toBeNull()
      expect(store.isAuthenticated).toBe(false)
      expect(store.initialized).toBe(true)
    })

    it('should only initialize once', async () => {
      vi.mocked(authService.checkAuth).mockResolvedValue(null)

      const store = useAuthStore()
      await store.initialize()
      await store.initialize()

      expect(authService.checkAuth).toHaveBeenCalledTimes(1)
    })
  })

  describe('login', () => {
    it('should set user on successful login', async () => {
      const mockUser = { id: 1, name: 'Test User', email: 'test@example.com' }
      vi.mocked(authService.login).mockResolvedValue(mockUser)

      const store = useAuthStore()
      const credentials = { email: 'test@example.com', password: 'password123' }

      const result = await store.login(credentials)

      expect(authService.login).toHaveBeenCalledWith(credentials)
      expect(store.user).toEqual(mockUser)
      expect(store.isAuthenticated).toBe(true)
      expect(result).toEqual(mockUser)
    })

    it('should set error on failed login', async () => {
      const error = {
        response: { data: { message: 'Invalid credentials' } },
      }
      vi.mocked(authService.login).mockRejectedValue(error)

      const store = useAuthStore()

      await expect(
        store.login({ email: 'test@example.com', password: 'wrong' }),
      ).rejects.toEqual(error)

      expect(store.error).toBe('Invalid credentials')
      expect(store.user).toBeNull()
    })

    it('should set loading state during login', async () => {
      vi.mocked(authService.login).mockImplementation(
        () => new Promise((resolve) => setTimeout(() => resolve({ id: 1, name: 'Test', email: 'test@example.com' }), 100)),
      )

      const store = useAuthStore()
      const loginPromise = store.login({ email: 'test@example.com', password: 'password' })

      expect(store.loading).toBe(true)

      await loginPromise

      expect(store.loading).toBe(false)
    })
  })

  describe('register', () => {
    it('should set user on successful registration', async () => {
      const mockUser = { id: 1, name: 'New User', email: 'new@example.com' }
      vi.mocked(authService.register).mockResolvedValue(mockUser)

      const store = useAuthStore()
      const data = {
        name: 'New User',
        email: 'new@example.com',
        password: 'password123',
        password_confirmation: 'password123',
      }

      const result = await store.register(data)

      expect(authService.register).toHaveBeenCalledWith(data)
      expect(store.user).toEqual(mockUser)
      expect(store.isAuthenticated).toBe(true)
      expect(result).toEqual(mockUser)
    })

    it('should set error on failed registration', async () => {
      const error = {
        response: { data: { message: 'Email already taken' } },
      }
      vi.mocked(authService.register).mockRejectedValue(error)

      const store = useAuthStore()

      await expect(
        store.register({
          name: 'Test',
          email: 'test@example.com',
          password: 'password',
          password_confirmation: 'password',
        }),
      ).rejects.toEqual(error)

      expect(store.error).toBe('Email already taken')
    })
  })

  describe('logout', () => {
    it('should clear user on successful logout', async () => {
      vi.mocked(authService.logout).mockResolvedValue(undefined)

      const store = useAuthStore()
      store.user = { id: 1, name: 'Test', email: 'test@example.com' }

      await store.logout()

      expect(authService.logout).toHaveBeenCalled()
      expect(store.user).toBeNull()
      expect(store.isAuthenticated).toBe(false)
    })

    it('should set error on failed logout', async () => {
      const error = {
        response: { data: { message: 'Logout failed' } },
      }
      vi.mocked(authService.logout).mockRejectedValue(error)

      const store = useAuthStore()
      store.user = { id: 1, name: 'Test', email: 'test@example.com' }

      await expect(store.logout()).rejects.toEqual(error)

      expect(store.error).toBe('Logout failed')
    })
  })

  describe('clearError', () => {
    it('should clear the error state', () => {
      const store = useAuthStore()
      store.error = 'Some error'

      store.clearError()

      expect(store.error).toBeNull()
    })
  })
})

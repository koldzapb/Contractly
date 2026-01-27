import api from './api'
import type { User, ApiResponse } from '@/types'

export interface LoginCredentials {
  email: string
  password: string
  remember?: boolean
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface AuthResponse {
  user: User
  message?: string
}

/**
 * Get CSRF cookie from Laravel Sanctum
 */
export async function getCsrfCookie(): Promise<void> {
  await api.get('/sanctum/csrf-cookie', {
    baseURL: import.meta.env.VITE_APP_URL || 'http://localhost',
  })
}

/**
 * Register a new user
 */
export async function register(data: RegisterData): Promise<User> {
  await getCsrfCookie()
  const response = await api.post<ApiResponse<AuthResponse>>('/register', data)
  return response.data.data.user
}

/**
 * Login user
 */
export async function login(credentials: LoginCredentials): Promise<User> {
  await getCsrfCookie()
  const response = await api.post<ApiResponse<AuthResponse>>('/login', credentials)
  return response.data.data.user
}

/**
 * Logout user
 */
export async function logout(): Promise<void> {
  await api.post('/logout')
}

/**
 * Get current authenticated user
 */
export async function getUser(): Promise<User> {
  const response = await api.get<ApiResponse<User>>('/user')
  return response.data.data
}

/**
 * Check if user is authenticated
 */
export async function checkAuth(): Promise<User | null> {
  try {
    return await getUser()
  } catch {
    return null
  }
}

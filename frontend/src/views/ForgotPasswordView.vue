<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import { ExclamationCircleIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import * as authService from '@/services/auth'
import ThemeToggle from '@/components/ThemeToggle.vue'

const email = ref('')
const loading = ref(false)
const error = ref<string | null>(null)
const success = ref(false)

async function handleSubmit(): Promise<void> {
  loading.value = true
  error.value = null
  success.value = false

  try {
    await authService.forgotPassword(email.value)
    success.value = true
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message ?? 'Failed to send reset link. Please try again.'
  } finally {
    loading.value = false
  }
}

function clearError(): void {
  error.value = null
}
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8"
  >
    <div class="absolute top-4 right-4">
      <ThemeToggle />
    </div>
    <div class="max-w-md w-full space-y-8">
      <div>
        <h1 class="text-center text-2xl font-bold text-indigo-600 dark:text-indigo-400">
          Contractly
        </h1>
        <h2 class="mt-6 text-center text-3xl font-bold text-gray-900 dark:text-white">
          Reset your password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
          Enter your email address and we'll send you a link to reset your password.
        </p>
      </div>

      <form v-if="!success" class="mt-8 space-y-6" novalidate @submit.prevent="handleSubmit">
        <div
          v-if="error"
          class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg flex items-start gap-3"
        >
          <ExclamationCircleIcon class="h-5 w-5 flex-shrink-0 mt-0.5" />
          <span>{{ error }}</span>
        </div>

        <div>
          <label for="email" class="label">Email address</label>
          <input
            id="email"
            v-model="email"
            name="email"
            type="email"
            autocomplete="email"
            required
            class="input"
            placeholder="you@example.com"
            @focus="clearError"
          />
        </div>

        <button type="submit" :disabled="loading" class="w-full btn-primary py-3">
          <span v-if="loading">Sending reset link...</span>
          <span v-else>Send reset link</span>
        </button>

        <div class="text-center">
          <RouterLink
            to="/login"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
          >
            Back to sign in
          </RouterLink>
        </div>
      </form>

      <div v-else class="mt-8 space-y-6">
        <div
          class="bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-4 rounded-lg flex items-start gap-3"
        >
          <CheckCircleIcon class="h-5 w-5 flex-shrink-0 mt-0.5" />
          <div>
            <p class="font-medium">Check your email</p>
            <p class="mt-1 text-sm">
              We've sent a password reset link to <strong>{{ email }}</strong>
            </p>
          </div>
        </div>

        <div class="text-center space-y-4">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Didn't receive the email? Check your spam folder.
          </p>
          <RouterLink
            to="/login"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
          >
            Back to sign in
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

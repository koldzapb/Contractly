<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const error = ref<string | null>(null)

async function handleSubmit(): Promise<void> {
  // TODO: Implement registration logic
  loading.value = true
  error.value = null

  try {
    // API call will be implemented later
    console.log('Register:', {
      name: name.value,
      email: email.value,
      password: password.value,
    })
  } catch (e) {
    error.value = 'Registration failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">Create your account</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Already have an account?
          <RouterLink to="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
            Sign in
          </RouterLink>
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
          {{ error }}
        </div>

        <div class="space-y-4">
          <div>
            <label for="name" class="label">Full name</label>
            <input
              id="name"
              v-model="name"
              name="name"
              type="text"
              autocomplete="name"
              required
              class="input"
              placeholder="John Doe"
            />
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
            />
          </div>

          <div>
            <label for="password" class="label">Password</label>
            <input
              id="password"
              v-model="password"
              name="password"
              type="password"
              autocomplete="new-password"
              required
              class="input"
              placeholder="••••••••"
            />
          </div>

          <div>
            <label for="password_confirmation" class="label">Confirm password</label>
            <input
              id="password_confirmation"
              v-model="passwordConfirmation"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              required
              class="input"
              placeholder="••••••••"
            />
          </div>
        </div>

        <button type="submit" :disabled="loading" class="w-full btn-primary py-3">
          <span v-if="loading">Creating account...</span>
          <span v-else>Create account</span>
        </button>
      </form>
    </div>
  </div>
</template>

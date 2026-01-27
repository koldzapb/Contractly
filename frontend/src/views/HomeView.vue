<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center">
          <span class="text-xl font-bold text-indigo-600">Contractly</span>
        </div>
        <div class="flex items-center space-x-4">
          <RouterLink to="/login" class="text-gray-600 hover:text-gray-900">Sign in</RouterLink>
          <RouterLink to="/register" class="btn-primary">Get started</RouterLink>
        </div>
      </nav>
    </header>

    <!-- Hero Section -->
    <main class="flex-1 flex items-center justify-center">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
          AI-Powered Contract Analysis
        </h1>
        <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
          Upload your contracts and let AI identify risky clauses, extract important deadlines, and
          provide actionable insights.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <RouterLink to="/register" class="btn-primary text-lg px-8 py-3">
            Start analyzing contracts
          </RouterLink>
          <RouterLink to="/login" class="btn-secondary text-lg px-8 py-3">
            Sign in to your account
          </RouterLink>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center text-gray-500">
        <p>&copy; 2024 Contractly. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

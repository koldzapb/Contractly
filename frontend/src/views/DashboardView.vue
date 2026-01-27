<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <span class="text-xl font-bold text-indigo-600">Contractly</span>
        <nav class="flex items-center space-x-6">
          <RouterLink
            to="/dashboard"
            class="text-gray-900 font-medium"
            active-class="text-indigo-600"
          >
            Dashboard
          </RouterLink>
          <RouterLink to="/contracts" class="text-gray-600 hover:text-gray-900">
            Contracts
          </RouterLink>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600">Welcome back! Here's an overview of your contracts.</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card">
          <p class="text-sm font-medium text-gray-500">Total Contracts</p>
          <p class="text-3xl font-bold text-gray-900">0</p>
        </div>
        <div class="card">
          <p class="text-sm font-medium text-gray-500">Pending Analysis</p>
          <p class="text-3xl font-bold text-yellow-600">0</p>
        </div>
        <div class="card">
          <p class="text-sm font-medium text-gray-500">High Risk Items</p>
          <p class="text-3xl font-bold text-red-600">0</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-4">
          <RouterLink to="/contracts" class="btn-primary"> Upload Contract </RouterLink>
          <RouterLink to="/contracts" class="btn-secondary"> View All Contracts </RouterLink>
        </div>
      </div>
    </main>
  </div>
</template>

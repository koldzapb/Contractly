<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import ThemeToggle from '@/components/ThemeToggle.vue'

const dragOver = ref(false)

function handleDrop(event: DragEvent): void {
  dragOver.value = false
  const files = event.dataTransfer?.files
  if (files && files.length > 0) {
    handleFiles(files)
  }
}

function handleFileSelect(event: Event): void {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    handleFiles(target.files)
  }
}

function handleFiles(files: FileList): void {
  // TODO: Implement file upload logic
  console.log('Files to upload:', files)
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-8">
          <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">Contractly</span>
          <nav class="hidden md:flex items-center space-x-6">
            <RouterLink to="/dashboard" class="text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
              Dashboard
            </RouterLink>
            <RouterLink
              to="/contracts"
              class="text-gray-900 dark:text-white font-medium"
              active-class="text-indigo-600 dark:text-indigo-400"
            >
              Contracts
            </RouterLink>
          </nav>
        </div>
        <div class="flex items-center gap-4">
          <ThemeToggle />
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Contracts</h1>
        <p class="text-gray-600 dark:text-gray-400">Upload and manage your contracts.</p>
      </div>

      <!-- Upload Area -->
      <div
        class="card mb-8"
        :class="{ 'border-indigo-500 dark:border-indigo-400 border-2': dragOver }"
        @dragover.prevent="dragOver = true"
        @dragleave="dragOver = false"
        @drop.prevent="handleDrop"
      >
        <div class="text-center py-12">
          <svg
            class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500"
            stroke="currentColor"
            fill="none"
            viewBox="0 0 48 48"
          >
            <path
              d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <div class="mt-4">
            <label
              for="file-upload"
              class="cursor-pointer font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
            >
              Upload a file
            </label>
            <input
              id="file-upload"
              name="file-upload"
              type="file"
              accept=".pdf"
              class="sr-only"
              @change="handleFileSelect"
            />
            <span class="text-gray-500 dark:text-gray-400"> or drag and drop</span>
          </div>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">PDF files up to 10MB</p>
        </div>
      </div>

      <!-- Contract List -->
      <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Your Contracts</h2>
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
          <p>No contracts uploaded yet.</p>
          <p class="text-sm">Upload your first contract to get started.</p>
        </div>
      </div>
    </main>
  </div>
</template>

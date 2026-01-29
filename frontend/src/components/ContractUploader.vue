<script setup lang="ts">
import { ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useContractsStore } from '@/stores/contracts'
import UploadProgress from './UploadProgress.vue'
import PasteTextForm from './PasteTextForm.vue'
import QuickAnalysisResult from './QuickAnalysisResult.vue'
import type { QuickAnalysisResult as QuickAnalysisResultType } from '@/types/quickAnalysis'

type TabType = 'upload' | 'paste'

const store = useContractsStore()
const { upload, isUploading } = storeToRefs(store)

const activeTab = ref<TabType>('upload')
const dragOver = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const quickAnalysisResult = ref<QuickAnalysisResultType | null>(null)

const uploadError = computed(() => upload.value.error)

function handleDrop(event: DragEvent): void {
  dragOver.value = false
  // Only handle drop on upload tab
  if (activeTab.value !== 'upload') return

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
  // Reset input so the same file can be selected again
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

async function handleFiles(files: FileList): Promise<void> {
  const file = files[0]
  if (!file) return

  try {
    await store.uploadContract({ file })
  } catch {
    // Error is handled by the store
  }
}

function handleRetry(): void {
  if (upload.value.file) {
    handleFiles(new DataTransfer().files)
  }
  store.resetUpload()
}

function handleAnalyzeResult(result: QuickAnalysisResultType): void {
  quickAnalysisResult.value = result
}

function handleBackToForm(): void {
  quickAnalysisResult.value = null
}

function handleTabChange(tab: TabType): void {
  activeTab.value = tab
  // Clear quick analysis result when switching tabs
  if (tab === 'upload') {
    quickAnalysisResult.value = null
  }
}
</script>

<template>
  <div
    class="card"
    :class="{
      'border-indigo-500 dark:border-indigo-400 border-2': dragOver && activeTab === 'upload',
      'border-red-500 dark:border-red-400 border-2': uploadError && activeTab === 'upload',
    }"
    @dragover.prevent="activeTab === 'upload' && (dragOver = true)"
    @dragleave="dragOver = false"
    @drop.prevent="handleDrop"
  >
    <!-- Tab Navigation -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4">
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
        :class="
          activeTab === 'upload'
            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'
        "
        @click="handleTabChange('upload')"
      >
        <span class="flex items-center gap-2">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
            />
          </svg>
          Upload File
        </span>
      </button>
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
        :class="
          activeTab === 'paste'
            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'
        "
        @click="handleTabChange('paste')"
      >
        <span class="flex items-center gap-2">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          Paste Text
        </span>
      </button>
    </div>

    <!-- Upload Tab Content -->
    <div v-if="activeTab === 'upload'">
      <!-- Upload Progress State -->
      <UploadProgress
        v-if="isUploading"
        :progress="upload.progress"
        :filename="upload.file?.name ?? ''"
      />

      <!-- Error State -->
      <div v-else-if="uploadError" class="text-center py-8">
        <div
          class="mx-auto h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4"
        >
          <svg
            class="h-6 w-6 text-red-600 dark:text-red-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
        </div>
        <p class="text-red-600 dark:text-red-400 font-medium mb-2">Upload Failed</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ uploadError }}</p>
        <button type="button" class="btn-secondary text-sm" @click="handleRetry">Try Again</button>
      </div>

      <!-- Default Upload State -->
      <div v-else class="text-center py-12">
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
            ref="fileInput"
            name="file-upload"
            type="file"
            accept=".pdf,.jpg,.jpeg,.png,.webp,.gif,.txt,application/pdf,image/jpeg,image/png,image/webp,image/gif,text/plain"
            class="sr-only"
            @change="handleFileSelect"
          />
          <span class="text-gray-500 dark:text-gray-400"> or drag and drop</span>
        </div>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          PDF (up to 10MB), images (up to 20MB), or text files (up to 5MB)
        </p>
      </div>
    </div>

    <!-- Paste Text Tab Content -->
    <div v-else>
      <!-- Show result if we have one -->
      <QuickAnalysisResult
        v-if="quickAnalysisResult"
        :result="quickAnalysisResult"
        @back="handleBackToForm"
      />
      <!-- Otherwise show the form -->
      <PasteTextForm v-else @analyze="handleAnalyzeResult" />
    </div>
  </div>
</template>

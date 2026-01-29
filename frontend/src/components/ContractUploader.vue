<script setup lang="ts">
import { ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useContractsStore } from '@/stores/contracts'
import UploadProgress from './UploadProgress.vue'

const store = useContractsStore()
const { upload, isUploading } = storeToRefs(store)

const dragOver = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

const uploadError = computed(() => upload.value.error)

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
</script>

<template>
  <div
    class="card"
    :class="{
      'border-indigo-500 dark:border-indigo-400 border-2': dragOver,
      'border-red-500 dark:border-red-400 border-2': uploadError,
    }"
    @dragover.prevent="dragOver = true"
    @dragleave="dragOver = false"
    @drop.prevent="handleDrop"
  >
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
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline'

interface Props {
  modelValue: string
  placeholder?: string
  debounceMs?: number
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Search contracts...',
  debounceMs: 300,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  search: [value: string]
}>()

const inputValue = ref(props.modelValue)
let debounceTimer: ReturnType<typeof setTimeout> | null = null

watch(
  () => props.modelValue,
  (newValue) => {
    inputValue.value = newValue
  },
)

function handleInput(event: Event): void {
  const value = (event.target as HTMLInputElement).value
  inputValue.value = value
  emit('update:modelValue', value)

  // Debounce the search event
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }

  debounceTimer = setTimeout(() => {
    emit('search', value)
  }, props.debounceMs)
}

function handleClear(): void {
  inputValue.value = ''
  emit('update:modelValue', '')
  emit('search', '')
}

function handleKeydown(event: KeyboardEvent): void {
  if (event.key === 'Enter') {
    // Immediate search on Enter
    if (debounceTimer) {
      clearTimeout(debounceTimer)
    }
    emit('search', inputValue.value)
  } else if (event.key === 'Escape') {
    handleClear()
  }
}
</script>

<template>
  <div class="relative">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
      <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
    </div>
    <input
      type="text"
      :value="inputValue"
      :placeholder="placeholder"
      class="block w-full rounded-lg border border-gray-300 bg-white py-2 pr-10 pl-10 text-sm text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500"
      @input="handleInput"
      @keydown="handleKeydown"
    />
    <button
      v-if="inputValue"
      type="button"
      class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
      @click="handleClear"
    >
      <XMarkIcon class="h-5 w-5" aria-hidden="true" />
      <span class="sr-only">Clear search</span>
    </button>
  </div>
</template>

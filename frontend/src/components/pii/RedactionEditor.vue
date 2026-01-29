<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import type { PiiDetectionResult, PiiType, DetectedPii } from '@/types'
import PiiTypeBadge from './PiiTypeBadge.vue'
import PiiHighlight from './PiiHighlight.vue'
import RedactionControls from './RedactionControls.vue'
import RedactionPreview from './RedactionPreview.vue'

interface Props {
  piiDetection: PiiDetectionResult
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})

const emit = defineEmits<{
  'apply-redactions': [itemIds: string[]]
  skip: []
}>()

// Selection state
const selectedIds = ref<Set<string>>(new Set())

// Tab state
const activeTab = ref<'editor' | 'preview'>('editor')

// Initialize with all items selected by default
watch(
  () => props.piiDetection.items,
  (items) => {
    selectedIds.value = new Set(items.map((item) => item.id))
  },
  { immediate: true },
)

// Computed values
const selectedCount = computed(() => selectedIds.value.size)

const selectedCountsByType = computed(() => {
  const counts: Record<PiiType, number> = {
    ssn: 0,
    email: 0,
    phone: 0,
    credit_card: 0,
    bank_routing: 0,
    bank_account: 0,
  }

  for (const item of props.piiDetection.items) {
    if (selectedIds.value.has(item.id)) {
      counts[item.type]++
    }
  }

  return counts
})

const groupedItems = computed(() => {
  const groups: Record<PiiType, DetectedPii[]> = {
    ssn: [],
    email: [],
    phone: [],
    credit_card: [],
    bank_routing: [],
    bank_account: [],
  }

  for (const item of props.piiDetection.items) {
    if (!groups[item.type]) {
      groups[item.type] = []
    }
    groups[item.type].push(item)
  }

  return groups
})

const activeGroups = computed(() => {
  return (Object.entries(groupedItems.value) as [PiiType, DetectedPii[]][])
    .filter(([, items]) => items.length > 0)
    .sort(([, a], [, b]) => b.length - a.length)
})

// Actions
function toggleItem(id: string): void {
  const newSet = new Set(selectedIds.value)
  if (newSet.has(id)) {
    newSet.delete(id)
  } else {
    newSet.add(id)
  }
  selectedIds.value = newSet
}

function selectAll(): void {
  selectedIds.value = new Set(props.piiDetection.items.map((item) => item.id))
}

function clearAll(): void {
  selectedIds.value = new Set()
}

function toggleType(type: PiiType): void {
  const typeItems = props.piiDetection.items.filter((item) => item.type === type)
  const allTypeSelected = typeItems.every((item) => selectedIds.value.has(item.id))

  const newSet = new Set(selectedIds.value)
  for (const item of typeItems) {
    if (allTypeSelected) {
      newSet.delete(item.id)
    } else {
      newSet.add(item.id)
    }
  }
  selectedIds.value = newSet
}

function isItemSelected(id: string): boolean {
  return selectedIds.value.has(id)
}

function handleApply(): void {
  emit('apply-redactions', Array.from(selectedIds.value))
}

function handleSkip(): void {
  emit('skip')
}
</script>

<template>
  <div class="card">
    <!-- Header -->
    <div class="flex items-start justify-between mb-6">
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
          <svg
            class="h-5 w-5 text-yellow-500"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
            />
          </svg>
          PII Detected
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          We found {{ piiDetection.total_count }} potentially sensitive item{{
            piiDetection.total_count === 1 ? '' : 's'
          }}
          in this document. Review and select which items to redact.
        </p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <button
          type="button"
          class="py-2 px-1 border-b-2 font-medium text-sm transition-colors"
          :class="[
            activeTab === 'editor'
              ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
          ]"
          @click="activeTab = 'editor'"
        >
          Select Items
        </button>
        <button
          type="button"
          class="py-2 px-1 border-b-2 font-medium text-sm transition-colors"
          :class="[
            activeTab === 'preview'
              ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
          ]"
          @click="activeTab = 'preview'"
        >
          Preview Changes
        </button>
      </nav>
    </div>

    <!-- Editor Tab -->
    <div v-show="activeTab === 'editor'" class="space-y-6">
      <!-- Controls Panel -->
      <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
        <RedactionControls
          :counts-by-type="piiDetection.counts_by_type"
          :selected-counts-by-type="selectedCountsByType"
          :total-count="piiDetection.total_count"
          :selected-count="selectedCount"
          @select-all="selectAll"
          @clear-all="clearAll"
          @toggle-type="toggleType"
        />
      </div>

      <!-- Items by Type -->
      <div class="space-y-6">
        <div v-for="[type, items] in activeGroups" :key="type" class="space-y-3">
          <div class="flex items-center gap-2">
            <PiiTypeBadge :type="type" :count="items.length" />
          </div>
          <div class="space-y-2">
            <div
              v-for="item in items"
              :key="item.id"
              class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg"
            >
              <PiiHighlight
                :type="item.type"
                :value="item.value"
                :redacted-value="item.redacted_value"
                :selected="isItemSelected(item.id)"
                @toggle="toggleItem(item.id)"
              />
              <span class="text-xs text-gray-500 dark:text-gray-400 truncate flex-1">
                {{ item.context }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Preview Tab -->
    <div v-show="activeTab === 'preview'">
      <RedactionPreview
        :original-text="piiDetection.extracted_text"
        :items="piiDetection.items"
        :selected-ids="selectedIds"
      />
    </div>

    <!-- Actions -->
    <div
      class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between gap-4"
    >
      <button
        type="button"
        class="btn-secondary text-sm order-2 sm:order-1"
        :disabled="loading"
        @click="handleSkip"
      >
        Skip Redaction
      </button>
      <button
        type="button"
        class="btn-primary text-sm order-1 sm:order-2 flex items-center justify-center gap-2"
        :disabled="loading || selectedCount === 0"
        @click="handleApply"
      >
        <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
          />
        </svg>
        {{ loading ? 'Applying...' : `Apply Redactions (${selectedCount})` }}
      </button>
    </div>
  </div>
</template>

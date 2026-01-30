<script setup lang="ts">
import { ref, watch } from 'vue'
import { FunnelIcon, ChevronDownIcon, ChevronUpIcon } from '@heroicons/vue/24/outline'
import type {
  ContractStatus,
  RiskLevel,
  FileType,
  SortField,
  SortOrder,
  ContractSearchFilters,
} from '@/types'

interface Props {
  filters: ContractSearchFilters
  activeFilterCount?: number
}

const props = withDefaults(defineProps<Props>(), {
  activeFilterCount: 0,
})

const emit = defineEmits<{
  'update:filters': [filters: Partial<ContractSearchFilters>]
  clear: []
}>()

const isExpanded = ref(false)

// Local state for filters
const selectedStatuses = ref<ContractStatus[]>(props.filters.status || [])
const selectedRiskLevels = ref<RiskLevel[]>(props.filters.risk_level || [])
const selectedFileTypes = ref<FileType[]>(props.filters.file_type || [])
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')
const hasDeadlines = ref<boolean | undefined>(props.filters.has_deadlines)
const sortBy = ref<SortField>(props.filters.sort_by || 'created_at')
const sortOrder = ref<SortOrder>(props.filters.sort_order || 'desc')

// Options
const statusOptions: { value: ContractStatus; label: string; color: string }[] = [
  { value: 'pending', label: 'Pending', color: 'gray' },
  { value: 'processing', label: 'Processing', color: 'blue' },
  { value: 'completed', label: 'Completed', color: 'green' },
  { value: 'failed', label: 'Failed', color: 'red' },
]

const riskOptions: { value: RiskLevel; label: string; color: string }[] = [
  { value: 'low', label: 'Low Risk', color: 'green' },
  { value: 'medium', label: 'Medium Risk', color: 'yellow' },
  { value: 'high', label: 'High Risk', color: 'red' },
]

const fileTypeOptions: { value: FileType; label: string }[] = [
  { value: 'pdf', label: 'PDF' },
  { value: 'image', label: 'Image' },
  { value: 'text', label: 'Text' },
]

const sortOptions: { value: SortField; label: string }[] = [
  { value: 'created_at', label: 'Date Created' },
  { value: 'title', label: 'Title' },
  { value: 'overall_risk_level', label: 'Risk Level' },
  { value: 'status', label: 'Status' },
  { value: 'analyzed_at', label: 'Date Analyzed' },
  { value: 'file_size', label: 'File Size' },
]

// Watch for external filter changes
watch(
  () => props.filters,
  (newFilters) => {
    selectedStatuses.value = newFilters.status || []
    selectedRiskLevels.value = newFilters.risk_level || []
    selectedFileTypes.value = newFilters.file_type || []
    dateFrom.value = newFilters.date_from || ''
    dateTo.value = newFilters.date_to || ''
    hasDeadlines.value = newFilters.has_deadlines
    sortBy.value = newFilters.sort_by || 'created_at'
    sortOrder.value = newFilters.sort_order || 'desc'
  },
  { deep: true },
)

function toggleStatus(status: ContractStatus): void {
  const index = selectedStatuses.value.indexOf(status)
  if (index === -1) {
    selectedStatuses.value.push(status)
  } else {
    selectedStatuses.value.splice(index, 1)
  }
  applyFilters()
}

function toggleRiskLevel(level: RiskLevel): void {
  const index = selectedRiskLevels.value.indexOf(level)
  if (index === -1) {
    selectedRiskLevels.value.push(level)
  } else {
    selectedRiskLevels.value.splice(index, 1)
  }
  applyFilters()
}

function toggleFileType(type: FileType): void {
  const index = selectedFileTypes.value.indexOf(type)
  if (index === -1) {
    selectedFileTypes.value.push(type)
  } else {
    selectedFileTypes.value.splice(index, 1)
  }
  applyFilters()
}

function toggleHasDeadlines(): void {
  if (hasDeadlines.value === undefined) {
    hasDeadlines.value = true
  } else if (hasDeadlines.value === true) {
    hasDeadlines.value = false
  } else {
    hasDeadlines.value = undefined
  }
  applyFilters()
}

function handleDateChange(): void {
  applyFilters()
}

function handleSortChange(): void {
  applyFilters()
}

function toggleSortOrder(): void {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  applyFilters()
}

function applyFilters(): void {
  emit('update:filters', {
    status: selectedStatuses.value.length ? selectedStatuses.value : undefined,
    risk_level: selectedRiskLevels.value.length ? selectedRiskLevels.value : undefined,
    file_type: selectedFileTypes.value.length ? selectedFileTypes.value : undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    has_deadlines: hasDeadlines.value,
    sort_by: sortBy.value,
    sort_order: sortOrder.value,
  })
}

function clearAllFilters(): void {
  selectedStatuses.value = []
  selectedRiskLevels.value = []
  selectedFileTypes.value = []
  dateFrom.value = ''
  dateTo.value = ''
  hasDeadlines.value = undefined
  sortBy.value = 'created_at'
  sortOrder.value = 'desc'
  emit('clear')
}

function getStatusColorClass(color: string): string {
  const colorClasses: Record<string, string> = {
    gray: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    green: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    red: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
  }
  return colorClasses[color] ?? colorClasses.gray ?? ''
}

function getSelectedColorClass(color: string): string {
  const colorClasses: Record<string, string> = {
    gray: 'bg-gray-600 text-white dark:bg-gray-500',
    blue: 'bg-blue-600 text-white dark:bg-blue-500',
    green: 'bg-green-600 text-white dark:bg-green-500',
    red: 'bg-red-600 text-white dark:bg-red-500',
    yellow: 'bg-yellow-600 text-white dark:bg-yellow-500',
  }
  return colorClasses[color] ?? colorClasses.gray ?? ''
}
</script>

<template>
  <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
    <!-- Header -->
    <button
      type="button"
      class="flex w-full items-center justify-between px-4 py-3 text-left"
      @click="isExpanded = !isExpanded"
    >
      <div class="flex items-center gap-2">
        <FunnelIcon class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Filters</span>
        <span
          v-if="props.activeFilterCount > 0"
          class="rounded-full bg-blue-600 px-2 py-0.5 text-xs font-medium text-white"
        >
          {{ props.activeFilterCount }}
        </span>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="props.activeFilterCount > 0"
          type="button"
          class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
          @click.stop="clearAllFilters"
        >
          Clear all
        </button>
        <component
          :is="isExpanded ? ChevronUpIcon : ChevronDownIcon"
          class="h-5 w-5 text-gray-400"
        />
      </div>
    </button>

    <!-- Filter Content -->
    <div v-if="isExpanded" class="border-t border-gray-200 p-4 dark:border-gray-700">
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <!-- Status Filter -->
        <div>
          <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
            Status
          </label>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="option in statusOptions"
              :key="option.value"
              type="button"
              class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
              :class="
                selectedStatuses.includes(option.value)
                  ? getSelectedColorClass(option.color)
                  : getStatusColorClass(option.color)
              "
              @click="toggleStatus(option.value)"
            >
              {{ option.label }}
            </button>
          </div>
        </div>

        <!-- Risk Level Filter -->
        <div>
          <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
            Risk Level
          </label>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="option in riskOptions"
              :key="option.value"
              type="button"
              class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
              :class="
                selectedRiskLevels.includes(option.value)
                  ? getSelectedColorClass(option.color)
                  : getStatusColorClass(option.color)
              "
              @click="toggleRiskLevel(option.value)"
            >
              {{ option.label }}
            </button>
          </div>
        </div>

        <!-- File Type Filter -->
        <div>
          <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
            File Type
          </label>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="option in fileTypeOptions"
              :key="option.value"
              type="button"
              class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
              :class="
                selectedFileTypes.includes(option.value)
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
              "
              @click="toggleFileType(option.value)"
            >
              {{ option.label }}
            </button>
          </div>
        </div>

        <!-- Date Range Filter -->
        <div>
          <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
            Date Range
          </label>
          <div class="flex gap-2">
            <input
              v-model="dateFrom"
              type="date"
              class="block w-full rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="handleDateChange"
            />
            <span class="self-center text-gray-500">to</span>
            <input
              v-model="dateTo"
              type="date"
              class="block w-full rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="handleDateChange"
            />
          </div>
        </div>

        <!-- Has Deadlines Filter -->
        <div>
          <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
            Deadlines
          </label>
          <button
            type="button"
            class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
            :class="
              hasDeadlines === undefined
                ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                : hasDeadlines
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-600 text-white'
            "
            @click="toggleHasDeadlines"
          >
            {{ hasDeadlines === undefined ? 'Any' : hasDeadlines ? 'Has Deadlines' : 'No Deadlines' }}
          </button>
        </div>

        <!-- Sort Options -->
        <div>
          <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
            Sort By
          </label>
          <div class="flex gap-2">
            <select
              v-model="sortBy"
              class="block w-full rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="handleSortChange"
            >
              <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
            <button
              type="button"
              class="rounded-md border border-gray-300 px-2 py-1 text-sm hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
              :title="sortOrder === 'asc' ? 'Ascending' : 'Descending'"
              @click="toggleSortOrder"
            >
              <component
                :is="sortOrder === 'asc' ? ChevronUpIcon : ChevronDownIcon"
                class="h-4 w-4 text-gray-600 dark:text-gray-400"
              />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

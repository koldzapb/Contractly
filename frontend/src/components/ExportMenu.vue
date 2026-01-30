<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import {
  ArrowDownTrayIcon,
  DocumentTextIcon,
  TableCellsIcon,
  CalendarDaysIcon,
  ArchiveBoxIcon,
} from '@heroicons/vue/24/outline'
import { exportPdf, exportClausesCsv, exportDeadlinesCsv, exportAllZip } from '@/services/exports'

interface Props {
  contractId: string
  contractTitle: string
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
})

const emit = defineEmits<{
  success: [format: string]
  error: [message: string]
}>()

const isOpen = ref(false)
const isExporting = ref(false)
const exportingFormat = ref<string | null>(null)
const menuRef = ref<HTMLElement | null>(null)

const menuItems = [
  {
    id: 'pdf',
    label: 'PDF Report',
    description: 'Full analysis report with formatting',
    icon: DocumentTextIcon,
    action: exportPdf,
  },
  {
    id: 'clauses',
    label: 'Clauses CSV',
    description: 'All extracted clauses in spreadsheet format',
    icon: TableCellsIcon,
    action: exportClausesCsv,
  },
  {
    id: 'deadlines',
    label: 'Deadlines CSV',
    description: 'All deadlines for calendar import',
    icon: CalendarDaysIcon,
    action: exportDeadlinesCsv,
  },
  {
    id: 'all',
    label: 'Export All (ZIP)',
    description: 'Summary + all CSVs in one download',
    icon: ArchiveBoxIcon,
    action: exportAllZip,
  },
]

const isDisabled = computed(() => props.disabled || isExporting.value)

function toggleMenu() {
  if (!isDisabled.value) {
    isOpen.value = !isOpen.value
  }
}

function closeMenu() {
  isOpen.value = false
}

async function handleExport(item: (typeof menuItems)[0]) {
  if (isExporting.value) return

  isExporting.value = true
  exportingFormat.value = item.id
  closeMenu()

  try {
    await item.action(props.contractId, props.contractTitle)
    emit('success', item.id)
  } catch (error) {
    const message = error instanceof Error ? error.message : 'Export failed. Please try again.'
    emit('error', message)
  } finally {
    isExporting.value = false
    exportingFormat.value = null
  }
}

// Close menu when clicking outside
function handleClickOutside(event: MouseEvent) {
  if (menuRef.value && !menuRef.value.contains(event.target as Node)) {
    closeMenu()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div ref="menuRef" class="relative">
    <!-- Trigger Button -->
    <button
      type="button"
      :disabled="isDisabled"
      :class="[
        'inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors',
        'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
        isDisabled
          ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600'
          : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700',
      ]"
      @click="toggleMenu"
      aria-haspopup="true"
      :aria-expanded="isOpen"
    >
      <ArrowDownTrayIcon
        :class="['h-5 w-5', isExporting ? 'animate-bounce' : '']"
        aria-hidden="true"
      />
      <span>{{ isExporting ? 'Exporting...' : 'Export' }}</span>
      <svg
        :class="['h-4 w-4 transition-transform', isOpen ? 'rotate-180' : '']"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        aria-hidden="true"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 z-10 mt-2 w-72 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800 dark:ring-gray-700"
        role="menu"
        aria-orientation="vertical"
      >
        <div class="py-1">
          <button
            v-for="item in menuItems"
            :key="item.id"
            type="button"
            :disabled="isExporting"
            class="w-full flex items-start gap-3 px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            role="menuitem"
            @click="handleExport(item)"
          >
            <component
              :is="item.icon"
              :class="[
                'h-5 w-5 mt-0.5 flex-shrink-0',
                exportingFormat === item.id
                  ? 'text-blue-500 animate-pulse'
                  : 'text-gray-400 dark:text-gray-500',
              ]"
              aria-hidden="true"
            />
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ item.label }}
                <span
                  v-if="exportingFormat === item.id"
                  class="ml-2 text-xs text-blue-500 animate-pulse"
                >
                  Downloading...
                </span>
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                {{ item.description }}
              </p>
            </div>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

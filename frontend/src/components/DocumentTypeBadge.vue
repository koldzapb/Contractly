<script setup lang="ts">
import { computed } from 'vue'
import type { DocumentType, DocumentCategory } from '@/types'

interface Props {
  type: DocumentType
  category?: DocumentCategory
  size?: 'sm' | 'md'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  category: undefined,
})

const config = computed(() => {
  const typeConfigs: Record<DocumentType, { label: string; category: DocumentCategory }> = {
    contract: { label: 'Contract', category: 'legal' },
    amendment: { label: 'Amendment', category: 'legal' },
    nda: { label: 'NDA', category: 'legal' },
    mou: { label: 'MOU', category: 'pre_contractual' },
    loi: { label: 'Letter of Intent', category: 'pre_contractual' },
    term_sheet: { label: 'Term Sheet', category: 'pre_contractual' },
    invoice: { label: 'Invoice', category: 'non_legal' },
    receipt: { label: 'Receipt', category: 'non_legal' },
    letter: { label: 'Letter', category: 'non_legal' },
    report: { label: 'Report', category: 'non_legal' },
    other: { label: 'Other', category: 'non_legal' },
    unknown: { label: 'Unknown', category: 'non_legal' },
  }

  const typeConfig = typeConfigs[props.type] || typeConfigs.unknown
  const category = props.category || typeConfig.category

  const categoryStyles: Record<
    DocumentCategory,
    { bgClass: string; textClass: string; iconClass: string }
  > = {
    legal: {
      bgClass: 'bg-green-100 dark:bg-green-900/30',
      textClass: 'text-green-800 dark:text-green-400',
      iconClass: 'text-green-600 dark:text-green-400',
    },
    pre_contractual: {
      bgClass: 'bg-yellow-100 dark:bg-yellow-900/30',
      textClass: 'text-yellow-800 dark:text-yellow-400',
      iconClass: 'text-yellow-600 dark:text-yellow-400',
    },
    non_legal: {
      bgClass: 'bg-red-100 dark:bg-red-900/30',
      textClass: 'text-red-800 dark:text-red-400',
      iconClass: 'text-red-600 dark:text-red-400',
    },
  }

  return {
    label: typeConfig.label,
    category,
    ...categoryStyles[category],
  }
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-sm',
  }
  return sizes[props.size]
})

const iconSizeClasses = computed(() => {
  const sizes = {
    sm: 'h-3 w-3',
    md: 'h-4 w-4',
  }
  return sizes[props.size]
})
</script>

<template>
  <span
    :class="[config.bgClass, config.textClass, sizeClasses]"
    class="inline-flex items-center gap-1.5 font-medium rounded-full"
  >
    <svg
      :class="[iconSizeClasses, config.iconClass]"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      aria-hidden="true"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
      />
    </svg>
    {{ config.label }}
  </span>
</template>

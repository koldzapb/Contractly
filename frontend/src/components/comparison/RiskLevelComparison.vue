<script setup lang="ts">
import { computed } from 'vue'
import type { RiskLevel } from '@/types'
import RiskBadge from '@/components/RiskBadge.vue'

interface Props {
  riskA: RiskLevel | null
  riskB: RiskLevel | null
  changed: boolean
}

const props = defineProps<Props>()

const riskOrder: Record<string, number> = {
  low: 1,
  medium: 2,
  high: 3,
}

const riskDirection = computed(() => {
  if (!props.riskA || !props.riskB) return null
  const orderA = riskOrder[props.riskA] ?? 0
  const orderB = riskOrder[props.riskB] ?? 0
  if (orderB > orderA) return 'increased'
  if (orderB < orderA) return 'decreased'
  return 'same'
})

const arrowConfig = computed(() => {
  if (riskDirection.value === 'increased') {
    return {
      class: 'text-red-500',
      path: 'M7 17l5-10 5 10',
      label: 'Risk increased',
    }
  }
  if (riskDirection.value === 'decreased') {
    return {
      class: 'text-green-500',
      path: 'M7 7l5 10 5-10',
      label: 'Risk decreased',
    }
  }
  return {
    class: 'text-gray-400',
    path: 'M5 12h14',
    label: 'No change',
  }
})
</script>

<template>
  <div class="flex items-center gap-4">
    <!-- Risk A -->
    <div class="flex flex-col items-center">
      <span class="text-xs text-gray-500 dark:text-gray-400 mb-1">Contract A</span>
      <RiskBadge v-if="riskA" :level="riskA" size="md" />
      <span v-else class="text-sm text-gray-400">N/A</span>
    </div>

    <!-- Arrow -->
    <div class="flex flex-col items-center">
      <svg
        :class="arrowConfig.class"
        class="w-8 h-8"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="2"
      >
        <path stroke-linecap="round" stroke-linejoin="round" :d="arrowConfig.path" />
      </svg>
      <span v-if="changed" :class="arrowConfig.class" class="text-xs font-medium mt-1">
        {{ arrowConfig.label }}
      </span>
    </div>

    <!-- Risk B -->
    <div class="flex flex-col items-center">
      <span class="text-xs text-gray-500 dark:text-gray-400 mb-1">Contract B</span>
      <RiskBadge v-if="riskB" :level="riskB" size="md" />
      <span v-else class="text-sm text-gray-400">N/A</span>
    </div>
  </div>
</template>

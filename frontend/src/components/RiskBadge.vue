<script setup lang="ts">
import { computed } from 'vue'
import type { RiskLevel } from '@/types'

interface Props {
  level: RiskLevel | null
  size?: 'sm' | 'md' | 'lg'
  showLabel?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  showLabel: true,
})

const config = computed(() => {
  if (!props.level) return null

  const configs = {
    low: {
      label: 'Low Risk',
      shortLabel: 'Low',
      bgClass: 'bg-green-100 dark:bg-green-900/30',
      textClass: 'text-green-800 dark:text-green-400',
      dotClass: 'bg-green-500',
    },
    medium: {
      label: 'Medium Risk',
      shortLabel: 'Medium',
      bgClass: 'bg-yellow-100 dark:bg-yellow-900/30',
      textClass: 'text-yellow-800 dark:text-yellow-400',
      dotClass: 'bg-yellow-500',
    },
    high: {
      label: 'High Risk',
      shortLabel: 'High',
      bgClass: 'bg-red-100 dark:bg-red-900/30',
      textClass: 'text-red-800 dark:text-red-400',
      dotClass: 'bg-red-500',
    },
  }

  return configs[props.level]
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-sm',
    lg: 'px-3 py-1.5 text-base',
  }
  return sizes[props.size]
})

const dotSizeClasses = computed(() => {
  const sizes = {
    sm: 'h-1.5 w-1.5',
    md: 'h-2 w-2',
    lg: 'h-2.5 w-2.5',
  }
  return sizes[props.size]
})
</script>

<template>
  <span
    v-if="config"
    :class="[config.bgClass, config.textClass, sizeClasses]"
    class="inline-flex items-center gap-1.5 font-medium rounded-full"
  >
    <span :class="[config.dotClass, dotSizeClasses]" class="rounded-full" />
    <span v-if="showLabel">{{ config.label }}</span>
    <span v-else>{{ config.shortLabel }}</span>
  </span>
</template>

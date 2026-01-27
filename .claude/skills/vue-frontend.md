# Vue Frontend Skill

## Context
Working in `frontend/` directory. See `/.ai/guidelines/coding-standards.md` for full standards.

## Quick Reference

### File Locations
- Components: `src/components/`
- Views: `src/views/`
- Stores: `src/stores/`
- Composables: `src/composables/`
- Services: `src/services/`
- Types: `src/types/`
- Tests: `tests/`

### Key Rules
1. `<script setup lang="ts">` always
2. No `any` types
3. Explicit interfaces for props/emits
4. `ref()` for primitives, `computed()` for derived
5. Pinia stores with composition syntax
6. `data-testid` for E2E tests
7. Heroicons for icons

### Component Template
```vue
<script setup lang="ts">
import { ref, computed } from 'vue'
import type { Contract } from '@/types'

interface Props {
  contract: Contract
}

const props = defineProps<Props>()

const emit = defineEmits<{
  select: [contract: Contract]
}>()
</script>

<template>
  <div data-testid="component-name">
    {{ props.contract.title }}
  </div>
</template>
```

### New Component Checklist
1. [ ] Props interface defined
2. [ ] Emits interface defined
3. [ ] TypeScript strict (no any)
4. [ ] `data-testid` added
5. [ ] Unit test created

### Commands
```bash
make npm-dev          # Start dev server
make npm-test         # Run tests
make npm-lint         # Lint code
make npm-build        # Production build
```

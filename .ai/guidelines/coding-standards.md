# Coding Standards

## PHP / Laravel

### Required Practices

```php
// ALWAYS use strict types
declare(strict_types=1);

// ALWAYS use Facades over helpers
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

$userId = Auth::id();      // Correct
// $userId = auth()->id(); // Wrong - don't use helpers
```

### Architecture Rules

**Controllers must be slim:**
```php
// Correct - delegate to service
class UploadContractController extends Controller
{
    public function __invoke(
        StoreContractRequest $request,
        ContractUploadService $service
    ): JsonResponse {
        $contract = $service->upload($request->validated());
        return ContractResource::make($contract)
            ->response()
            ->setStatusCode(201);
    }
}
```

**Services contain business logic:**
- Inject in method parameter if used once
- Inject in constructor if used multiple times

**Models:**
```php
// Enums in app/Enums/
enum ContractStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}

// Cast in model
#[ObservedBy(ContractObserver::class)]
class Contract extends Model
{
    protected $casts = [
        'status' => ContractStatus::class,
    ];
}
```

**API Responses:**
```php
// ALWAYS use Resources
return ContractResource::make($contract);
return ContractResource::collection($contracts);

// NEVER return raw models
// return response()->json($contract); // Wrong
```

### Code Patterns

```php
// Use match over switch
$label = match($status) {
    ContractStatus::PENDING => 'Waiting',
    ContractStatus::COMPLETED => 'Done',
};

// Eliminate single-use variables
return ContractResource::make($this->service->analyze($contract));
```

### Naming

| Type | Convention | Example |
|------|------------|---------|
| Class | PascalCase | `ContractAnalysis` |
| Method | camelCase | `analyzeContract()` |
| Variable | camelCase | `$contractId` |
| Constant | SCREAMING_SNAKE | `MAX_FILE_SIZE` |
| DB column | snake_case | `created_at` |
| Route | kebab-case | `/api/contracts/{id}` |

### Testing

- Use Pest syntax
- Arrange-Act-Assert pattern
- Mock external services

```php
it('uploads a contract', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('test.pdf', 1000, 'application/pdf');

    $response = $this->actingAs($user)
        ->postJson('/api/contracts', ['file' => $file]);

    $response->assertCreated();
});
```

---

## Vue / TypeScript

### Required Practices

```vue
<script setup lang="ts">
// ALWAYS Composition API with <script setup>
// ALWAYS TypeScript strict mode
// NEVER use 'any' type
// NEVER use Options API

import { ref, computed, onMounted } from 'vue'
import type { Contract } from '@/types'

// Explicit interfaces
interface Props {
  contract: Contract
  compact?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  compact: false
})

const emit = defineEmits<{
  select: [contract: Contract]
  delete: [id: string]
}>()
</script>
```

### Reactivity

```typescript
// ref for primitives
const isLoading = ref(false)
const count = ref(0)

// computed for derived state
const fullName = computed(() => `${first.value} ${last.value}`)

// toRefs when destructuring props
const { contractId } = toRefs(props)

// storeToRefs for Pinia
const { contracts, isLoading } = storeToRefs(store)
```

### Pinia Stores

```typescript
export const useContractStore = defineStore('contracts', () => {
  const contracts = ref<Contract[]>([])
  const isLoading = ref(false)

  const getById = computed(() =>
    (id: string) => contracts.value.find(c => c.id === id)
  )

  const fetchContracts = async () => {
    isLoading.value = true
    try {
      contracts.value = await api.contracts.list()
    } finally {
      isLoading.value = false
    }
  }

  return { contracts, isLoading, getById, fetchContracts }
})
```

### Naming

| Type | Convention | Example |
|------|------------|---------|
| Component | PascalCase | `ContractCard.vue` |
| Composable | use prefix | `useContracts.ts` |
| Store | camelCase | `contracts.ts` |
| Interface | PascalCase | `ContractAnalysis` |
| Event handler | handle prefix | `handleSubmit` |
| Boolean | is/has/can prefix | `isLoading` |

### Testing

- Add `data-testid` for E2E tests
- Use Vitest for unit tests
- Use Playwright for E2E

```typescript
it('emits select on click', async () => {
  const wrapper = mount(ContractCard, {
    props: { contract: mockContract }
  })
  await wrapper.trigger('click')
  expect(wrapper.emitted('select')).toBeTruthy()
})
```

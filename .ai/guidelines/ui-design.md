# UI Design Guidelines

## Colors (Tailwind)

### Primary Actions
- Button: `bg-primary-500 hover:bg-primary-600 text-white`
- Link: `text-primary-600 hover:text-primary-700`

### Risk Levels
```html
<span class="bg-gray-100 text-gray-700">None</span>
<span class="bg-green-50 text-green-700">Low</span>
<span class="bg-amber-50 text-amber-700">Medium</span>
<span class="bg-red-50 text-red-700">High</span>
```

### Status
```html
<span class="bg-gray-100 text-gray-700">Pending</span>
<span class="bg-blue-50 text-blue-700">Processing</span>
<span class="bg-green-50 text-green-700">Completed</span>
<span class="bg-red-50 text-red-700">Failed</span>
```

## Components

### Button Primary
```html
<button class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-md shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
  Label
</button>
```

### Button Secondary
```html
<button class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 font-medium border border-gray-300 rounded-md shadow-sm">
  Label
</button>
```

### Input
```html
<input class="w-full px-3 py-2 border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-100" />
```

### Card
```html
<div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
  Content
</div>
```

### Clickable Card
```html
<div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 cursor-pointer hover:border-primary-300 hover:shadow-md transition-all">
  Content
</div>
```

### Badge
```html
<span class="px-2 py-1 text-xs font-medium rounded-full bg-{color}-50 text-{color}-700">
  Label
</span>
```

## Loading States

### Spinner
```html
<svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
</svg>
```

### Skeleton
```html
<div class="animate-pulse">
  <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
  <div class="h-4 bg-gray-200 rounded w-1/2"></div>
</div>
```

## Layout

### Page Container
```html
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  Content
</div>
```

### Grid
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  Cards
</div>
```

## Icons

Use **Heroicons** (`@heroicons/vue/24/outline`):
- Document: `DocumentIcon`
- Upload: `ArrowUpTrayIcon`
- Delete: `TrashIcon`
- Warning: `ExclamationTriangleIcon`
- Error: `ExclamationCircleIcon`
- Success: `CheckCircleIcon`
- Clock/Deadline: `ClockIcon`
- Reminder: `BellIcon`

## Accessibility

- Focus: `focus:ring-2 focus:ring-primary-500 focus:ring-offset-2`
- Icon buttons: Add `aria-label`
- Tests: Add `data-testid` attributes
- Contrast: Minimum 4.5:1 for text

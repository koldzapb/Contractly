# Contractly - Design System

## Overview

This document defines the visual design standards for Contractly, ensuring consistency across all UI components.

---

## Color Palette

### Primary Colors
```css
--primary-50:  #eff6ff;  /* Lightest */
--primary-100: #dbeafe;
--primary-200: #bfdbfe;
--primary-300: #93c5fd;
--primary-400: #60a5fa;
--primary-500: #3b82f6;  /* Base - buttons, links */
--primary-600: #2563eb;  /* Hover states */
--primary-700: #1d4ed8;
--primary-800: #1e40af;
--primary-900: #1e3a8a;  /* Darkest */
```

### Semantic Colors
```css
/* Success - completed, safe clauses */
--success-50:  #f0fdf4;
--success-500: #22c55e;
--success-700: #15803d;

/* Warning - medium risk, attention needed */
--warning-50:  #fffbeb;
--warning-500: #f59e0b;
--warning-700: #b45309;

/* Danger - high risk, errors */
--danger-50:  #fef2f2;
--danger-500: #ef4444;
--danger-700: #b91c1c;

/* Info - informational, tips */
--info-50:  #f0f9ff;
--info-500: #0ea5e9;
--info-700: #0369a1;
```

### Neutral Colors
```css
--gray-50:  #f9fafb;  /* Background */
--gray-100: #f3f4f6;  /* Card background */
--gray-200: #e5e7eb;  /* Borders */
--gray-300: #d1d5db;  /* Disabled */
--gray-400: #9ca3af;  /* Placeholder text */
--gray-500: #6b7280;  /* Secondary text */
--gray-600: #4b5563;  /* Body text */
--gray-700: #374151;  /* Headings */
--gray-800: #1f2937;  /* Dark text */
--gray-900: #111827;  /* Darkest text */
```

### Risk Level Colors
```css
/* Used for risk badges and indicators */
--risk-none:   #6b7280;  /* Gray - no risk */
--risk-low:    #22c55e;  /* Green */
--risk-medium: #f59e0b;  /* Amber */
--risk-high:   #ef4444;  /* Red */
```

---

## Typography

### Font Family
```css
--font-sans: 'Inter', system-ui, -apple-system, sans-serif;
--font-mono: 'JetBrains Mono', 'Fira Code', monospace;
```

### Font Sizes
| Name | Size | Line Height | Usage |
|------|------|-------------|-------|
| `xs` | 12px | 16px | Labels, captions |
| `sm` | 14px | 20px | Secondary text, buttons |
| `base` | 16px | 24px | Body text |
| `lg` | 18px | 28px | Lead paragraphs |
| `xl` | 20px | 28px | Card titles |
| `2xl` | 24px | 32px | Section headings |
| `3xl` | 30px | 36px | Page titles |
| `4xl` | 36px | 40px | Hero headings |

### Font Weights
| Name | Weight | Usage |
|------|--------|-------|
| `normal` | 400 | Body text |
| `medium` | 500 | Buttons, labels |
| `semibold` | 600 | Headings, emphasis |
| `bold` | 700 | Strong emphasis |

---

## Spacing

Based on 4px grid system:

| Name | Value | Usage |
|------|-------|-------|
| `1` | 4px | Tight spacing |
| `2` | 8px | Icon gaps |
| `3` | 12px | Small padding |
| `4` | 16px | Standard padding |
| `5` | 20px | Medium spacing |
| `6` | 24px | Section gaps |
| `8` | 32px | Large spacing |
| `10` | 40px | Section padding |
| `12` | 48px | Page margins |
| `16` | 64px | Large sections |

---

## Border Radius

| Name | Value | Usage |
|------|-------|-------|
| `sm` | 4px | Badges, small elements |
| `md` | 6px | Buttons, inputs |
| `lg` | 8px | Cards |
| `xl` | 12px | Modals |
| `2xl` | 16px | Large cards |
| `full` | 9999px | Pills, avatars |

---

## Shadows

```css
/* Subtle - cards, dropdowns */
--shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);

/* Default - elevated cards */
--shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);

/* Medium - modals, popovers */
--shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);

/* Large - dialogs */
--shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
```

---

## Component Specifications

### Buttons

**Primary Button**
```html
<button class="
  px-4 py-2
  bg-primary-500 hover:bg-primary-600
  text-white font-medium
  rounded-md
  shadow-sm
  transition-colors duration-150
  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
  disabled:opacity-50 disabled:cursor-not-allowed
">
  Upload Contract
</button>
```

**Secondary Button**
```html
<button class="
  px-4 py-2
  bg-white hover:bg-gray-50
  text-gray-700 font-medium
  border border-gray-300
  rounded-md
  shadow-sm
  transition-colors duration-150
">
  Cancel
</button>
```

**Danger Button**
```html
<button class="
  px-4 py-2
  bg-danger-500 hover:bg-danger-600
  text-white font-medium
  rounded-md
">
  Delete
</button>
```

**Button Sizes**
| Size | Padding | Font |
|------|---------|------|
| `sm` | `px-3 py-1.5` | `text-sm` |
| `md` | `px-4 py-2` | `text-sm` |
| `lg` | `px-6 py-3` | `text-base` |

### Input Fields

```html
<input class="
  w-full px-3 py-2
  border border-gray-300 rounded-md
  text-gray-900 placeholder-gray-400
  focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500
  disabled:bg-gray-100 disabled:cursor-not-allowed
" />
```

**Error State**
```html
<input class="
  border-danger-500
  focus:ring-danger-500 focus:border-danger-500
" />
<p class="mt-1 text-sm text-danger-600">Error message here</p>
```

### Cards

**Basic Card**
```html
<div class="
  bg-white
  border border-gray-200
  rounded-lg
  shadow-sm
  p-6
">
  Content
</div>
```

**Interactive Card (clickable)**
```html
<div class="
  bg-white
  border border-gray-200
  rounded-lg
  shadow-sm
  p-6
  cursor-pointer
  hover:border-primary-300 hover:shadow-md
  transition-all duration-150
">
  Content
</div>
```

### Risk Badges

```html
<!-- None -->
<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
  None
</span>

<!-- Low -->
<span class="px-2 py-1 text-xs font-medium rounded-full bg-success-50 text-success-700">
  Low Risk
</span>

<!-- Medium -->
<span class="px-2 py-1 text-xs font-medium rounded-full bg-warning-50 text-warning-700">
  Medium Risk
</span>

<!-- High -->
<span class="px-2 py-1 text-xs font-medium rounded-full bg-danger-50 text-danger-700">
  High Risk
</span>
```

### Status Badges

```html
<!-- Pending -->
<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
  Pending
</span>

<!-- Processing -->
<span class="px-2 py-1 text-xs font-medium rounded-full bg-info-50 text-info-700">
  Processing
</span>

<!-- Completed -->
<span class="px-2 py-1 text-xs font-medium rounded-full bg-success-50 text-success-700">
  Completed
</span>

<!-- Failed -->
<span class="px-2 py-1 text-xs font-medium rounded-full bg-danger-50 text-danger-700">
  Failed
</span>
```

---

## Layout

### Page Structure
```
┌─────────────────────────────────────────────────────┐
│ Header (h-16, sticky)                               │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌─────────────────────────────────────────────┐   │
│  │ Page Content (max-w-7xl, mx-auto, px-4)     │   │
│  │                                             │   │
│  │                                             │   │
│  └─────────────────────────────────────────────┘   │
│                                                     │
├─────────────────────────────────────────────────────┤
│ Footer (optional)                                   │
└─────────────────────────────────────────────────────┘
```

### Breakpoints
| Name | Min Width | Usage |
|------|-----------|-------|
| `sm` | 640px | Mobile landscape |
| `md` | 768px | Tablet |
| `lg` | 1024px | Desktop |
| `xl` | 1280px | Large desktop |
| `2xl` | 1536px | Wide screens |

### Grid System
```html
<!-- Responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <!-- Cards -->
</div>
```

---

## Icons

Use **Heroicons** (https://heroicons.com/)

| Icon | Usage |
|------|-------|
| `DocumentIcon` | Contract file |
| `ExclamationTriangleIcon` | Warning, medium risk |
| `ExclamationCircleIcon` | Error, high risk |
| `CheckCircleIcon` | Success, completed |
| `ClockIcon` | Pending, deadline |
| `BellIcon` | Reminders |
| `TrashIcon` | Delete |
| `ArrowUpTrayIcon` | Upload |
| `MagnifyingGlassIcon` | Search |

---

## Animations

### Transitions
```css
/* Default transition */
transition-all duration-150 ease-in-out

/* Slow transition (modals) */
transition-all duration-300 ease-in-out
```

### Loading States

**Spinner**
```html
<svg class="animate-spin h-5 w-5 text-primary-500" viewBox="0 0 24 24">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
</svg>
```

**Skeleton Loading**
```html
<div class="animate-pulse">
  <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
  <div class="h-4 bg-gray-200 rounded w-1/2"></div>
</div>
```

---

## Accessibility

### Focus States
- All interactive elements must have visible focus indicators
- Use `focus:ring-2 focus:ring-primary-500 focus:ring-offset-2`

### Color Contrast
- Text on backgrounds must meet WCAG 2.1 AA (4.5:1 for normal text)
- Use `text-gray-700` or darker on white backgrounds

### Screen Reader
- Use `sr-only` class for visually hidden but accessible text
- Include `aria-label` on icon-only buttons

```html
<button aria-label="Delete contract">
  <TrashIcon class="h-5 w-5" />
</button>
```

---

## Tailwind Config

```javascript
// tailwind.config.js
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts}'],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
        },
        success: {
          50: '#f0fdf4',
          500: '#22c55e',
          700: '#15803d',
        },
        warning: {
          50: '#fffbeb',
          500: '#f59e0b',
          700: '#b45309',
        },
        danger: {
          50: '#fef2f2',
          500: '#ef4444',
          700: '#b91c1c',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace'],
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
```

# UI Design Skill

## Context
See `/.ai/guidelines/ui-design.md` for component patterns.
See `/docs/design-system.md` for full specifications.

## Quick Reference

### Risk Level Colors
```
None:   bg-gray-100 text-gray-700
Low:    bg-green-50 text-green-700
Medium: bg-amber-50 text-amber-700
High:   bg-red-50 text-red-700
```

### Status Colors
```
Pending:    bg-gray-100 text-gray-700
Processing: bg-blue-50 text-blue-700
Completed:  bg-green-50 text-green-700
Failed:     bg-red-50 text-red-700
```

### Common Classes

**Button Primary:**
`px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-md shadow-sm`

**Button Secondary:**
`px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-md`

**Input:**
`w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500`

**Card:**
`bg-white border border-gray-200 rounded-lg shadow-sm p-6`

**Badge:**
`px-2 py-1 text-xs font-medium rounded-full`

### Icons
Use Heroicons (`@heroicons/vue/24/outline`)

### Accessibility
- Focus: `focus:ring-2 focus:ring-primary-500 focus:ring-offset-2`
- Icon buttons: Add `aria-label`
- Tests: Add `data-testid`

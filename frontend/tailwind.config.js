/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Risk levels
        risk: {
          low: '#10B981',
          medium: '#F59E0B',
          high: '#EF4444',
        },
        // Contract status
        status: {
          draft: '#6B7280',
          processing: '#3B82F6',
          analyzed: '#10B981',
          failed: '#EF4444',
        },
      },
    },
  },
  plugins: [require('@tailwindcss/forms')],
}

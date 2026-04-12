/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}", // Dòng này rất quan trọng
  ],
  theme: {
    extend: {
      colors: { 
        primary: 'var(--color-primary)',
        'primary-glow': 'var(--color-primary-glow)'
      }
    },
  },
  plugins: [],
}
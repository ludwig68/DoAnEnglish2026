/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}", // Dòng này rất quan trọng
  ],
  theme: {
    extend: {
      theme: {
  extend: {
    colors: { primary: '#7AE582' }
  },
},
    },
  },
  plugins: [],
}
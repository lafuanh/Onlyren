/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        orange: {
          500: '#FF6B35', // Customize the orange color to match your design
        }
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'], // Default font family
        koulen: ['Koulen', 'cursive'], // Koulen font for specific usage
      },
    },
  },
  plugins: [],
}

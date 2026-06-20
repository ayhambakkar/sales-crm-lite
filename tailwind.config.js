/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './src/Views/**/*.php',
    './public/**/*.php'
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
      }
    }
  },
  plugins: []
};

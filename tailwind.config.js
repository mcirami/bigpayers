/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.jsx',
    './legacy/**/*.php',
  ],
  theme: {
    extend: {},
    screens: {
      'sm': '550px',
      'md': '768px',
      'lg': '992px',
      'xl': '1199px',
      '2xl': '1536px',
    },
    container: {
      center: true,
    },
  },
  plugins: [],
}


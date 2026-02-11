/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './storage/framework/views/*.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    // Include SCSS files if you write classes inside @apply
    './resources/css/.css',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
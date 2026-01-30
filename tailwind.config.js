/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'cherry': 'var(--cherry)',
        'black': 'var(--black)',
        'white': 'var(--white)',

        'gray-high': 'var(--gray-high)',
        'gray-middle': 'var(--gray-middle)',
        'gray-low': 'var(--gray-low)',
      },
    },
  },
  plugins: [],
}

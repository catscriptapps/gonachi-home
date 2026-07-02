/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/views/**/*.php',
    './resources/js/**/*.js',
    './src/**/*.php',
    './index.php',
  ],
  safelist: [
    // General utility classes that might be dynamically generated or used in the app
    'flex', 'hidden', 'block', 'inline-block', 'grid', 'gap-4', 'p-4', 'm-4',
    // Backgrounds
    'bg-blue-500/10',
    'bg-emerald-500/10',
    'bg-orange-500/10',
    'bg-amber-500/10',
    // Text colors
    'text-blue-500',
    'text-emerald-500',
    'text-orange-500',
    'text-amber-500',
  ],
  theme: {
    extend: {
      fontFamily: {
        'sans': ['Quicksand', 'sans-serif'],
      },
      colors: {
        // PRIMARY: Based off #397448 (Forest Green)
        primary: {
          '50': '#f4f8f5',
          '100': '#e6f0e8',
          '200': '#cfe1d3',
          '300': '#a9caa0',
          '400': '#7ba980',
          '500': '#397448', // Base color
          '600': '#427c51',
          '700': '#366442',
          '800': '#2d5036',
          '900': '#26432f',
          '950': '#122419',
        },
        // SECONDARY: Rebuilt based off #5b5d9c (Slate Blue)
        secondary: {
          '50': '#f4f5f9',
          '100': '#e7e9f2',
          '200': '#d3d6e7',
          '300': '#b2b7d5',
          '400': '#8a91bf',
          '500': '#5b5d9c', // New Base color
          '600': '#4c4d83',
          '700': '#3f3f6c',
          '800': '#36365b',
          '900': '#2f2f4d',
          '950': '#1b1b2d',
        },
      },
    },
  },
  plugins: [],
  darkMode: 'class',
}
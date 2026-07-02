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
        // PRIMARY: Based off #FC832B (Vibrant Orange)
        primary: {
          '50': '#fff8ed',
          '100': '#ffefd4',
          '200': '#ffdbab',
          '300': '#ffbe76',
          '400': '#fc832b', // Base color
          '500': '#f97316',
          '600': '#ea580c',
          '700': '#c2410c',
          '800': '#9a3412',
          '900': '#7c2d12',
          '950': '#431407',
        },
        // SECONDARY: Based off #284389 (Royal Navy)
        secondary: {
          '50': '#f1f5f9',
          '100': '#e2e8f0',
          '200': '#cfd9e7',
          '300': '#adbed8',
          '400': '#284389', // Base color
          '500': '#213872',
          '600': '#1b2d5c',
          '700': '#152349',
          '800': '#101a37',
          '900': '#0d152a',
          '950': '#070b16',
        },
      },
    },
  },
  plugins: [],
  darkMode: 'class',
}
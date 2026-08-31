const { colors, radii } = require('./theme/tokens.js');

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./app/**/*.{js,jsx,ts,tsx}', './components/**/*.{js,jsx,ts,tsx}'],
  darkMode: 'class',
  presets: [require('nativewind/preset')],
  theme: {
    extend: {
      colors: {
        cream: colors.cream,
        gold: colors.gold,
        navy: colors.navy,
        success: colors.semantic.success,
        danger: colors.semantic.danger,
        warning: colors.semantic.warning,
        info: colors.semantic.info,
      },
      borderRadius: radii,
    },
  },
  plugins: [],
};

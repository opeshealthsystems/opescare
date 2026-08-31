/**
 * OpesCare design tokens — gold/cream brand.
 * Plain CommonJS so tailwind.config.js (loaded directly by Node, no TS loader)
 * and the app's TypeScript code can both import the same source of truth.
 *
 * Gold + cream values sampled programmatically from the reference screens in
 * `Mobile app screens/` via tools/screens-triage/extract_accents.py (HSV hue
 * clustering across onboarding, login, and dashboard references). Text/semantic
 * values are visual-read approximations, refined per-screen by the pixel-diff
 * tool as each screen is built — see docs/superpowers/specs/2026-08-31-mobile-expo-app-design.md.
 */

const colors = {
  cream: {
    50: '#FFFDF9',
    100: '#FDF8F0', // primary app background — sampled, ~30-70% of every reference screen
    200: '#F7F0E3', // card / elevated surface on cream
    300: '#EADFC8', // borders, dividers
  },
  gold: {
    50: '#FBF3DF',
    100: '#F5E4B8',
    300: '#D9A73A',
    500: '#A6720B', // primary brand accent — sampled
    600: '#8B600A', // gradient-dark / pressed state — sampled
    700: '#6E4C08',
    900: '#4A3305',
  },
  navy: {
    text: '#1A2338', // headline / primary text
    secondary: '#5B6472', // body / secondary text
    muted: '#93989F', // placeholder / disabled text
  },
  white: '#FFFFFF',
  black: '#000000',
  semantic: {
    success: '#1E8E5A',
    successSurface: '#E4F5EC',
    danger: '#D64545',
    dangerSurface: '#FCE8E8',
    warning: '#B8860B',
    warningSurface: '#FBF3DF',
    info: '#2F6FED',
    infoSurface: '#E9F0FE',
  },
};

const spacing = { xs: 4, sm: 8, md: 12, lg: 16, xl: 20, '2xl': 24, '3xl': 32, '4xl': 40 };

const radii = { sm: 8, md: 12, lg: 16, xl: 20, full: 999 };

const typography = {
  fontFamily: { heading: 'System', body: 'System' },
  size: { xs: 12, sm: 14, md: 16, lg: 18, xl: 22, '2xl': 28, '3xl': 34 },
};

module.exports = { colors, spacing, radii, typography };

export const colors: {
  cream: { 50: string; 100: string; 200: string; 300: string };
  gold: { 50: string; 100: string; 300: string; 500: string; 600: string; 700: string; 900: string };
  navy: { text: string; secondary: string; muted: string };
  white: string;
  black: string;
  semantic: {
    success: string;
    successSurface: string;
    danger: string;
    dangerSurface: string;
    warning: string;
    warningSurface: string;
    info: string;
    infoSurface: string;
  };
};
export const spacing: Record<'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl', number>;
export const radii: Record<'sm' | 'md' | 'lg' | 'xl' | 'full', number>;
export const typography: {
  fontFamily: { heading: string; body: string };
  size: Record<'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl', number>;
};

import type { ResolvedAppearance } from '@/types';

export type { ResolvedAppearance };

export type ThemeState = {
    resolvedAppearance: () => ResolvedAppearance;
};

const applyLightTheme = (): void => {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.classList.remove('dark');
    document.documentElement.style.colorScheme = 'light';
};

export function initializeTheme(): () => void {
    applyLightTheme();

    if (typeof window !== 'undefined') {
        window.localStorage.removeItem('appearance');
        document.cookie = 'appearance=;path=/;max-age=0;SameSite=Lax';
    }

    return () => {};
}

export function themeState(): ThemeState {
    return {
        resolvedAppearance: () => 'light',
    };
}

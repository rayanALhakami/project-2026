import {
    localeInfo,
    locales,
    messages,
    type LocaleCode,
    type LocaleInfo,
    type MessageKey,
} from '@/lib/i18n/messages';

export { locales, localeInfo };
export type { LocaleCode, LocaleInfo, MessageKey };

const state = $state<{ code: LocaleCode }>({ code: 'ar' });

export function getLocale(): LocaleCode {
    return state.code;
}

export function dir(): 'rtl' | 'ltr' {
    return localeInfo(state.code).dir;
}

function applyDocument(): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.lang = state.code;
    document.documentElement.dir = localeInfo(state.code).dir;
}

export function setLocale(code: LocaleCode): void {
    state.code = code;

    if (typeof window !== 'undefined') {
        localStorage.setItem('locale', code);
        document.cookie = `locale=${code};path=/;max-age=31536000;SameSite=Lax`;
    }

    applyDocument();
}

export function initializeLocale(): void {
    if (typeof window === 'undefined') {
        return;
    }

    const stored = localStorage.getItem('locale') as LocaleCode | null;
    const server = document.documentElement.lang as LocaleCode;
    const isKnown = (code: LocaleCode | null): code is LocaleCode =>
        !!code && locales.some((locale) => locale.code === code);

    if (isKnown(stored)) {
        state.code = stored;
    } else if (isKnown(server)) {
        state.code = server;
    }

    applyDocument();
}

export function t(
    key: MessageKey,
    params?: Record<string, string | number>,
): string {
    const value =
        messages[state.code]?.[key] ?? messages.en?.[key] ?? (key as string);

    if (!params) {
        return value;
    }

    return Object.entries(params).reduce(
        (result, [name, replacement]) =>
            result.replaceAll(`{${name}}`, String(replacement)),
        value,
    );
}

export function formatNumber(value: number): string {
    return new Intl.NumberFormat(state.code).format(value);
}

export function formatDate(
    value: string,
    options: Intl.DateTimeFormatOptions = {
        day: 'numeric',
        month: 'long',
    },
): string {
    return new Intl.DateTimeFormat(state.code, options).format(new Date(value));
}

export function formatFullDate(value: Date = new Date()): string {
    return new Intl.DateTimeFormat(state.code, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(value);
}

/**
 * Hijri date through Intl so every locale renders its own numerals and
 * month names automatically (Arabic-Indic digits for `ar`).
 */
export function formatHijriDate(value: Date = new Date()): string {
    return new Intl.DateTimeFormat(`${state.code}-u-ca-islamic-umalqura`, {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(value);
}

export function formatList(items: string[]): string {
    if (items.length === 0) {
        return '';
    }

    return new Intl.ListFormat(state.code, {
        style: 'long',
        type: 'conjunction',
    }).format(items);
}

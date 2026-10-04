import { getLocale, type LocaleCode } from '@/lib/i18n.svelte';
import {
    speak as speakRoute,
    transcribe as transcribeRoute,
} from '@/routes/assistant';

export type VoiceGender = 'male' | 'female';

export interface VoiceLanguageOption {
    code: string;
    label: string;
    flag: string;
}

export const voiceLanguageOptions: VoiceLanguageOption[] = [
    { code: 'ar-SA', label: 'العربية', flag: '🇸🇦' },
    { code: 'en-US', label: 'English', flag: '🇬🇧' },
    { code: 'fr-FR', label: 'Français', flag: '🇫🇷' },
    { code: 'es-ES', label: 'Español', flag: '🇪🇸' },
    { code: 'de-DE', label: 'Deutsch', flag: '🇩🇪' },
    { code: 'ru-RU', label: 'Русский', flag: '🇷🇺' },
    { code: 'tr-TR', label: 'Türkçe', flag: '🇹🇷' },
    { code: 'zh-CN', label: '中文', flag: '🇨🇳' },
    { code: 'hi-IN', label: 'हिन्दी', flag: '🇮🇳' },
    { code: 'ur-PK', label: 'اردو', flag: '🇵🇰' },
];

const LANGUAGE_BY_LOCALE: Record<LocaleCode, string> = {
    ar: 'ar-SA',
    en: 'en-US',
    fr: 'fr-FR',
    es: 'es-ES',
    de: 'de-DE',
    ru: 'ru-RU',
    tr: 'tr-TR',
    zh: 'zh-CN',
    hi: 'hi-IN',
    ur: 'ur-PK',
};

const STORAGE_KEYS = {
    language: 'voice-language',
    gender: 'voice-gender',
    autoPlay: 'voice-autoplay',
} as const;

const RECORDING_MIME_TYPES = [
    'audio/webm;codecs=opus',
    'audio/webm',
    'audio/mp4',
    'audio/ogg',
];

const ERRORS = {
    unsupported:
        'متصفحك لا يدعم تسجيل الصوت. جرّب متصفحاً حديثاً مثل كروم أو إيدج.',
    permission:
        'تعذر الوصول إلى الميكروفون. اسمح بالوصول للخدمة وحاول مرة أخرى.',
    emptyRecording: 'لم يتم تسجيل أي صوت. حاول مرة أخرى.',
    transcribe: 'تعذر تفريغ التسجيل الصوتي. حاول مرة أخرى.',
    speak: 'تعذر تشغيل الصوت. حاول مرة أخرى.',
} as const;

export const voice = $state({
    recording: false,
    processing: false,
    speaking: false,
    autoPlay: true,
    language: languageTagForLocale(getLocale()),
    voiceGender: 'female' as VoiceGender,
    error: null as string | null,
});

let mediaRecorder: MediaRecorder | null = null;
let mediaStream: MediaStream | null = null;
let recordedChunks: Blob[] = [];
let currentAudio: HTMLAudioElement | null = null;
let currentAudioUrl: string | null = null;
let currentUtterance: SpeechSynthesisUtterance | null = null;
let speechSequence = 0;

export function initializeVoice(): void {
    if (typeof window === 'undefined') {
        return;
    }

    const storedLanguage = window.localStorage.getItem(STORAGE_KEYS.language);

    if (
        storedLanguage !== null &&
        voiceLanguageOptions.some((option) => option.code === storedLanguage)
    ) {
        voice.language = storedLanguage;
    } else {
        voice.language = languageTagForLocale(getLocale());
    }

    const storedGender = window.localStorage.getItem(STORAGE_KEYS.gender);

    if (storedGender === 'male' || storedGender === 'female') {
        voice.voiceGender = storedGender;
    }

    const storedAutoPlay = window.localStorage.getItem(STORAGE_KEYS.autoPlay);

    if (storedAutoPlay !== null) {
        voice.autoPlay = storedAutoPlay === 'true';
    }
}

export function languageTagForLocale(code: LocaleCode): string {
    return LANGUAGE_BY_LOCALE[code] ?? 'ar-SA';
}

export function setVoiceLanguage(code: string): void {
    if (!voiceLanguageOptions.some((option) => option.code === code)) {
        return;
    }

    voice.language = code;

    if (typeof window !== 'undefined') {
        window.localStorage.setItem(STORAGE_KEYS.language, code);
    }
}

export function setVoiceGender(gender: VoiceGender): void {
    voice.voiceGender = gender;

    if (typeof window !== 'undefined') {
        window.localStorage.setItem(STORAGE_KEYS.gender, gender);
    }
}

export function setVoiceAutoPlay(enabled: boolean): void {
    voice.autoPlay = enabled;

    if (typeof window !== 'undefined') {
        window.localStorage.setItem(STORAGE_KEYS.autoPlay, String(enabled));
    }
}

export function isRecordingSupported(): boolean {
    return (
        typeof window !== 'undefined' &&
        typeof navigator !== 'undefined' &&
        typeof navigator.mediaDevices?.getUserMedia === 'function' &&
        typeof MediaRecorder !== 'undefined'
    );
}

export async function startRecording(): Promise<void> {
    if (voice.recording || voice.processing) {
        return;
    }

    if (!isRecordingSupported()) {
        voice.error = ERRORS.unsupported;
        throw new Error(ERRORS.unsupported);
    }

    voice.error = null;

    let stream: MediaStream;

    try {
        stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    } catch {
        voice.error = ERRORS.permission;
        throw new Error(ERRORS.permission);
    }

    mediaStream = stream;
    recordedChunks = [];

    try {
        mediaRecorder = createRecorder(stream);
    } catch {
        releaseStream();
        voice.error = ERRORS.unsupported;
        throw new Error(ERRORS.unsupported);
    }

    mediaRecorder.start();
    voice.recording = true;
}

export async function stopRecording(language?: string): Promise<string | null> {
    const recorder = mediaRecorder;
    mediaRecorder = null;
    voice.recording = false;

    const blob =
        recorder !== null && recorder.state !== 'inactive'
            ? await stopRecorder(recorder)
            : null;

    releaseStream();

    if (blob === null) {
        voice.error ??= ERRORS.emptyRecording;

        return null;
    }

    voice.processing = true;

    try {
        return await transcribeBlob(blob, language ?? voice.language);
    } finally {
        voice.processing = false;
    }
}

export function cancelRecording(): void {
    voice.recording = false;

    if (mediaRecorder !== null && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }

    mediaRecorder = null;
    recordedChunks = [];
    releaseStream();
}

export async function speak(text: string, language?: string): Promise<void> {
    const value = text.trim();

    if (value === '') {
        return;
    }

    const tag = language ?? voice.language;

    stopSpeaking();

    const sequence = ++speechSequence;
    voice.error = null;

    try {
        const response = await fetch(speakRoute.url(), {
            method: 'POST',
            credentials: 'same-origin',
            headers: requestHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({
                text: value,
                voice: voice.voiceGender,
                language: tag,
            }),
        });

        if (sequence !== speechSequence) {
            return;
        }

        if (!response.ok) {
            voice.error = await errorMessageFrom(response, ERRORS.speak);
            speakWithBrowser(value, tag);

            return;
        }

        const blob = await response.blob();

        if (sequence !== speechSequence) {
            return;
        }

        await playBlob(blob, sequence, value, tag);
    } catch {
        if (sequence === speechSequence) {
            speakWithBrowser(value, tag);
        }
    }
}

export function stopSpeaking(): void {
    speechSequence += 1;

    if (currentAudio !== null) {
        currentAudio.pause();
        finishAudio(currentAudio);
    }

    currentUtterance = null;

    if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
        window.speechSynthesis.cancel();
    }

    voice.speaking = false;
}

function createRecorder(stream: MediaStream): MediaRecorder {
    const mimeType = supportedMimeType();
    const recorder =
        mimeType !== null
            ? new MediaRecorder(stream, { mimeType })
            : new MediaRecorder(stream);

    recorder.addEventListener('dataavailable', (event) => {
        if (event.data.size > 0) {
            recordedChunks.push(event.data);
        }
    });

    recorder.addEventListener('error', () => {
        voice.error = ERRORS.emptyRecording;
        voice.recording = false;
        releaseStream();
    });

    return recorder;
}

function supportedMimeType(): string | null {
    if (typeof MediaRecorder.isTypeSupported !== 'function') {
        return null;
    }

    return (
        RECORDING_MIME_TYPES.find((type) =>
            MediaRecorder.isTypeSupported(type),
        ) ?? null
    );
}

function stopRecorder(recorder: MediaRecorder): Promise<Blob | null> {
    return new Promise((resolve) => {
        recorder.addEventListener(
            'stop',
            () => {
                if (recordedChunks.length === 0) {
                    resolve(null);

                    return;
                }

                const type =
                    recorder.mimeType ||
                    recordedChunks[0]?.type ||
                    'audio/webm';

                resolve(new Blob(recordedChunks, { type }));
            },
            { once: true },
        );

        recorder.stop();
    });
}

async function transcribeBlob(
    blob: Blob,
    language: string,
): Promise<string | null> {
    const formData = new FormData();
    formData.append('audio', blob, `recording.${extensionFor(blob.type)}`);
    formData.append('language', language);

    try {
        const response = await fetch(transcribeRoute.url(), {
            method: 'POST',
            credentials: 'same-origin',
            headers: requestHeaders(),
            body: formData,
        });

        if (!response.ok) {
            voice.error = await errorMessageFrom(response, ERRORS.transcribe);

            return null;
        }

        const payload = (await response.json()) as { text?: unknown };

        if (typeof payload.text === 'string') {
            return payload.text;
        }

        voice.error = ERRORS.transcribe;

        return null;
    } catch {
        voice.error = ERRORS.transcribe;

        return null;
    }
}

async function playBlob(
    blob: Blob,
    sequence: number,
    fallbackText: string,
    language: string,
): Promise<void> {
    const url = URL.createObjectURL(blob);
    const audio = new Audio(url);

    currentAudio = audio;
    currentAudioUrl = url;
    voice.speaking = true;

    audio.addEventListener('ended', () => finishAudio(audio), {
        once: true,
    });

    audio.addEventListener(
        'error',
        () => {
            finishAudio(audio);

            if (sequence === speechSequence) {
                speakWithBrowser(fallbackText, language);
            }
        },
        { once: true },
    );

    try {
        await audio.play();
    } catch {
        finishAudio(audio);

        if (sequence === speechSequence) {
            speakWithBrowser(fallbackText, language);
        }
    }
}

function finishAudio(audio: HTMLAudioElement): void {
    if (currentAudio !== audio) {
        return;
    }

    currentAudio = null;
    voice.speaking = false;

    if (currentAudioUrl !== null) {
        URL.revokeObjectURL(currentAudioUrl);
        currentAudioUrl = null;
    }
}

function speakWithBrowser(text: string, language: string): void {
    if (typeof window === 'undefined' || !('speechSynthesis' in window)) {
        voice.error = ERRORS.speak;
        voice.speaking = false;

        return;
    }

    window.speechSynthesis.cancel();

    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = language;

    const finish = (): void => {
        if (currentUtterance === utterance) {
            currentUtterance = null;
            voice.speaking = false;
        }
    };

    utterance.onend = finish;
    utterance.onerror = finish;

    currentUtterance = utterance;
    voice.speaking = true;

    try {
        window.speechSynthesis.speak(utterance);
    } catch {
        finish();
        voice.error = ERRORS.speak;
    }
}

function releaseStream(): void {
    mediaStream?.getTracks().forEach((track) => track.stop());
    mediaStream = null;
}

function requestHeaders(
    extra: Record<string, string> = {},
): Record<string, string> {
    const headers: Record<string, string> = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...extra,
    };

    const token = xsrfToken();

    if (token !== null) {
        headers['X-XSRF-TOKEN'] = token;
    }

    return headers;
}

function xsrfToken(): string | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : null;
}

async function errorMessageFrom(
    response: Response,
    fallback: string,
): Promise<string> {
    try {
        const payload = (await response.json()) as {
            error?: unknown;
            message?: unknown;
            errors?: Record<string, unknown>;
        };

        if (typeof payload.error === 'string' && payload.error !== '') {
            return payload.error;
        }

        if (payload.errors !== undefined) {
            for (const value of Object.values(payload.errors)) {
                if (Array.isArray(value) && typeof value[0] === 'string') {
                    return value[0];
                }

                if (typeof value === 'string' && value !== '') {
                    return value;
                }
            }
        }

        if (typeof payload.message === 'string' && payload.message !== '') {
            return payload.message;
        }
    } catch {
        // Fall back to the provided message below.
    }

    return fallback;
}

function extensionFor(mimeType: string): string {
    const type = mimeType.split(';')[0].trim().toLowerCase();

    switch (type) {
        case 'audio/mp4':
            return 'm4a';
        case 'audio/ogg':
            return 'ogg';
        case 'audio/wav':
        case 'audio/wave':
        case 'audio/x-wav':
            return 'wav';
        case 'audio/mpeg':
            return 'mp3';
        default:
            return 'webm';
    }
}

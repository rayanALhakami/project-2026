/**
 * Lightweight browser speech helpers (no backend, no dependencies).
 *
 * - `speak` uses the Web Speech API SpeechSynthesis (text to speech).
 * - `createDictation` uses SpeechRecognition (speech to text) where available.
 *
 * All helpers fail gracefully when the browser does not support the API.
 */
interface RecognitionAlternative {
    readonly transcript: string;
}
interface RecognitionResult {
    readonly length: number;
    [index: number]: RecognitionAlternative;
}
interface RecognitionEvent {
    readonly results: {
        readonly length: number;
        [index: number]: RecognitionResult;
    };
}
interface RecognitionInstance {
    lang: string;
    continuous: boolean;
    interimResults: boolean;
    onresult: ((event: RecognitionEvent) => void) | null;
    onend: (() => void) | null;
    onerror: (() => void) | null;
    start(): void;
    stop(): void;
}
type RecognitionConstructor = new () => RecognitionInstance;

export interface Dictation {
    start(): void;
    stop(): void;
}

function recognitionConstructor(): RecognitionConstructor | null {
    if (typeof window === 'undefined') {
        return null;
    }

    const scope = window as unknown as {
        SpeechRecognition?: RecognitionConstructor;
        webkitSpeechRecognition?: RecognitionConstructor;
    };

    return scope.SpeechRecognition ?? scope.webkitSpeechRecognition ?? null;
}

export function isSpeechSupported(): boolean {
    return typeof window !== 'undefined' && 'speechSynthesis' in window;
}

export function isDictationSupported(): boolean {
    return recognitionConstructor() !== null;
}

export function speak(text: string, lang: string): boolean {
    if (!isSpeechSupported() || text.trim() === '') {
        return false;
    }

    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = lang;
    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(utterance);

    return true;
}

export function createDictation(
    lang: string,
    onResult: (text: string) => void,
    onEnd: () => void,
): Dictation | null {
    const Constructor = recognitionConstructor();

    if (Constructor === null) {
        return null;
    }

    const recognition = new Constructor();
    recognition.lang = lang;
    recognition.continuous = false;
    recognition.interimResults = false;

    recognition.onresult = (event) => {
        let transcript = '';

        for (let index = 0; index < event.results.length; index++) {
            transcript += event.results[index][0]?.transcript ?? '';
        }

        onResult(transcript.trim());
    };
    recognition.onend = onEnd;
    recognition.onerror = onEnd;

    return {
        start: () => recognition.start(),
        stop: () => recognition.stop(),
    };
}

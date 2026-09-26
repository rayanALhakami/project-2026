import AssistantController from '@/actions/App/Http/Controllers/AssistantController';
import type { AssistantFrame, AssistantHistoryItem } from '@/types';

export type AssistantStreamPayload = {
    message: string;
    history: AssistantHistoryItem[];
    session_id: string;
};

function csrfToken(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

/**
 * POST the conversation to the assistant and dispatch each SSE frame as it arrives.
 */
export async function streamAssistant(
    payload: AssistantStreamPayload,
    onFrame: (frame: AssistantFrame) => void,
    signal: AbortSignal,
): Promise<void> {
    const { url, method } = AssistantController.stream();

    const response = await fetch(url, {
        method: method.toUpperCase(),
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'text/event-stream',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(payload),
        signal,
    });

    if (!response.ok || response.body === null) {
        throw new Error(
            `Assistant request failed with status ${response.status}.`,
        );
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let buffer = '';

    for (;;) {
        const { value, done } = await reader.read();

        if (done) {
            break;
        }

        buffer += decoder.decode(value, { stream: true });

        let boundary = buffer.indexOf('\n\n');

        while (boundary !== -1) {
            const chunk = buffer.slice(0, boundary);
            buffer = buffer.slice(boundary + 2);
            boundary = buffer.indexOf('\n\n');

            for (const line of chunk.split('\n')) {
                if (!line.startsWith('data:')) {
                    continue;
                }

                const data = line.slice(5).trim();

                if (data === '' || data === '[DONE]') {
                    continue;
                }

                try {
                    onFrame(JSON.parse(data) as AssistantFrame);
                } catch {
                    // Ignore malformed frames rather than breaking the stream.
                }
            }
        }
    }
}

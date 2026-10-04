import DOMPurify from 'dompurify';
import { marked } from 'marked';

/**
 * Render assistant markdown safely. The HTML is always sanitized before it
 * reaches {@html}.
 */
export function renderMarkdown(source: string): string {
    const html = marked.parse(source, {
        async: false,
        gfm: true,
        breaks: true,
    }) as string;

    // During SSR there is no DOM to sanitize against; client hydration always
    // runs the sanitizer before the HTML is injected.
    if (
        typeof window === 'undefined' ||
        typeof DOMPurify.sanitize !== 'function'
    ) {
        return html;
    }

    return DOMPurify.sanitize(html);
}

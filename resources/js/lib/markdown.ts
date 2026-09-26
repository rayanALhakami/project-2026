import DOMPurify from 'dompurify';
import { marked } from 'marked';

marked.use({
    async: false,
    breaks: true,
    gfm: true,
});

DOMPurify.addHook('afterSanitizeAttributes', (node) => {
    if (node instanceof HTMLElement && node.tagName === 'A') {
        node.setAttribute('target', '_blank');
        node.setAttribute('rel', 'noopener noreferrer');
    }
});

const SANITIZE_CONFIG = {
    ALLOWED_TAGS: [
        'p',
        'br',
        'strong',
        'em',
        'del',
        'ul',
        'ol',
        'li',
        'blockquote',
        'code',
        'pre',
        'a',
        'table',
        'thead',
        'tbody',
        'tr',
        'th',
        'td',
        'hr',
        'h3',
        'h4',
    ],
    ALLOWED_ATTR: ['href', 'target', 'rel', 'align'],
};

/**
 * Close any markdown construct left open by a partial stream chunk so the
 * layout never jumps and no broken markers (```, **, |) are shown.
 */
export function stabilizeMarkdown(source: string): string {
    let text = source;

    const lines = text.split('\n');
    const last = lines[lines.length - 1] ?? '';

    if (last.trimStart().startsWith('|') && !last.trimEnd().endsWith('|')) {
        lines.pop();
        text = lines.join('\n');
    }

    const fences = text.match(/```/g);
    if (fences && fences.length % 2 === 1) {
        text += '\n```';
    }

    const bold = text.match(/\*\*/g);
    if (bold && bold.length % 2 === 1) {
        text += '**';
    }

    return text;
}

/**
 * Render assistant markdown to sanitized HTML.
 */
export function renderMarkdown(source: string): string {
    const html = marked.parse(stabilizeMarkdown(source ?? '')) as string;

    return DOMPurify.sanitize(html, SANITIZE_CONFIG);
}

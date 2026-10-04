<script lang="ts">
    import { t } from '@/lib/i18n.svelte';

    let {
        class: className = '',
        label,
    }: { class?: string; label?: string } = $props();
</script>

<span
    class="saudi-flag {className}"
    role="img"
    aria-label={label ?? t('common.saudiFlag')}
>
    <span class="saudi-flag-text" aria-hidden="true"
        >لا إله إلا الله محمد رسول الله</span
    >
    <svg class="saudi-flag-sword" viewBox="0 0 120 14" aria-hidden="true">
        <g fill="currentColor">
            <path d="M3 7 L86 5 L86 9 Z" />
            <rect x="86" y="3" width="3.5" height="8" rx="1" />
            <rect x="92" y="6" width="16" height="2" rx="1" />
            <circle cx="112" cy="7" r="2.2" />
        </g>
    </svg>
</span>

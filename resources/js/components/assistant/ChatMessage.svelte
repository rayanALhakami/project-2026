<script lang="ts">
    import CircleAlert from '@lucide/svelte/icons/circle-alert';
    import ToolCallCard from '@/components/assistant/ToolCallCard.svelte';
    import TypingIndicator from '@/components/assistant/TypingIndicator.svelte';
    import { renderMarkdown } from '@/lib/markdown';
    import type { ChatMessage } from '@/types';

    let {
        message,
        streaming = false,
    }: {
        message: ChatMessage;
        streaming?: boolean;
    } = $props();
</script>

{#if message.role === 'user'}
    <div class="flex justify-end">
        <div
            dir="auto"
            class="max-w-[85%] rounded-[18px] bg-primary px-4 py-2.5 text-[15px] whitespace-pre-wrap text-primary-foreground"
        >
            {message.content}
        </div>
    </div>
{:else}
    <div class="flex justify-start">
        <div class="flex w-full max-w-[92%] flex-col gap-2">
            {#each message.toolCalls as call (call.id)}
                <ToolCallCard {call} />
            {/each}

            {#if message.content}
                <div
                    dir="auto"
                    class="md-body rounded-[18px] border border-black/[0.06] bg-card px-4 py-3 text-[15px] leading-relaxed text-foreground"
                >
                    {@html renderMarkdown(message.content)}
                </div>
            {:else if streaming}
                <div
                    class="w-fit rounded-[18px] border border-black/[0.06] bg-card px-4 py-2"
                >
                    <TypingIndicator />
                </div>
            {/if}

            {#if message.error}
                <div
                    class="flex items-start gap-2 rounded-2xl bg-destructive/10 px-4 py-3 text-sm text-destructive"
                >
                    <CircleAlert class="mt-0.5 size-4 shrink-0" />
                    <p>{message.error}</p>
                </div>
            {/if}
        </div>
    </div>
{/if}

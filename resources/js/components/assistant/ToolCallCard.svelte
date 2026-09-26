<script lang="ts">
    import Check from '@lucide/svelte/icons/check';
    import CircleAlert from '@lucide/svelte/icons/circle-alert';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Plus from '@lucide/svelte/icons/plus';
    import Rows3 from '@lucide/svelte/icons/rows-3';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import type { ToolCallView } from '@/types';

    let { call }: { call: ToolCallView } = $props();

    const TOOL_LABELS: Record<string, string> = {
        ListTransactions: 'عرض العمليات',
        CreateTransactions: 'إضافة عمليات',
        UpdateTransactions: 'تعديل عمليات',
        DeleteTransactions: 'حذف عمليات',
    };

    const ARG_LABELS: Record<string, string> = {
        id: 'المعرّف',
        ids: 'المعرّفات',
        date: 'التاريخ',
        date_from: 'من تاريخ',
        date_to: 'إلى تاريخ',
        type: 'النوع',
        category: 'الفئة',
        category_id: 'الفئة',
        amount: 'المبلغ',
        min_amount: 'أدنى مبلغ',
        max_amount: 'أعلى مبلغ',
        title: 'الوصف',
        search: 'بحث',
        sort: 'الترتيب',
        limit: 'الحد الأقصى',
        transactions: 'العمليات',
        updates: 'التعديلات',
    };

    const TYPE_LABELS: Record<string, string> = {
        expense: 'مصروف',
        income: 'دخل',
    };

    const label = $derived(TOOL_LABELS[call.name] ?? call.name);
    const statusLabel = $derived(
        call.status === 'running'
            ? 'جارٍ التنفيذ'
            : call.status === 'success'
              ? 'تم'
              : 'فشل',
    );

    function formatValue(key: string, value: unknown): string {
        if (value === null || value === undefined || value === '') {
            return '—';
        }

        if (key === 'type' && typeof value === 'string') {
            return TYPE_LABELS[value] ?? value;
        }

        if (Array.isArray(value)) {
            if (value.length === 0) {
                return '—';
            }

            return value
                .map((item) =>
                    typeof item === 'object' && item !== null
                        ? Object.entries(item as Record<string, unknown>)
                              .map(
                                  ([itemKey, itemValue]) =>
                                      `${ARG_LABELS[itemKey] ?? itemKey}: ${formatValue(itemKey, itemValue)}`,
                              )
                              .join(' · ')
                        : formatValue(key, item),
                )
                .join('\n');
        }

        if (typeof value === 'object') {
            return Object.entries(value as Record<string, unknown>)
                .map(([itemKey, itemValue]) => `${ARG_LABELS[itemKey] ?? itemKey}: ${formatValue(itemKey, itemValue)}`)
                .join(' · ');
        }

        return String(value);
    }

    const entries = $derived(
        Object.entries(call.arguments ?? {}).map(([key, value]) => ({
            key,
            label: ARG_LABELS[key] ?? key,
            value: formatValue(key, value),
        })),
    );
</script>

<div class="rounded-2xl border border-black/[0.06] bg-secondary/60 p-3">
    <div class="flex items-center gap-2">
        <span
            class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary"
        >
            {#if call.name === 'CreateTransactions'}
                <Plus class="size-4" />
            {:else if call.name === 'UpdateTransactions'}
                <Pencil class="size-4" />
            {:else if call.name === 'DeleteTransactions'}
                <Trash2 class="size-4" />
            {:else}
                <Rows3 class="size-4" />
            {/if}
        </span>

        <span class="text-sm font-medium text-foreground">{label}</span>

        <span
            class="ms-auto inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs {call.status ===
            'running'
                ? 'bg-primary/10 text-primary'
                : call.status === 'success'
                  ? 'bg-[#34c759]/15 text-[#1c7c33]'
                  : 'bg-destructive/10 text-destructive'}"
        >
            {#if call.status === 'running'}
                <LoaderCircle class="size-3 animate-spin" />
            {:else if call.status === 'success'}
                <Check class="size-3" />
            {:else}
                <CircleAlert class="size-3" />
            {/if}
            {statusLabel}
        </span>
    </div>

    {#if call.summary}
        <p class="mt-2 text-sm text-muted-foreground">{call.summary}</p>
    {/if}

    {#if entries.length > 0}
        <details class="mt-2 group">
            <summary
                class="cursor-pointer list-none text-xs font-medium text-primary select-none"
            >
                عرض التفاصيل
            </summary>
            <dl class="mt-2 flex flex-col gap-1">
                {#each entries as entry (entry.key)}
                    <div class="flex gap-2 text-xs">
                        <dt class="shrink-0 text-muted-foreground">{entry.label}:</dt>
                        <dd class="whitespace-pre-wrap text-foreground">{entry.value}</dd>
                    </div>
                {/each}
            </dl>
        </details>
    {/if}
</div>

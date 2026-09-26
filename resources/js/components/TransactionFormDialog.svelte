<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import type { Category, Transaction, TransactionType } from '@/types';
    import { toUrl } from '@/lib/utils';
    import transactions from '@/routes/transactions';

    let {
        open = $bindable(false),
        categories = [],
        transaction = null,
        defaultType = 'expense',
    }: {
        open?: boolean;
        categories: Category[];
        transaction?: Transaction | null;
        defaultType?: TransactionType;
    } = $props();

    const today = new Date().toISOString().slice(0, 10);

    const form = useForm(() => ({
        title: transaction?.title ?? '',
        amount: transaction ? String(transaction.amount) : '',
        type: (transaction?.type ?? defaultType) as TransactionType,
        category_id: transaction?.category?.id ?? '',
        date: transaction?.date ?? today,
    }));

    form.transform((data) => {
        const title = data.title?.trim() ?? '';

        return {
            ...data,
            title: title === '' ? null : title,
            amount:
                data.amount === '' || data.amount === null || data.amount === undefined
                    ? 0
                    : Number(data.amount),
            category_id: data.category_id === '' ? null : Number(data.category_id),
        };
    });

    $effect(() => {
        if (!open) {
            return;
        }
        untrack(() => {
            form.reset();
            form.clearErrors();
        });
    });

    const filteredCategories = $derived(
        categories.filter((category) => category.type === form.type),
    );

    function setType(next: TransactionType): void {
        if (form.type === next) {
            return;
        }

        form.type = next;
        form.category_id = '';
    }

    function submit(): void {
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                open = false;
            },
        };

        if (transaction) {
            form.put(toUrl(transactions.update({ transaction: transaction.id })), options);
        } else {
            form.post(toUrl(transactions.store()), options);
        }
    }
</script>

{#if open}
    <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
        <button
            type="button"
            class="fixed inset-0 cursor-default bg-black/40"
            onclick={() => (open = false)}
            aria-label="إغلاق"
        ></button>

        <form
            onsubmit={(event) => {
                event.preventDefault();
                submit();
            }}
            class="relative flex w-full max-w-md flex-col gap-5 rounded-t-[18px] bg-card p-6 shadow-xl sm:rounded-[18px]"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-foreground">
                    {transaction ? 'تعديل معاملة' : 'إضافة معاملة'}
                </h2>
                <button
                    type="button"
                    onclick={() => (open = false)}
                    class="text-muted-foreground transition-colors hover:text-foreground"
                    aria-label="إغلاق"
                >
                    ✕
                </button>
            </div>

            <div class="grid grid-cols-2 gap-2 rounded-full bg-secondary p-1">
                <button
                    type="button"
                    onclick={() => setType('expense')}
                    class="rounded-full px-4 py-2 text-sm font-medium transition-colors {form.type ===
                    'expense'
                        ? 'bg-card text-foreground shadow-xs'
                        : 'text-muted-foreground'}"
                >
                    مصروف
                </button>
                <button
                    type="button"
                    onclick={() => setType('income')}
                    class="rounded-full px-4 py-2 text-sm font-medium transition-colors {form.type ===
                    'income'
                        ? 'bg-card text-foreground shadow-xs'
                        : 'text-muted-foreground'}"
                >
                    دخل
                </button>
            </div>

            {#if form.errors.type}
                <p class="text-xs text-destructive">{form.errors.type}</p>
            {/if}

            <div class="grid gap-1.5">
                <label for="transaction-amount" class="text-sm text-muted-foreground">
                    المبلغ
                </label>
                <input
                    id="transaction-amount"
                    type="number"
                    inputmode="decimal"
                    step="0.01"
                    min="0.01"
                    placeholder="0.00"
                    bind:value={form.amount}
                    class="h-11 rounded-xl border border-border bg-background px-4 text-foreground outline-none focus:border-primary"
                />
                {#if form.errors.amount}
                    <p class="text-xs text-destructive">{form.errors.amount}</p>
                {/if}
            </div>

            <div class="grid gap-1.5">
                <label for="transaction-title" class="text-sm text-muted-foreground">
                    الوصف (اختياري)
                </label>
                <input
                    id="transaction-title"
                    type="text"
                    placeholder="مثال: مشتريات بقالة"
                    bind:value={form.title}
                    class="h-11 rounded-xl border border-border bg-background px-4 text-foreground outline-none focus:border-primary"
                />
                {#if form.errors.title}
                    <p class="text-xs text-destructive">{form.errors.title}</p>
                {/if}
            </div>

            <div class="grid gap-1.5">
                <span id="transaction-category-label" class="text-sm text-muted-foreground">
                    الفئة
                </span>
                {#if filteredCategories.length === 0}
                    <p class="text-xs text-muted-foreground">
                        لا توجد فئات لهذا النوع. أضف فئة أولاً من صفحة الفئات.
                    </p>
                {:else}
                    <div
                        class="flex flex-wrap gap-2"
                        role="radiogroup"
                        aria-labelledby="transaction-category-label"
                    >
                        {#each filteredCategories as category (category.id)}
                            <button
                                type="button"
                                role="radio"
                                aria-checked={form.category_id === category.id}
                                onclick={() => (form.category_id = category.id)}
                                class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-2 text-sm transition-colors {form.category_id ===
                                category.id
                                    ? 'border-transparent font-medium text-foreground'
                                    : 'border-border text-muted-foreground hover:bg-muted'}"
                                style={form.category_id === category.id
                                    ? `background-color: ${category.color}1a; border-color: ${category.color};`
                                    : ''}
                            >
                                {#if category.icon}
                                    <span>{category.icon}</span>
                                {/if}
                                <span>{category.name}</span>
                            </button>
                        {/each}
                    </div>
                {/if}
                {#if form.errors.category_id}
                    <p class="text-xs text-destructive">{form.errors.category_id}</p>
                {/if}
            </div>

            <div class="grid gap-1.5">
                <label for="transaction-date" class="text-sm text-muted-foreground">
                    التاريخ
                </label>
                <input
                    id="transaction-date"
                    type="date"
                    bind:value={form.date}
                    class="h-11 rounded-xl border border-border bg-background px-4 text-foreground outline-none focus:border-primary"
                />
                {#if form.errors.date}
                    <p class="text-xs text-destructive">{form.errors.date}</p>
                {/if}
            </div>

            <button
                type="submit"
                disabled={form.processing}
                class="mt-1 inline-flex h-11 items-center justify-center rounded-full bg-primary px-6 text-[15px] font-medium text-primary-foreground transition active:scale-95 disabled:opacity-50"
            >
                {form.processing ? 'جارٍ الحفظ...' : transaction ? 'حفظ التعديلات' : 'إضافة'}
            </button>
        </form>
    </div>
{/if}

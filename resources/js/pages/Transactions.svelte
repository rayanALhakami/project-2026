<script module lang="ts">
    import { index as transactionsIndex } from '@/routes/transactions';

    export const layout = {
        breadcrumbs: [
            {
                title: 'المعاملات',
                href: transactionsIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Plus from '@lucide/svelte/icons/plus';
    import Search from '@lucide/svelte/icons/search';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import { untrack } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import EmptyState from '@/components/EmptyState.svelte';
    import TransactionFormDialog from '@/components/TransactionFormDialog.svelte';
    import { formatCurrency, formatDate } from '@/lib/format';
    import { toUrl } from '@/lib/utils';
    import transactions from '@/routes/transactions';
    import type { Category, Transaction } from '@/types';

    type Paginated = {
        data: Transaction[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        total: number;
    };

    let {
        transactions: paginated = {
            data: [],
            current_page: 1,
            last_page: 1,
            prev_page_url: null,
            next_page_url: null,
            total: 0,
        } as Paginated,
        categories = [] as Category[],
        filters = {} as { search?: string; type?: string; category?: string },
    } = $props();

    let search = $state(untrack(() => filters.search ?? ''));
    let type = $state(untrack(() => filters.type ?? ''));
    let category = $state(untrack(() => filters.category ?? ''));

    let dialogOpen = $state(false);
    let editing = $state<Transaction | null>(null);

    let debounceTimer: ReturnType<typeof setTimeout> | undefined;

    function applyFilters(): void {
        router.get(
            toUrl(transactions.index()),
            { search: search || undefined, type: type || undefined, category: category || undefined },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    function onSearchInput(): void {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyFilters, 300);
    }

    function openCreate(): void {
        editing = null;
        dialogOpen = true;
    }

    function openEdit(transaction: Transaction): void {
        editing = transaction;
        dialogOpen = true;
    }

    function remove(transaction: Transaction): void {
        if (confirm(`هل تريد حذف «${transaction.title?.trim() || 'بدون وصف'}»؟`)) {
            router.delete(toUrl(transactions.destroy({ transaction: transaction.id })), {
                preserveScroll: true,
            });
        }
    }

    function signedCurrency(transaction: Transaction): string {
        const sign = transaction.type === 'income' ? '+' : '-';
        return `${sign}${formatCurrency(transaction.amount)}`;
    }
</script>

<AppHead title="المعاملات" />

<div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                المعاملات
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {paginated.total.toLocaleString('ar-SA')} معاملة مسجلة
            </p>
        </div>
        <button
            type="button"
            onclick={openCreate}
            class="inline-flex items-center gap-2 rounded-full bg-primary px-6 py-2.5 text-[15px] font-medium text-primary-foreground transition active:scale-95"
        >
            <Plus class="size-4" />
            إضافة معاملة
        </button>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
            <Search
                class="pointer-events-none absolute start-3.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
            />
            <input
                type="text"
                placeholder="ابحث عن معاملة..."
                bind:value={search}
                oninput={onSearchInput}
                class="h-11 w-full rounded-full border border-border bg-card ps-10 pe-4 text-foreground outline-none focus:border-primary"
            />
        </div>

        <select
            bind:value={type}
            onchange={applyFilters}
            class="h-11 rounded-full border border-border bg-card px-4 text-foreground outline-none focus:border-primary"
        >
            <option value="">كل الأنواع</option>
            <option value="expense">مصروف</option>
            <option value="income">دخل</option>
        </select>

        <select
            bind:value={category}
            onchange={applyFilters}
            class="h-11 rounded-full border border-border bg-card px-4 text-foreground outline-none focus:border-primary"
        >
            <option value="">كل الفئات</option>
            {#each categories as item (item.id)}
                <option value={String(item.id)}>
                    {item.icon ? `${item.icon} ` : ''}{item.name}
                </option>
            {/each}
        </select>
    </div>

    <div class="rounded-[18px] border border-black/[0.06] bg-card">
        {#if paginated.data.length === 0}
            <div class="p-6">
                {#if paginated.current_page > 1}
                    <EmptyState
                        icon="🧾"
                        title="لا توجد نتائج في هذه الصفحة"
                        description="عُد إلى الصفحة السابقة لعرض المعاملات."
                    />
                {:else}
                    <EmptyState
                        icon="🧾"
                        title="لا توجد معاملات"
                        description="جرّب تعديل البحث أو الفلاتر، أو أضف معاملة جديدة."
                    />
                {/if}
            </div>
        {:else}
            <ul>
                {#each paginated.data as transaction, i (transaction.id)}
                    <li
                        class="flex items-center gap-3 px-5 py-3.5 {i < paginated.data.length - 1
                            ? 'border-b border-border/60'
                            : ''}"
                    >
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-full text-lg"
                            style="background-color: {transaction.category?.color ?? '#8e8e93'}1a;"
                        >
                            {transaction.category?.icon ?? '🏷️'}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-foreground">
                                {transaction.title?.trim() || 'بدون وصف'}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {transaction.category?.name ?? 'بدون فئة'} · {formatDate(transaction.date)}
                            </p>
                        </div>
                        <span
                            class="text-sm font-semibold {transaction.type === 'income'
                                ? 'text-[#34c759]'
                                : 'text-foreground'}"
                        >
                            {signedCurrency(transaction)}
                        </span>
                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                onclick={() => openEdit(transaction)}
                                class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                aria-label="تعديل"
                            >
                                <Pencil class="size-4" />
                            </button>
                            <button
                                type="button"
                                onclick={() => remove(transaction)}
                                class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                aria-label="حذف"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </li>
                {/each}
            </ul>
        {/if}

        {#if paginated.last_page > 1 || paginated.current_page > 1}
            <div class="flex items-center justify-between border-t border-border/60 px-5 py-4">
                <button
                    type="button"
                    disabled={!paginated.prev_page_url}
                    onclick={() => router.get(paginated.prev_page_url!, {}, { preserveScroll: true })}
                    class="rounded-full border border-border px-4 py-2 text-sm text-foreground transition-colors hover:bg-muted disabled:opacity-40"
                >
                    السابق
                </button>
                <span class="text-sm text-muted-foreground">
                    صفحة {Math.min(paginated.current_page, paginated.last_page).toLocaleString('ar-SA')} من {paginated.last_page.toLocaleString('ar-SA')}
                </span>
                <button
                    type="button"
                    disabled={!paginated.next_page_url}
                    onclick={() => router.get(paginated.next_page_url!, {}, { preserveScroll: true })}
                    class="rounded-full border border-border px-4 py-2 text-sm text-foreground transition-colors hover:bg-muted disabled:opacity-40"
                >
                    التالي
                </button>
            </div>
        {/if}
    </div>
</div>

<TransactionFormDialog
    bind:open={dialogOpen}
    {categories}
    transaction={editing}
    defaultType="expense"
/>

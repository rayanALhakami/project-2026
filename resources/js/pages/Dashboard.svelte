<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'الرئيسية',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import ArrowDownRight from '@lucide/svelte/icons/arrow-down-right';
    import ArrowUpRight from '@lucide/svelte/icons/arrow-up-right';
    import Plus from '@lucide/svelte/icons/plus';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import AppHead from '@/components/AppHead.svelte';
    import EmptyState from '@/components/EmptyState.svelte';
    import TransactionFormDialog from '@/components/TransactionFormDialog.svelte';
    import BarChart from '@/components/dashboard/BarChart.svelte';
    import DonutChart from '@/components/dashboard/DonutChart.svelte';
    import { formatCurrency, formatDate } from '@/lib/format';
    import { assistant } from '@/routes';
    import transactions from '@/routes/transactions';
    import type {
        Category,
        CategoryRank,
        DashboardSummary,
        DashboardTrendPoint,
        SpendingSlice,
        Transaction,
    } from '@/types';

    let {
        summary = {
            spent: 0,
            income: 0,
            balance: 0,
            spent_delta: null,
        } as DashboardSummary,
        monthlyTrend = [] as DashboardTrendPoint[],
        spendingByCategory = [] as SpendingSlice[],
        categoryRanking = [] as CategoryRank[],
        recentTransactions = [] as Transaction[],
        categories = [] as Category[],
        insight = null as string | null,
    } = $props();

    const user = $derived(page.props.auth.user);

    let quickAddOpen = $state(false);

    function signedCurrency(transaction: Transaction): string {
        const sign = transaction.type === 'income' ? '+' : '-';
        return `${sign}${formatCurrency(transaction.amount)}`;
    }
</script>

<AppHead title="الرئيسية" />

<div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                مرحباً، {user?.name}
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                إليك ملخص مصاريفك لهذا الشهر
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <Link
                href={assistant()}
                class="inline-flex items-center gap-2 rounded-full border border-border bg-card px-5 py-2.5 text-[15px] font-medium text-foreground transition hover:border-primary hover:text-primary active:scale-95"
            >
                <Sparkles class="size-4" />
                المساعد
            </Link>
            <button
                type="button"
                onclick={() => (quickAddOpen = true)}
                class="inline-flex items-center gap-2 rounded-full bg-primary px-6 py-2.5 text-[15px] font-medium text-primary-foreground transition active:scale-95"
            >
                <Plus class="size-4" />
                إضافة مصروف
            </button>
        </div>
    </div>

    {#if insight}
        <div class="flex items-start gap-3 rounded-[18px] bg-secondary p-5 text-foreground">
            <span
                class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary"
            >
                <Sparkles class="size-5" />
            </span>
            <div>
                <p class="text-sm font-semibold">رؤية تحليلية</p>
                <p class="mt-0.5 text-sm text-muted-foreground">{insight}</p>
            </div>
        </div>
    {/if}

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-[18px] border border-black/[0.06] bg-card p-5">
            <p class="text-sm text-muted-foreground">إجمالي الصرف</p>
            <p class="mt-2 text-[28px] font-bold tracking-tight text-foreground">
                {formatCurrency(summary.spent)}
            </p>
            <p class="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                {#if summary.spent_delta !== null}
                    {#if summary.spent_delta > 0}
                        <span class="flex items-center gap-1 text-destructive">
                            <ArrowUpRight class="size-3.5" />
                            {summary.spent_delta.toLocaleString('ar-SA')}٪
                        </span>
                    {:else if summary.spent_delta < 0}
                        <span class="flex items-center gap-1 text-[#34c759]">
                            <ArrowDownRight class="size-3.5" />
                            {Math.abs(summary.spent_delta).toLocaleString('ar-SA')}٪
                        </span>
                    {:else}
                        <span class="text-muted-foreground">لا تغيير</span>
                    {/if}
                    <span>عن الشهر الماضي</span>
                {:else}
                    هذا الشهر
                {/if}
            </p>
        </div>
        <div class="rounded-[18px] border border-black/[0.06] bg-card p-5">
            <p class="text-sm text-muted-foreground">الدخل</p>
            <p class="mt-2 text-[28px] font-bold tracking-tight text-foreground">
                {formatCurrency(summary.income)}
            </p>
            <p class="mt-1 text-xs text-muted-foreground">هذا الشهر</p>
        </div>
        <div class="rounded-[18px] border border-black/[0.06] bg-card p-5">
            <p class="text-sm text-muted-foreground">صافي الشهر</p>
            <p class="mt-2 text-[28px] font-bold tracking-tight text-primary">
                {formatCurrency(summary.balance)}
            </p>
            <p class="mt-1 text-xs text-muted-foreground">
                الدخل ناقص الصرف لهذا الشهر
            </p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-[18px] border border-black/[0.06] bg-card p-6">
            <h2 class="text-base font-semibold text-foreground">
                المصروفات حسب الفئة
            </h2>
            {#if spendingByCategory.length === 0}
                <div class="mt-6">
                    <EmptyState
                        icon="📊"
                        title="لا توجد مصروفات بعد"
                        description="أضف أول مصروف لبدء تتبع فئات الإنفاق."
                    />
                </div>
            {:else}
                <div class="mt-6 flex flex-col items-center gap-8 sm:flex-row">
                    <DonutChart
                        data={spendingByCategory}
                        centerValue={formatCurrency(summary.spent)}
                        centerTitle="الإجمالي"
                    />
                    <ul class="flex w-full flex-col gap-3">
                        {#each spendingByCategory as category, index (category.label + '-' + index)}
                            <li class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="flex size-7 shrink-0 items-center justify-center rounded-full text-sm"
                                        style="background-color: {category.color}1a;"
                                    >
                                        {category.icon}
                                    </span>
                                    <span class="text-sm text-foreground">
                                        {category.label}
                                    </span>
                                </div>
                                <span class="text-sm font-medium text-foreground">
                                    {formatCurrency(category.value)}
                                </span>
                            </li>
                        {/each}
                    </ul>
                </div>
            {/if}
        </div>

        <div class="rounded-[18px] border border-black/[0.06] bg-card p-6">
            <h2 class="text-base font-semibold text-foreground">
                اتجاه الإنفاق
            </h2>
            <p class="mt-1 text-sm text-muted-foreground">آخر ٦ أشهر</p>
            <div class="mt-6">
                <BarChart data={monthlyTrend} color="#0066cc" />
            </div>
        </div>
    </div>

    <div class="rounded-[18px] border border-black/[0.06] bg-card p-6">
        <h2 class="text-base font-semibold text-foreground">
            الأكثر صرفاً هذا الشهر
        </h2>
        {#if categoryRanking.length === 0}
            <div class="mt-4">
                <EmptyState
                    icon="🏆"
                    title="لا توجد فئات بعد"
                    description="أضف فئات لتظهر هنا مرتبة حسب الأكثر صرفاً."
                />
            </div>
        {:else}
            <ul class="mt-4 flex flex-col gap-4">
                {#each categoryRanking as category, index (category.id)}
                    <li>
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-secondary text-xs font-semibold text-muted-foreground"
                                >
                                    {(index + 1).toLocaleString('ar-SA')}
                                </span>
                                <span
                                    class="flex size-8 shrink-0 items-center justify-center rounded-full text-base"
                                    style="background-color: {category.color}1a;"
                                >
                                    {category.icon}
                                </span>
                                <span class="truncate text-sm font-medium text-foreground">
                                    {category.label}
                                </span>
                            </div>
                            <span class="shrink-0 text-sm text-muted-foreground">
                                {formatCurrency(category.value)} · {category.percent.toLocaleString('ar-SA')}٪
                            </span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-secondary">
                            <div
                                class="h-full rounded-full"
                                style="width: {category.percent}%; background-color: {category.color};"
                            ></div>
                        </div>
                    </li>
                {/each}
            </ul>
        {/if}
    </div>

    <div class="rounded-[18px] border border-black/[0.06] bg-card p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-foreground">آخر المعاملات</h2>
            <Link href={transactions.index()} class="text-sm text-primary">
                عرض الكل
            </Link>
        </div>

        {#if recentTransactions.length === 0}
            <div class="mt-4">
                <EmptyState
                    icon="🧾"
                    title="لا توجد معاملات بعد"
                    description="ابدأ بإضافة مصروف أو دخل لتظهر هنا."
                />
            </div>
        {:else}
            <ul class="mt-4">
                {#each recentTransactions as transaction, i (transaction.id)}
                    <li
                        class="flex items-center gap-3 py-3 {i < recentTransactions.length - 1
                            ? 'border-b border-border/60'
                            : ''}"
                    >
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-base"
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
                    </li>
                {/each}
            </ul>
        {/if}
    </div>
</div>

<TransactionFormDialog
    bind:open={quickAddOpen}
    {categories}
    defaultType="expense"
/>

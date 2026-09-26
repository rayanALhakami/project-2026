<script module lang="ts">
    import { reports } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'التقارير',
                href: reports(),
            },
        ],
    };
</script>

<script lang="ts">
    import Download from '@lucide/svelte/icons/download';
    import AppHead from '@/components/AppHead.svelte';
    import EmptyState from '@/components/EmptyState.svelte';
    import BarChart, { type BarDatum } from '@/components/dashboard/BarChart.svelte';
    import DonutChart from '@/components/dashboard/DonutChart.svelte';
    import { formatCurrency } from '@/lib/format';
    import { exportMethod } from '@/routes/reports';
    import type {
        CategoryReport,
        MonthlyTrendPoint,
        SpendingSlice,
    } from '@/types';

    let {
        summary = { expense: 0, income: 0, net: 0 },
        monthlyTrend = [] as MonthlyTrendPoint[],
        spendingByCategory = [] as SpendingSlice[],
        topCategories = [] as CategoryReport[],
    } = $props();

    const expenseTrend: BarDatum[] = $derived(
        monthlyTrend.map((point) => ({ label: point.label, value: point.expense })),
    );

    const incomeTrend: BarDatum[] = $derived(
        monthlyTrend.map((point) => ({ label: point.label, value: point.income })),
    );
</script>

<AppHead title="التقارير" />

<div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                التقارير
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                نظرة تحليلية على آخر ٦ أشهر
            </p>
        </div>
        <a
            href={exportMethod.url()}
            download
            class="inline-flex items-center gap-2 rounded-full border border-black/[0.08] bg-card px-5 py-2.5 text-[15px] font-medium text-foreground transition active:scale-95"
        >
            <Download class="size-4" />
            تصدير CSV
        </a>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-[18px] border border-black/[0.06] bg-card p-5">
            <p class="text-sm text-muted-foreground">إجمالي الصرف</p>
            <p class="mt-2 text-[28px] font-bold tracking-tight text-foreground">
                {formatCurrency(summary.expense)}
            </p>
            <p class="mt-1 text-xs text-muted-foreground">آخر ٦ أشهر</p>
        </div>
        <div class="rounded-[18px] border border-black/[0.06] bg-card p-5">
            <p class="text-sm text-muted-foreground">إجمالي الدخل</p>
            <p class="mt-2 text-[28px] font-bold tracking-tight text-[#34c759]">
                {formatCurrency(summary.income)}
            </p>
            <p class="mt-1 text-xs text-muted-foreground">آخر ٦ أشهر</p>
        </div>
        <div class="rounded-[18px] border border-black/[0.06] bg-card p-5">
            <p class="text-sm text-muted-foreground">صافي التوفير</p>
            <p
                class="mt-2 text-[28px] font-bold tracking-tight {summary.net >= 0
                    ? 'text-primary'
                    : 'text-destructive'}"
            >
                {formatCurrency(summary.net)}
            </p>
            <p class="mt-1 text-xs text-muted-foreground">آخر ٦ أشهر</p>
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
                        title="لا توجد بيانات"
                        description="أضف مصروفات لعرض توزيع الفئات."
                    />
                </div>
            {:else}
                <div class="mt-6 flex flex-col items-center gap-8 sm:flex-row">
                    <DonutChart
                        data={spendingByCategory}
                        centerValue={formatCurrency(summary.expense)}
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
                اتجاه الدخل والصرف
            </h2>
            <p class="mt-1 text-sm text-muted-foreground">آخر ٦ أشهر</p>
            {#if monthlyTrend.every((point) => point.income === 0 && point.expense === 0)}
                <div class="mt-6">
                    <EmptyState
                        icon="📈"
                        title="لا توجد بيانات"
                        description="أضف معاملات لعرض اتجاه الدخل والصرف."
                    />
                </div>
            {:else}
                <div class="mt-6">
                    <BarChart
                        data={expenseTrend}
                        secondaryData={incomeTrend}
                        color="#0066cc"
                        secondaryColor="#34c759"
                        legend
                    />
                </div>
            {/if}
        </div>
    </div>

    <div class="rounded-[18px] border border-black/[0.06] bg-card p-6">
        <h2 class="text-base font-semibold text-foreground">ترتيب الفئات</h2>
        {#if topCategories.length === 0}
            <div class="mt-6">
                <EmptyState
                    icon="🏆"
                    title="لا توجد بيانات"
                    description="أضف مصروفات لعرض ترتيب الفئات."
                />
            </div>
        {:else}
            <ul class="mt-4 flex flex-col gap-4">
                {#each topCategories as category, index (category.label + '-' + index)}
                    <li>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span
                                    class="flex size-7 shrink-0 items-center justify-center rounded-full text-sm"
                                    style="background-color: {category.color}1a;"
                                >
                                    {category.icon}
                                </span>
                                <span class="text-sm font-medium text-foreground">
                                    {category.label}
                                </span>
                            </div>
                            <span class="text-sm text-muted-foreground">
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
        <h2 class="text-base font-semibold text-foreground">مقارنة الدخل والصرف</h2>
        {#if monthlyTrend.every((point) => point.income === 0 && point.expense === 0)}
            <div class="mt-6">
                <EmptyState
                    icon="📋"
                    title="لا توجد بيانات"
                    description="أضف معاملات لعرض مقارنة الدخل والصرف."
                />
            </div>
        {:else}
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[480px] text-sm">
                <thead>
                    <tr class="text-muted-foreground">
                        <th class="pb-3 text-start font-medium">الشهر</th>
                        <th class="pb-3 text-end font-medium">الدخل</th>
                        <th class="pb-3 text-end font-medium">الصرف</th>
                        <th class="pb-3 text-end font-medium">الصافي</th>
                    </tr>
                </thead>
                <tbody>
                    {#each monthlyTrend as point (point.label)}
                        <tr class="border-t border-border/60">
                            <td class="py-3 font-medium text-foreground">{point.label}</td>
                            <td class="py-3 text-end text-[#34c759]">
                                {formatCurrency(point.income)}
                            </td>
                            <td class="py-3 text-end text-foreground">
                                {formatCurrency(point.expense)}
                            </td>
                            <td
                                class="py-3 text-end font-semibold {point.income - point.expense >=
                                0
                                    ? 'text-primary'
                                    : 'text-destructive'}"
                            >
                                {formatCurrency(point.income - point.expense)}
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
        {/if}
    </div>
</div>

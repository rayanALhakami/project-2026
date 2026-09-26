<script module lang="ts">
    import { index as categoriesIndex } from '@/routes/categories';

    export const layout = {
        breadcrumbs: [
            {
                title: 'الفئات',
                href: categoriesIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Plus from '@lucide/svelte/icons/plus';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import CategoryFormDialog from '@/components/CategoryFormDialog.svelte';
    import EmptyState from '@/components/EmptyState.svelte';
    import { toUrl } from '@/lib/utils';
    import categories from '@/routes/categories';
    import type { Category } from '@/types';

    let {
        categories: items = [] as Category[],
    } = $props();

    const expenseCategories = $derived(
        items.filter((category) => category.type === 'expense'),
    );
    const incomeCategories = $derived(
        items.filter((category) => category.type === 'income'),
    );

    let dialogOpen = $state(false);
    let editing = $state<Category | null>(null);

    function openCreate(): void {
        editing = null;
        dialogOpen = true;
    }

    function openEdit(category: Category): void {
        editing = category;
        dialogOpen = true;
    }

    function remove(category: Category): void {
        const count = category.transactions_count ?? 0;
        const message =
            count > 0
                ? `هل تريد حذف فئة «${category.name}»؟ سيتم إلغاء تصنيف ${count.toLocaleString('ar-SA')} معاملة مرتبطة بها وستصبح بدون فئة.`
                : `هل تريد حذف فئة «${category.name}»؟`;

        if (confirm(message)) {
            router.delete(toUrl(categories.destroy({ category: category.id })), {
                preserveScroll: true,
            });
        }
    }
</script>

<AppHead title="الفئات" />

<div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                الفئات
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                نظّم مصروفاتك ودخلك في فئات
            </p>
        </div>
        <button
            type="button"
            onclick={openCreate}
            class="inline-flex items-center gap-2 rounded-full bg-primary px-6 py-2.5 text-[15px] font-medium text-primary-foreground transition active:scale-95"
        >
            <Plus class="size-4" />
            إضافة فئة
        </button>
    </div>

    {#if items.length === 0}
        <EmptyState
            icon="🗂️"
            title="لا توجد فئات بعد"
            description="أضف فئات لتنظيم معاملاتك بشكل أفضل."
        />
    {:else}
        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-[18px] border border-black/[0.06] bg-card p-6">
                <h2 class="text-base font-semibold text-foreground">المصروفات</h2>
                {#if expenseCategories.length === 0}
                    <p class="mt-4 text-sm text-muted-foreground">لا توجد فئات مصروفات بعد.</p>
                {:else}
                <ul class="mt-4">
                    {#each expenseCategories as category, i (category.id)}
                        <li
                            class="flex items-center gap-3 py-3 {i < expenseCategories.length - 1
                                ? 'border-b border-border/60'
                                : ''}"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-full text-lg"
                                style="background-color: {category.color}1a;"
                            >
                                {category.icon ?? '🏷️'}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-foreground">
                                    {category.name}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {(category.transactions_count ?? 0).toLocaleString('ar-SA')} معاملة
                                </p>
                            </div>
                            <button
                                type="button"
                                onclick={() => openEdit(category)}
                                class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                aria-label="تعديل"
                            >
                                <Pencil class="size-4" />
                            </button>
                            <button
                                type="button"
                                onclick={() => remove(category)}
                                class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                aria-label="حذف"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </li>
                    {/each}
                </ul>
                {/if}
            </div>

            <div class="rounded-[18px] border border-black/[0.06] bg-card p-6">
                <h2 class="text-base font-semibold text-foreground">الدخل</h2>
                {#if incomeCategories.length === 0}
                    <p class="mt-4 text-sm text-muted-foreground">لا توجد فئات دخل بعد.</p>
                {:else}
                <ul class="mt-4">
                    {#each incomeCategories as category, i (category.id)}
                        <li
                            class="flex items-center gap-3 py-3 {i < incomeCategories.length - 1
                                ? 'border-b border-border/60'
                                : ''}"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-full text-lg"
                                style="background-color: {category.color}1a;"
                            >
                                {category.icon ?? '🏷️'}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-foreground">
                                    {category.name}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {(category.transactions_count ?? 0).toLocaleString('ar-SA')} معاملة
                                </p>
                            </div>
                            <button
                                type="button"
                                onclick={() => openEdit(category)}
                                class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                aria-label="تعديل"
                            >
                                <Pencil class="size-4" />
                            </button>
                            <button
                                type="button"
                                onclick={() => remove(category)}
                                class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                aria-label="حذف"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </li>
                    {/each}
                </ul>
                {/if}
            </div>
        </div>
    {/if}
</div>

<CategoryFormDialog bind:open={dialogOpen} category={editing} defaultType="expense" />

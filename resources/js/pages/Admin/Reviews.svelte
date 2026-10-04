<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import Star from '@lucide/svelte/icons/star';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import { toast } from 'svelte-sonner';
    import AdminNav from '@/components/AdminNav.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table';
    import { formatDate, formatNumber, t } from '@/lib/i18n.svelte';
    import { destroy as adminReviewDestroy } from '@/routes/admin/reviews';

    interface AdminReview {
        id: number;
        place_name: string | null;
        author: string;
        rating: number | string | null;
        content: string | null;
        source: string | null;
        reviewed_at: string | null;
    }

    let {
        reviews,
    }: {
        reviews: AdminReview[];
    } = $props();

    function removeReview(review: AdminReview): void {
        if (!window.confirm(t('admin.confirmDelete'))) {
            return;
        }

        router.delete(adminReviewDestroy.url(review.id), {
            preserveScroll: true,
            onSuccess: () => toast.success(t('admin.delete')),
        });
    }
</script>

<AppHead title={t('admin.reviews')} />

<div class="mx-auto w-full max-w-6xl px-4 py-6 md:px-6">
    <AdminNav />

    <header class="mt-6">
        <h1 class="text-2xl font-bold text-foreground sm:text-3xl">
            {t('admin.reviews')}
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">{t('admin.subtitle')}</p>
    </header>

    {#if reviews.length === 0}
        <div
            class="mt-6 rounded-[20px] bg-card p-10 text-center text-sm font-bold text-muted-foreground shadow-sm ring-1 ring-border"
        >
            {t('admin.empty')}
        </div>
    {:else}
        <div
            class="mt-6 overflow-hidden rounded-[20px] bg-card p-2 shadow-sm ring-1 ring-border"
        >
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.place')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.author')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.rating')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.content')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.source')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.reviewedAt')}
                        </TableHead>
                        <TableHead class="px-4 text-end">
                            {t('admin.actions')}
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {#each reviews as review (review.id)}
                        <TableRow>
                            <TableCell
                                class="px-4 py-3 text-start font-bold text-foreground"
                            >
                                {review.place_name ?? '—'}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {review.author}
                            </TableCell>
                            <TableCell class="px-4 py-3 text-start">
                                <span
                                    class="inline-flex items-center gap-1 text-muted-foreground"
                                >
                                    <Star
                                        class="size-4 fill-amber-400 text-amber-400"
                                    />
                                    {review.rating === null || review.rating === ''
                                        ? '—'
                                        : formatNumber(Number(review.rating))}
                                </span>
                            </TableCell>
                            <TableCell
                                class="max-w-md px-4 py-3 text-start whitespace-normal text-muted-foreground"
                            >
                                {review.content ?? '—'}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {review.source ?? '—'}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {review.reviewed_at
                                    ? formatDate(review.reviewed_at)
                                    : '—'}
                            </TableCell>
                            <TableCell class="px-4 py-3 text-end">
                                <button
                                    type="button"
                                    onclick={() => removeReview(review)}
                                    class="inline-flex min-h-9 items-center gap-1.5 rounded-lg px-3 text-sm font-bold text-red-600 ring-1 ring-red-200 transition hover:bg-red-50 active:scale-[0.98] dark:text-red-400 dark:ring-red-900/60 dark:hover:bg-red-950/40"
                                >
                                    <Trash2 class="size-3.5" />
                                    {t('admin.delete')}
                                </button>
                            </TableCell>
                        </TableRow>
                    {/each}
                </TableBody>
            </Table>
        </div>
    {/if}
</div>

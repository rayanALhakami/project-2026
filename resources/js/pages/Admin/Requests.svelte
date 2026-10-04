<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import Check from '@lucide/svelte/icons/check';
    import RotateCcw from '@lucide/svelte/icons/rotate-ccw';
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
    import {
        destroy as adminRequestDestroy,
        handle as adminRequestHandle,
    } from '@/routes/admin/requests';

    interface AdminRequest {
        id: number;
        name: string;
        phone: string;
        city_name: string | null;
        start_date: string | null;
        travelers: number | string | null;
        budget: number | string | null;
        notes: string | null;
        handled: boolean;
        created_at: string | null;
    }

    let {
        requests,
    }: {
        requests: AdminRequest[];
    } = $props();

    const toggleButtonClass =
        'inline-flex min-h-9 items-center gap-1.5 rounded-lg px-3 text-sm font-bold text-emerald-700 ring-1 ring-emerald-200 transition hover:bg-emerald-50 active:scale-[0.98] dark:text-emerald-300 dark:ring-emerald-900/60 dark:hover:bg-emerald-950/40';
    const deleteButtonClass =
        'inline-flex min-h-9 items-center gap-1.5 rounded-lg px-3 text-sm font-bold text-red-600 ring-1 ring-red-200 transition hover:bg-red-50 active:scale-[0.98] dark:text-red-400 dark:ring-red-900/60 dark:hover:bg-red-950/40';

    function toggleHandled(request: AdminRequest): void {
        router.patch(
            adminRequestHandle.url(request.id),
            {},
            { preserveScroll: true },
        );
    }

    function removeRequest(request: AdminRequest): void {
        if (!window.confirm(t('admin.confirmDelete'))) {
            return;
        }

        router.delete(adminRequestDestroy.url(request.id), {
            preserveScroll: true,
            onSuccess: () => toast.success(t('admin.delete')),
        });
    }

    function budgetLabel(budget: number | string | null): string {
        return budget === null
            ? '—'
            : `${formatNumber(Number(budget))} ${t('common.currency')}`;
    }
</script>

<AppHead title={t('admin.requests')} />

<div class="mx-auto w-full max-w-6xl px-4 py-6 md:px-6">
    <AdminNav />

    <header class="mt-6">
        <h1 class="text-2xl font-bold text-foreground sm:text-3xl">
            {t('admin.requests')}
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">{t('admin.subtitle')}</p>
    </header>

    {#if requests.length === 0}
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
                            {t('preview.formName')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.city')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.startDate')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('trips.travelers')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('trips.budget')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.notes')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.reviewedAt')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.handled')}
                        </TableHead>
                        <TableHead class="px-4 text-end">
                            {t('admin.actions')}
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {#each requests as request (request.id)}
                        <TableRow>
                            <TableCell class="px-4 py-3 text-start">
                                <span
                                    class="block font-bold text-foreground"
                                >
                                    {request.name}
                                </span>
                                <span
                                    class="block text-xs text-muted-foreground"
                                >
                                    <span dir="ltr">{request.phone}</span>
                                </span>
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {request.city_name ?? '—'}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {request.start_date
                                    ? formatDate(request.start_date)
                                    : '—'}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {request.travelers ?? '—'}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {budgetLabel(request.budget)}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                <span
                                    class="inline-block max-w-56 truncate align-middle"
                                    title={request.notes ?? ''}
                                >
                                    {request.notes ?? '—'}
                                </span>
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {request.created_at
                                    ? formatDate(request.created_at)
                                    : '—'}
                            </TableCell>
                            <TableCell class="px-4 py-3 text-start">
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {request.handled
                                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900/60'
                                        : 'bg-muted/60 text-muted-foreground ring-1 ring-border'}"
                                >
                                    {request.handled
                                        ? t('admin.handled')
                                        : t('admin.pending')}
                                </span>
                            </TableCell>
                            <TableCell class="px-4 py-3 text-end">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <button
                                        type="button"
                                        onclick={() => toggleHandled(request)}
                                        class={toggleButtonClass}
                                    >
                                        {#if request.handled}
                                            <RotateCcw class="size-3.5" />
                                            {t('admin.markPending')}
                                        {:else}
                                            <Check class="size-3.5" />
                                            {t('admin.markHandled')}
                                        {/if}
                                    </button>
                                    <button
                                        type="button"
                                        onclick={() => removeRequest(request)}
                                        class={deleteButtonClass}
                                    >
                                        <Trash2 class="size-3.5" />
                                        {t('admin.delete')}
                                    </button>
                                </div>
                            </TableCell>
                        </TableRow>
                    {/each}
                </TableBody>
            </Table>
        </div>
    {/if}
</div>

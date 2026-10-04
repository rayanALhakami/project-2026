<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Star from '@lucide/svelte/icons/star';
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
    import { formatNumber, t } from '@/lib/i18n.svelte';
    import { categoryMeta } from '@/lib/tourism.svelte';
    import { edit as adminPlaceEdit } from '@/routes/admin/places';
    import type { PlaceCategory } from '@/types';

    interface AdminPlaceRow {
        id: number;
        name: string;
        name_en: string;
        city_name: string | null;
        category: PlaceCategory;
        ticket_price: string | null;
        rating: string | null;
    }

    let {
        places,
    }: {
        places: AdminPlaceRow[];
    } = $props();

    function priceLabel(price: string | null): string {
        if (price === null) {
            return t('common.perRequest');
        }

        const value = Number(price);

        if (value === 0) {
            return t('common.free');
        }

        return `${formatNumber(value)} ${t('common.currency')}`;
    }

    function ratingLabel(rating: string | null): string {
        return rating === null ? '—' : formatNumber(Number(rating));
    }
</script>

<AppHead title={t('admin.places')} />

<div class="mx-auto w-full max-w-6xl px-4 py-6 md:px-6">
    <AdminNav />

    <header class="mt-6">
        <h1 class="text-2xl font-bold text-foreground sm:text-3xl">
            {t('admin.places')}
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">{t('admin.subtitle')}</p>
    </header>

    {#if places.length === 0}
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
                            {t('admin.fields.name')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.nameEn')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.city')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.category')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.ticketPrice')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.rating')}
                        </TableHead>
                        <TableHead class="px-4 text-end">
                            {t('admin.actions')}
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {#each places as place (place.id)}
                        <TableRow>
                            <TableCell
                                class="px-4 py-3 text-start font-bold text-foreground"
                            >
                                {place.name}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {place.name_en}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {place.city_name ?? '—'}
                            </TableCell>
                            <TableCell class="px-4 py-3 text-start">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-muted/60 px-3 py-1 text-xs font-bold text-secondary-foreground ring-1 ring-border"
                                >
                                    <span aria-hidden="true">
                                        {categoryMeta[place.category].icon}
                                    </span>
                                    {t(`categories.${place.category}`)}
                                </span>
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {priceLabel(place.ticket_price)}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                <span class="inline-flex items-center gap-1">
                                    <Star
                                        class="size-4 fill-amber-400 text-amber-400"
                                    />
                                    {ratingLabel(place.rating)}
                                </span>
                            </TableCell>
                            <TableCell class="px-4 py-3 text-end">
                                <Link
                                    href={adminPlaceEdit.url(place.id)}
                                    class="inline-flex min-h-9 items-center gap-1.5 rounded-lg px-3 text-sm font-bold text-emerald-700 ring-1 ring-emerald-200 transition hover:bg-emerald-50 active:scale-[0.98] dark:text-emerald-300 dark:ring-emerald-900/60 dark:hover:bg-emerald-950/40"
                                >
                                    <Pencil class="size-3.5" />
                                    {t('admin.edit')}
                                </Link>
                            </TableCell>
                        </TableRow>
                    {/each}
                </TableBody>
            </Table>
        </div>
    {/if}
</div>

<script lang="ts">
    import { Link, useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { toast } from 'svelte-sonner';
    import AdminNav from '@/components/AdminNav.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { t } from '@/lib/i18n.svelte';
    import { categoryMeta } from '@/lib/tourism.svelte';
    import {
        index as adminPlaces,
        update as adminPlaceUpdate,
    } from '@/routes/admin/places';
    import type { PlaceCategory } from '@/types';

    interface AdminPlace {
        id: number;
        city_id: number;
        name: string;
        name_en: string;
        category: PlaceCategory;
        description: string | null;
        ticket_price: string | null;
        booking_url: string | null;
        rating: string | null;
        opening_hours: string | null;
        is_indoor: boolean;
        family_friendly: boolean;
        wheelchair_accessible: boolean;
        prayer_facilities: boolean;
        closed_friday: boolean;
    }

    let {
        place,
    }: {
        place: AdminPlace;
    } = $props();

    const categories = Object.entries(categoryMeta) as [
        PlaceCategory,
        (typeof categoryMeta)[PlaceCategory],
    ][];

    const initialPlace = untrack(() => place);

    const form = useForm({
        name: initialPlace.name,
        name_en: initialPlace.name_en,
        category: initialPlace.category,
        description: initialPlace.description ?? '',
        ticket_price: initialPlace.ticket_price ?? '',
        booking_url: initialPlace.booking_url ?? '',
        rating: initialPlace.rating ?? '',
        opening_hours: initialPlace.opening_hours ?? '',
        is_indoor: initialPlace.is_indoor,
        family_friendly: initialPlace.family_friendly,
        wheelchair_accessible: initialPlace.wheelchair_accessible,
        prayer_facilities: initialPlace.prayer_facilities,
        closed_friday: initialPlace.closed_friday,
    });

    const inputClass =
        'min-h-12 w-full rounded-xl bg-card px-4 text-base font-bold text-foreground outline-none ring-1 ring-border focus:ring-2 focus:ring-emerald-400';
    const labelClass = 'text-sm font-bold text-secondary-foreground';
    const checkRowClass =
        'flex min-h-12 cursor-pointer items-center gap-3 rounded-xl bg-muted/40 px-4 ring-1 ring-border';
    const checkboxClass = 'size-5 shrink-0 accent-emerald-600';

    function submit(event: SubmitEvent): void {
        event.preventDefault();

        form.put(adminPlaceUpdate.url(place.id), {
            preserveScroll: true,
            onSuccess: () => toast.success(t('admin.save')),
        });
    }
</script>

<AppHead title={t('admin.edit')} />

<div class="mx-auto w-full max-w-4xl px-4 py-6 md:px-6">
    <AdminNav />

    <header class="mt-6">
        <h1 class="text-2xl font-bold text-foreground sm:text-3xl">
            {t('admin.edit')}
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">{place.name}</p>
    </header>

    <form
        class="mt-6 grid gap-5 rounded-[20px] bg-card p-5 shadow-sm ring-1 ring-border sm:p-6"
        onsubmit={submit}
    >
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2">
                <span class={labelClass}>{t('admin.fields.name')}</span>
                <input
                    bind:value={form.name}
                    class={inputClass}
                    maxlength="255"
                    required
                />
                <InputError message={form.errors.name} />
            </label>

            <label class="grid gap-2">
                <span class={labelClass}>{t('admin.fields.nameEn')}</span>
                <input
                    bind:value={form.name_en}
                    class={inputClass}
                    maxlength="255"
                    required
                />
                <InputError message={form.errors.name_en} />
            </label>
        </div>

        <label class="grid gap-2">
            <span class={labelClass}>{t('admin.fields.category')}</span>
            <select bind:value={form.category} class={inputClass}>
                {#each categories as [key] (key)}
                    <option value={key}>{t(`categories.${key}`)}</option>
                {/each}
            </select>
            <InputError message={form.errors.category} />
        </label>

        <label class="grid gap-2">
            <span class={labelClass}>{t('admin.fields.description')}</span>
            <textarea
                bind:value={form.description}
                class="{inputClass} min-h-28 py-3 font-normal"
                rows="4"
            ></textarea>
            <InputError message={form.errors.description} />
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2">
                <span class={labelClass}>
                    {t('admin.fields.ticketPrice')}
                </span>
                <input
                    type="number"
                    min="0"
                    step="0.01"
                    value={form.ticket_price}
                    oninput={(event) =>
                        (form.ticket_price = event.currentTarget.value)}
                    class={inputClass}
                />
                <InputError message={form.errors.ticket_price} />
            </label>

            <label class="grid gap-2">
                <span class={labelClass}>{t('admin.fields.rating')}</span>
                <input
                    type="number"
                    min="0"
                    max="5"
                    step="0.1"
                    value={form.rating}
                    oninput={(event) =>
                        (form.rating = event.currentTarget.value)}
                    class={inputClass}
                />
                <InputError message={form.errors.rating} />
            </label>
        </div>

        <label class="grid gap-2">
            <span class={labelClass}>{t('admin.fields.openingHours')}</span>
            <input
                bind:value={form.opening_hours}
                class={inputClass}
                maxlength="255"
            />
            <InputError message={form.errors.opening_hours} />
        </label>

        <label class="grid gap-2">
            <span class={labelClass}>{t('admin.fields.bookingUrl')}</span>
            <input
                type="url"
                bind:value={form.booking_url}
                class={inputClass}
                maxlength="2048"
                placeholder="https://"
                dir="ltr"
            />
            <InputError message={form.errors.booking_url} />
        </label>

        <div class="grid gap-3 sm:grid-cols-2">
            <label class={checkRowClass}>
                <input
                    type="checkbox"
                    bind:checked={form.is_indoor}
                    class={checkboxClass}
                />
                <span class={labelClass}>{t('admin.fields.indoor')}</span>
            </label>
            <label class={checkRowClass}>
                <input
                    type="checkbox"
                    bind:checked={form.family_friendly}
                    class={checkboxClass}
                />
                <span class={labelClass}>
                    {t('admin.fields.familyFriendly')}
                </span>
            </label>
            <label class={checkRowClass}>
                <input
                    type="checkbox"
                    bind:checked={form.wheelchair_accessible}
                    class={checkboxClass}
                />
                <span class={labelClass}>{t('admin.fields.wheelchair')}</span>
            </label>
            <label class={checkRowClass}>
                <input
                    type="checkbox"
                    bind:checked={form.prayer_facilities}
                    class={checkboxClass}
                />
                <span class={labelClass}>
                    {t('admin.fields.prayerFacilities')}
                </span>
            </label>
            <label class={checkRowClass}>
                <input
                    type="checkbox"
                    bind:checked={form.closed_friday}
                    class={checkboxClass}
                />
                <span class={labelClass}>
                    {t('admin.fields.closedFriday')}
                </span>
            </label>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button
                type="submit"
                disabled={form.processing}
                class="inline-flex min-h-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-[0.98] disabled:opacity-60"
            >
                {t('admin.save')}
            </button>
            <Link
                href={adminPlaces.url()}
                class="inline-flex min-h-12 items-center justify-center rounded-xl bg-card px-6 text-sm font-bold text-secondary-foreground ring-1 ring-border transition hover:ring-ring active:scale-[0.98]"
            >
                {t('admin.cancel')}
            </Link>
        </div>
    </form>
</div>

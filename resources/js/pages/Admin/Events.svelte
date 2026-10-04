<script lang="ts">
    import { Link, router, useForm } from '@inertiajs/svelte';
    import ExternalLink from '@lucide/svelte/icons/external-link';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import { toast } from 'svelte-sonner';
    import AdminNav from '@/components/AdminNav.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table';
    import { formatDate, t } from '@/lib/i18n.svelte';
    import { cityName } from '@/lib/localize';
    import { cities } from '@/lib/tourism.svelte';
    import {
        destroy as adminEventDestroy,
        store as adminEventStore,
        update as adminEventUpdate,
    } from '@/routes/admin/events';

    interface AdminEvent {
        id: number;
        city_id: number;
        city_name: string | null;
        name: string;
        description: string | null;
        start_date: string;
        end_date: string | null;
        image: string | null;
        url: string | null;
    }

    let {
        events,
    }: {
        events: AdminEvent[];
    } = $props();

    let editingId = $state<number | null>(null);

    const form = useForm({
        city_id: cities[0]?.id ?? '',
        name: '',
        description: '',
        start_date: '',
        end_date: '',
        image: '',
        url: '',
    });

    const inputClass =
        'min-h-12 w-full rounded-xl bg-card px-4 text-base font-bold text-foreground outline-none ring-1 ring-border focus:ring-2 focus:ring-emerald-400';
    const labelClass = 'text-sm font-bold text-secondary-foreground';
    const editButtonClass =
        'inline-flex min-h-9 items-center gap-1.5 rounded-lg px-3 text-sm font-bold text-emerald-700 ring-1 ring-emerald-200 transition hover:bg-emerald-50 active:scale-[0.98] dark:text-emerald-300 dark:ring-emerald-900/60 dark:hover:bg-emerald-950/40';
    const deleteButtonClass =
        'inline-flex min-h-9 items-center gap-1.5 rounded-lg px-3 text-sm font-bold text-red-600 ring-1 ring-red-200 transition hover:bg-red-50 active:scale-[0.98] dark:text-red-400 dark:ring-red-900/60 dark:hover:bg-red-950/40';

    function resetForm(): void {
        editingId = null;
        form.reset();
        form.clearErrors();
    }

    function startEdit(event: AdminEvent): void {
        editingId = event.id;
        form.clearErrors();
        form.city_id = event.city_id;
        form.name = event.name;
        form.description = event.description ?? '';
        form.start_date = event.start_date;
        form.end_date = event.end_date ?? '';
        form.image = event.image ?? '';
        form.url = event.url ?? '';
    }

    function submit(event: SubmitEvent): void {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(t('admin.save'));
                resetForm();
            },
        };

        if (editingId === null) {
            form.post(adminEventStore.url(), options);
        } else {
            form.put(adminEventUpdate.url(editingId), options);
        }
    }

    function removeEvent(event: AdminEvent): void {
        if (!window.confirm(t('admin.confirmDelete'))) {
            return;
        }

        router.delete(adminEventDestroy.url(event.id), {
            preserveScroll: true,
            onSuccess: () => toast.success(t('admin.delete')),
        });
    }
</script>

<AppHead title={t('admin.events')} />

<div class="mx-auto w-full max-w-6xl px-4 py-6 md:px-6">
    <AdminNav />

    <header class="mt-6">
        <h1 class="text-2xl font-bold text-foreground sm:text-3xl">
            {t('admin.events')}
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">{t('admin.subtitle')}</p>
    </header>

    <form
        class="mt-6 grid gap-4 rounded-[20px] bg-card p-5 shadow-sm ring-1 ring-border sm:p-6"
        onsubmit={submit}
    >
        <h2 class="text-lg font-bold text-foreground">
            {editingId === null ? t('admin.add') : t('admin.edit')}
        </h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2">
                <span class={labelClass}>{t('admin.fields.city')}</span>
                <select bind:value={form.city_id} class={inputClass} required>
                    {#if cities.length === 0}
                        <option value="">{t('admin.empty')}</option>
                    {/if}
                    {#each cities as city (city.id)}
                        <option value={city.id}>{cityName(city)}</option>
                    {/each}
                </select>
                <InputError message={form.errors.city_id} />
            </label>

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
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2">
                <span class={labelClass}>{t('admin.fields.startDate')}</span>
                <input
                    type="date"
                    bind:value={form.start_date}
                    class={inputClass}
                    required
                />
                <InputError message={form.errors.start_date} />
            </label>

            <label class="grid gap-2">
                <span class={labelClass}>{t('admin.fields.endDate')}</span>
                <input
                    type="date"
                    bind:value={form.end_date}
                    class={inputClass}
                />
                <InputError message={form.errors.end_date} />
            </label>
        </div>

        <label class="grid gap-2">
            <span class={labelClass}>{t('admin.fields.description')}</span>
            <textarea
                bind:value={form.description}
                class="{inputClass} min-h-24 py-3 font-normal"
                rows="3"
            ></textarea>
            <InputError message={form.errors.description} />
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2">
                <span class={labelClass}>{t('admin.fields.image')}</span>
                <input bind:value={form.image} class={inputClass} />
                <InputError message={form.errors.image} />
            </label>

            <label class="grid gap-2">
                <span class={labelClass}>{t('admin.fields.url')}</span>
                <input type="url" bind:value={form.url} class={inputClass} />
                <InputError message={form.errors.url} />
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
            {#if editingId !== null}
                <button
                    type="button"
                    onclick={resetForm}
                    class="inline-flex min-h-12 items-center justify-center rounded-xl bg-card px-6 text-sm font-bold text-secondary-foreground ring-1 ring-border transition hover:ring-ring active:scale-[0.98]"
                >
                    {t('admin.cancel')}
                </button>
            {/if}
        </div>
    </form>

    {#if events.length === 0}
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
                            {t('admin.fields.city')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.startDate')}
                        </TableHead>
                        <TableHead class="px-4 text-start">
                            {t('admin.fields.url')}
                        </TableHead>
                        <TableHead class="px-4 text-end">
                            {t('admin.actions')}
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {#each events as event (event.id)}
                        <TableRow>
                            <TableCell
                                class="px-4 py-3 text-start font-bold text-foreground"
                            >
                                {event.name}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {event.city_name ?? '—'}
                            </TableCell>
                            <TableCell
                                class="px-4 py-3 text-start text-muted-foreground"
                            >
                                {formatDate(event.start_date)}
                                {#if event.end_date}
                                    — {formatDate(event.end_date)}
                                {/if}
                            </TableCell>
                            <TableCell class="px-4 py-3 text-start">
                                {#if event.url}
                                    <a
                                        href={event.url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label={t('admin.fields.url')}
                                        class="inline-flex size-9 items-center justify-center rounded-lg text-muted-foreground ring-1 ring-border transition hover:text-foreground hover:ring-ring"
                                    >
                                        <ExternalLink class="size-4" />
                                    </a>
                                {/if}
                            </TableCell>
                            <TableCell class="px-4 py-3 text-end">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <button
                                        type="button"
                                        onclick={() => startEdit(event)}
                                        class={editButtonClass}
                                    >
                                        <Pencil class="size-3.5" />
                                        {t('admin.edit')}
                                    </button>
                                    <button
                                        type="button"
                                        onclick={() => removeEvent(event)}
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

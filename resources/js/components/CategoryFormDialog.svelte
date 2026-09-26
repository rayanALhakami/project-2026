<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
    import type { Category, TransactionType } from '@/types';
    import { toUrl } from '@/lib/utils';
    import categories from '@/routes/categories';

    const EMOJI_GROUPS = [
        {
            label: 'طعام وشراب',
            emojis: ['🍽️', '🍔', '🍕', '☕', '🛒', '🥘', '🍎', '🍰', '🧁', '🍩'],
        },
        {
            label: 'تسوق',
            emojis: ['🛍️', '👕', '👟', '💄', '🎁', '💍', '👜', '🕶️'],
        },
        {
            label: 'مواصلات',
            emojis: ['🚗', '🚕', '🚌', '⛽', '🚇', '✈️', '🅿️', '🚲'],
        },
        {
            label: 'فواتير وخدمات',
            emojis: ['🧾', '💡', '💧', '📱', '🌐', '🔌', '🏠', '📡'],
        },
        {
            label: 'صحة',
            emojis: ['🏥', '💊', '🩺', '🦷', '👓', '🧘', '💉'],
        },
        {
            label: 'ترفيه',
            emojis: ['🎬', '🎮', '🎵', '⚽', '🎳', '📺', '🎟️', '🏋️'],
        },
        {
            label: 'تعليم',
            emojis: ['📚', '🎓', '✏️', '🖥️', '📝', '🔬'],
        },
        {
            label: 'عمل ودخل',
            emojis: ['💼', '💰', '🧑‍💻', '📈', '🏦', '💵', '🤝', '🏢'],
        },
        {
            label: 'أخرى',
            emojis: ['🏷️', '❤️', '🐾', '🎉', '🧳', '🛠️', '🔄', '📦'],
        },
    ];

    const PALETTE = [
        '#0066cc',
        '#34c759',
        '#ff9500',
        '#ff3b30',
        '#5856d6',
        '#5ac8fa',
        '#af52de',
        '#ff2d55',
        '#0a84ff',
        '#30d158',
        '#ff9f0a',
        '#8e8e93',
    ];

    let {
        open = $bindable(false),
        category = null,
        defaultType = 'expense',
    }: {
        open?: boolean;
        category?: Category | null;
        defaultType?: TransactionType;
    } = $props();

    const form = useForm(() => ({
        name: category?.name ?? '',
        type: (category?.type ?? defaultType) as TransactionType,
        color: category?.color ?? PALETTE[0],
        icon: category?.icon ?? '',
    }));

    let emojiOpen = $state(false);

    $effect(() => {
        if (!open) {
            return;
        }
        untrack(() => {
            form.reset();
            form.clearErrors();
            emojiOpen = false;
        });
    });

    function submit(): void {
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                open = false;
            },
        };

        if (category) {
            form.put(toUrl(categories.update({ category: category.id })), options);
        } else {
            form.post(toUrl(categories.store()), options);
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
                    {category ? 'تعديل فئة' : 'إضافة فئة'}
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
                    onclick={() => (form.type = 'expense')}
                    class="rounded-full px-4 py-2 text-sm font-medium transition-colors {form.type ===
                    'expense'
                        ? 'bg-card text-foreground shadow-xs'
                        : 'text-muted-foreground'}"
                >
                    مصروف
                </button>
                <button
                    type="button"
                    onclick={() => (form.type = 'income')}
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
                <label for="category-name" class="text-sm text-muted-foreground">
                    الاسم
                </label>
                <input
                    id="category-name"
                    type="text"
                    placeholder="مثال: بقالة"
                    bind:value={form.name}
                    class="h-11 rounded-xl border border-border bg-background px-4 text-foreground outline-none focus:border-primary"
                />
                {#if form.errors.name}
                    <p class="text-xs text-destructive">{form.errors.name}</p>
                {/if}
            </div>

            <div class="grid gap-1.5">
                <span class="text-sm text-muted-foreground">الأيقونة</span>
                <Popover bind:open={emojiOpen}>
                    <PopoverTrigger
                        type="button"
                        class="flex h-11 w-full items-center gap-3 rounded-xl border border-border bg-background px-4 text-start outline-none transition-colors focus:border-primary"
                    >
                        <span
                            class="flex size-7 shrink-0 items-center justify-center rounded-full bg-secondary text-lg"
                        >
                            {form.icon || '🏷️'}
                        </span>
                        <span
                            class={form.icon
                                ? 'text-foreground'
                                : 'text-muted-foreground'}
                        >
                            {form.icon ? 'تغيير الأيقونة' : 'اختر أيقونة'}
                        </span>
                    </PopoverTrigger>
                    <PopoverContent
                        class="z-[60] w-[min(20rem,calc(100vw-3rem))] p-3"
                    >
                        <div class="max-h-64 overflow-y-auto pe-1">
                            {#each EMOJI_GROUPS as group (group.label)}
                                <div class="mb-2">
                                    <p
                                        class="mb-1.5 text-xs font-medium text-muted-foreground"
                                    >
                                        {group.label}
                                    </p>
                                    <div class="grid grid-cols-7 gap-1">
                                        {#each group.emojis as emoji (emoji)}
                                            <button
                                                type="button"
                                                onclick={() => {
                                                    form.icon = emoji;
                                                    emojiOpen = false;
                                                }}
                                                class="flex size-9 items-center justify-center rounded-lg text-lg transition-colors hover:bg-secondary {form.icon ===
                                                emoji
                                                    ? 'bg-secondary ring-1 ring-primary'
                                                    : ''}"
                                                aria-label={emoji}
                                            >
                                                {emoji}
                                            </button>
                                        {/each}
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </PopoverContent>
                </Popover>
                {#if form.errors.icon}
                    <p class="text-xs text-destructive">{form.errors.icon}</p>
                {/if}
            </div>

            <div class="grid gap-2">
                <span class="text-sm text-muted-foreground">اللون</span>
                <div class="flex flex-wrap gap-2">
                    {#each PALETTE as color (color)}
                        <button
                            type="button"
                            onclick={() => (form.color = color)}
                            class="size-8 rounded-full transition-transform {form.color === color
                                ? 'ring-2 ring-foreground ring-offset-2 ring-offset-card'
                                : ''}"
                            style="background-color: {color};"
                            aria-label={color}
                        ></button>
                    {/each}
                </div>
                {#if form.errors.color}
                    <p class="text-xs text-destructive">{form.errors.color}</p>
                {/if}
            </div>

            <button
                type="submit"
                disabled={form.processing}
                class="mt-1 inline-flex h-11 items-center justify-center rounded-full bg-primary px-6 text-[15px] font-medium text-primary-foreground transition active:scale-95 disabled:opacity-50"
            >
                {form.processing ? 'جارٍ الحفظ...' : category ? 'حفظ التعديلات' : 'إضافة'}
            </button>
        </form>
    </div>
{/if}

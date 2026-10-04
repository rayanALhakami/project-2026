<script lang="ts">
    import Accessibility from '@lucide/svelte/icons/accessibility';
    import BadgeCheck from '@lucide/svelte/icons/badge-check';
    import Landmark from '@lucide/svelte/icons/landmark';
    import MoonStar from '@lucide/svelte/icons/moon-star';
    import Users from '@lucide/svelte/icons/users';
    import { t } from '@/lib/i18n.svelte';
    import { cn } from '@/lib/utils';
    import type { Place } from '@/types';

    let {
        place,
        class: className = '',
        tone = 'solid',
    }: {
        place: Place;
        class?: string;
        tone?: 'solid' | 'overlay';
    } = $props();

    const badges = $derived.by(() => {
        const list: { key: string; label: string; icon: typeof Landmark }[] = [];

        if (place.tags.includes('يونسكو')) {
            list.push({
                key: 'unesco',
                label: t('badges.unesco'),
                icon: Landmark,
            });
        }

        if (place.ticketPrice === 0) {
            list.push({ key: 'free', label: t('common.free'), icon: BadgeCheck });
        }

        if (place.familyFriendly) {
            list.push({
                key: 'family',
                label: t('common.family'),
                icon: Users,
            });
        }

        if (place.wheelchairAccessible) {
            list.push({
                key: 'wheelchair',
                label: t('badges.wheelchair'),
                icon: Accessibility,
            });
        }

        if (place.hasPrayerFacilities) {
            list.push({
                key: 'prayer',
                label: t('badges.prayerRoom'),
                icon: MoonStar,
            });
        }

        return list;
    });

    const toneClass = $derived(
        tone === 'overlay'
            ? 'border-white/25 bg-black/35 text-white backdrop-blur'
            : 'border-border bg-card text-foreground',
    );
</script>

{#if badges.length > 0}
    <ul class={cn('flex flex-wrap gap-1.5', className)}>
        {#each badges as badge (badge.key)}
            <li
                class={cn(
                    'inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-semibold',
                    toneClass,
                )}
            >
                <badge.icon class="size-3.5" aria-hidden="true" />
                {badge.label}
            </li>
        {/each}
    </ul>
{/if}

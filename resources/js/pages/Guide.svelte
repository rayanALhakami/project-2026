<script lang="ts">
    import Banknote from '@lucide/svelte/icons/banknote';
    import Compass from '@lucide/svelte/icons/compass';
    import HeartHandshake from '@lucide/svelte/icons/heart-handshake';
    import Info from '@lucide/svelte/icons/info';
    import Languages from '@lucide/svelte/icons/languages';
    import Plug from '@lucide/svelte/icons/plug';
    import Siren from '@lucide/svelte/icons/siren';
    import Smartphone from '@lucide/svelte/icons/smartphone';
    import Stamp from '@lucide/svelte/icons/stamp';
    import AppHead from '@/components/AppHead.svelte';
    import SiteHeader from '@/components/SiteHeader.svelte';
    import { t } from '@/lib/i18n.svelte';
    import {
        guideSections,
        type GuideSectionId,
    } from '@/lib/tourist-guide';
    import type { Component } from 'svelte';

    const sections = $derived(guideSections());

    const sectionIcons: Record<
        GuideSectionId,
        Component<{ class?: string }>
    > = {
        visa: Stamp,
        sim: Smartphone,
        money: Banknote,
        phrases: Languages,
        etiquette: HeartHandshake,
        emergency: Siren,
        practical: Plug,
    };
</script>

<AppHead title={t('guide.title')} description={t('meta.guideDescription')} />

<SiteHeader active="guide" transparent />

<div class="min-h-dvh bg-background">
    <section
        class="bg-gradient-to-br from-[#16344f] via-[#0b2337] to-[#071523] pb-16 pt-28 text-white"
    >
        <div class="mx-auto w-full max-w-6xl px-4 md:px-6">
            <span
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-emerald-200 ring-1 ring-white/15"
            >
                <Info class="size-4" aria-hidden="true" />
                {t('guide.badge')}
            </span>
            <h1 class="mt-4 text-3xl font-bold sm:text-4xl">
                {t('guide.title')}
            </h1>
            <p class="mt-2 max-w-2xl text-slate-300">
                {t('guide.subtitle')}
            </p>
        </div>
    </section>

    <div class="mx-auto -mt-8 w-full max-w-6xl px-4 pb-20 md:px-6">
        <div class="space-y-6">
            {#each sections as section, sectionIndex (section.id)}
                {@const Icon = sectionIcons[section.id]}
                <article
                    class="motion-view overflow-hidden rounded-[20px] bg-card shadow-lg shadow-slate-900/5 ring-1 ring-border"
                    style="--stagger: {sectionIndex}"
                >
                    <header
                        class="flex items-start gap-4 border-b border-border/60 px-5 py-4 sm:px-6"
                    >
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-700 text-white shadow-lg shadow-emerald-950/30"
                        >
                            <Icon class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-foreground">
                                {section.title}
                            </h2>
                            <p class="mt-0.5 text-sm text-muted-foreground">
                                {section.intro}
                            </p>
                        </div>
                    </header>
                    <dl class="divide-y divide-border/50">
                        {#each section.items as item (item.title)}
                            <div class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:gap-6 sm:px-6">
                                <dt
                                    class="w-44 shrink-0 text-sm font-bold text-emerald-700 dark:text-emerald-300"
                                >
                                    {item.title}
                                </dt>
                                <dd
                                    class="text-sm leading-relaxed text-muted-foreground"
                                >
                                    {item.body}
                                </dd>
                            </div>
                        {/each}
                    </dl>
                </article>
            {/each}
        </div>

        <p
            class="motion-view mt-8 flex items-start gap-2 rounded-2xl border border-amber-200 bg-amber-50/70 px-4 py-3 text-sm font-semibold text-amber-800 dark:border-amber-900/70 dark:bg-amber-950/30 dark:text-amber-300"
        >
            <Compass class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            {t('guide.disclaimer')}
        </p>
    </div>

    <footer
        class="border-t border-white/10 bg-[#08131f] pt-6 pb-bottom-nav text-slate-400 md:pb-6"
    >
        <div
            class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 text-xs sm:flex-row md:px-6"
        >
            <p>© {new Date().getFullYear()} {t('app.name')}</p>
            <p>{t('preview.rights')}</p>
        </div>
    </footer>
</div>

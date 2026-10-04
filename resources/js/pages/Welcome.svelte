<script lang="ts">
    import type { Component } from 'svelte';
    import { Link, page } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import Camera from '@lucide/svelte/icons/camera';
    import Check from '@lucide/svelte/icons/check';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import Clock from '@lucide/svelte/icons/clock';
    import CloudSun from '@lucide/svelte/icons/cloud-sun';
    import Compass from '@lucide/svelte/icons/compass';
    import Languages from '@lucide/svelte/icons/languages';
    import Mail from '@lucide/svelte/icons/mail';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import MessageCircle from '@lucide/svelte/icons/message-circle';
    import Mic from '@lucide/svelte/icons/mic';
    import Phone from '@lucide/svelte/icons/phone';
    import Route from '@lucide/svelte/icons/route';
    import Send from '@lucide/svelte/icons/send';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Star from '@lucide/svelte/icons/star';
    import Users from '@lucide/svelte/icons/users';
    import Wallet from '@lucide/svelte/icons/wallet';
    import AppHead from '@/components/AppHead.svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import CityArt from '@/components/CityArt.svelte';
    import CityMotif from '@/components/CityMotif.svelte';
    import GuestStartMenu from '@/components/GuestStartMenu.svelte';
    import PlaceMedia from '@/components/PlaceMedia.svelte';
    import SiteHeader from '@/components/SiteHeader.svelte';
    import { cityImage } from '@/lib/city-images';
    import { formatNumber, locales, t } from '@/lib/i18n.svelte';
    import type { MessageKey } from '@/lib/i18n/messages';
    import { cityName, placeDescription, placeName } from '@/lib/localize';
    import { cities, places, placesByCity, topPlaces } from '@/lib/tourism.svelte';
    import { toUrl } from '@/lib/utils';
    import {
        assistant,
        dashboard,
        places as placesRoute,
        register,
        translate,
        trips,
    } from '@/routes';
    import type { Place, PlaceCategory } from '@/types';

    const auth = $derived(page.props.auth);
    const startHref = $derived(toUrl(auth.user ? dashboard() : register()));

    let submitted = $state(false);

    const featuredCities = cities;
    const featuredPlaces = topPlaces(4);

    let failedCityImages = $state<Record<number, boolean>>({});

    const heroCityIds = [1, 2, 9, 4, 7, 8, 5, 3, 6];
    const heroSlides = heroCityIds.map((id) => ({
        id,
        src: `/images/hero/${id}.jpg`,
        city: cities.find((item) => item.id === id),
    }));

    let heroIndex = $state(0);
    const currentHeroCity = $derived(heroSlides[heroIndex]?.city);

    $effect(() => {
        if (
            typeof window === 'undefined' ||
            window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            return;
        }

        const timer = setInterval(() => {
            heroIndex = (heroIndex + 1) % heroSlides.length;
        }, 6000);

        return () => clearInterval(timer);
    });

    const placeCity = (place: Place): string => {
        const city = cities.find((item) => item.id === place.cityId);

        return city ? cityName(city) : '';
    };

    const categoryLabel = (category: PlaceCategory): string =>
        t(`categories.${category}` as MessageKey);

    const priceLabel = (place: Place): string =>
        place.ticketPrice
            ? `${formatNumber(place.ticketPrice)} ${t('common.currency')}`
            : t('common.free');

    const aboutPoints = $derived.by(
        (): { title: string; desc: string; icon: Component<{ class?: string }> }[] => [
            {
                title: t('preview.aboutPoint1Title'),
                desc: t('preview.aboutPoint1Desc'),
                icon: Route,
            },
            {
                title: t('preview.aboutPoint2Title'),
                desc: t('preview.aboutPoint2Desc'),
                icon: Wallet,
            },
            {
                title: t('preview.aboutPoint3Title'),
                desc: t('preview.aboutPoint3Desc'),
                icon: Languages,
            },
        ],
    );

    const whyCards = $derived.by(
        (): { title: string; desc: string; icon: Component<{ class?: string }> }[] => [
            {
                title: t('preview.why1Title'),
                desc: t('preview.why1Desc'),
                icon: MessageCircle,
            },
            {
                title: t('preview.why2Title'),
                desc: t('preview.why2Desc'),
                icon: Mic,
            },
            {
                title: t('preview.why3Title'),
                desc: t('preview.why3Desc'),
                icon: Camera,
            },
            {
                title: t('preview.why4Title'),
                desc: t('preview.why4Desc'),
                icon: CloudSun,
            },
        ],
    );

    const testimonials = $derived.by(
        (): { quote: string; name: string; city: string }[] => [
            {
                quote: t('preview.testimonial1'),
                name: t('preview.testimonial1Name'),
                city: t('preview.testimonial1City'),
            },
            {
                quote: t('preview.testimonial2'),
                name: t('preview.testimonial2Name'),
                city: t('preview.testimonial2City'),
            },
            {
                quote: t('preview.testimonial3'),
                name: t('preview.testimonial3Name'),
                city: t('preview.testimonial3City'),
            },
        ],
    );

    function initialsOf(name: string): string {
        return name
            .split(' ')
            .slice(0, 2)
            .map((word) => word.charAt(0))
            .join('');
    }

    const inputClass =
        'w-full rounded-xl border border-border bg-card px-4 py-3 text-sm text-foreground transition placeholder:text-muted-foreground focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:ring-emerald-900/60 dark:focus:ring-emerald-900/50';
    const labelClass =
        'mb-1.5 block text-sm font-semibold text-secondary-foreground';

    function onSubmit(event: SubmitEvent): void {
        event.preventDefault();
        submitted = true;
    }
</script>

<AppHead title={t('welcome.title')} />

<div class="bg-background text-foreground">
    <SiteHeader active="home" />

    <section
        id="top"
        class="relative flex min-h-[92vh] items-center overflow-hidden text-white"
    >
        <div
            class="absolute inset-0 bg-gradient-to-br from-[#16344f] via-[#0b2337] to-[#071523]"
        ></div>
        {#each heroSlides as slide, index (slide.id)}
            <img
                src={slide.src}
                alt=""
                class="absolute inset-0 h-full w-full object-cover transition-all ease-out {index ===
                heroIndex
                    ? 'scale-110 opacity-100 duration-[7000ms]'
                    : 'scale-100 opacity-0 duration-[1500ms]'}"
                loading={index === 0 ? 'eager' : 'lazy'}
                decoding="async"
            />
        {/each}
        <div
            class="absolute inset-0 bg-gradient-to-t from-[#071523] via-[#071523]/60 to-[#071523]/40"
        ></div>
        <div
            class="pointer-events-none absolute inset-x-0 bottom-0 h-40 text-white opacity-20"
        >
            <CityMotif type="skyline" class="h-full w-full" />
        </div>

        <div class="relative mx-auto w-full max-w-6xl px-4 pb-14 pt-32 md:px-6">
            <span
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-bold text-emerald-200 ring-1 ring-white/15 backdrop-blur"
            >
                <Sparkles class="size-4" />
                {t('preview.badge')}
            </span>

            <h1
                class="mt-5 max-w-3xl text-4xl font-bold leading-[1.25] sm:text-5xl md:text-6xl"
            >
                {t('welcome.title')}
            </h1>
            <p class="mt-5 max-w-2xl text-base text-slate-200 sm:text-lg">
                {t('preview.heroSubtitle')}
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                {#if auth.user}
                    <Link
                        href={startHref}
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-7 text-base font-bold text-white shadow-xl shadow-emerald-950/30 transition hover:brightness-110 active:scale-[0.98]"
                    >
                        {t('welcome.browse')}
                        <ArrowLeft class="size-4 ltr:rotate-180" />
                    </Link>
                {:else}
                    <GuestStartMenu
                        label={t('welcome.start')}
                        arrow
                        buttonClass="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-7 text-base font-bold text-white shadow-xl shadow-emerald-950/30 transition hover:brightness-110 active:scale-[0.98]"
                    />
                {/if}
                <a
                    href="#features"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/35 px-7 text-base font-semibold text-white transition hover:bg-white/10 active:scale-[0.98]"
                >
                    {t('preview.ctaFeatures')}
                </a>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-1.5">
                    {#each heroSlides as slide, index (slide.id)}
                        <button
                            type="button"
                            onclick={() => (heroIndex = index)}
                            aria-label={slide.city ? cityName(slide.city) : ''}
                            class="h-1.5 rounded-full transition-all {index ===
                            heroIndex
                                ? 'w-7 bg-emerald-400'
                                : 'w-1.5 bg-white/40 hover:bg-white/70'}"
                        ></button>
                    {/each}
                </div>
                {#if currentHeroCity}
                    <span class="text-xs font-semibold text-slate-200">
                        {cityName(currentHeroCity)} · {currentHeroCity.nameEn}
                    </span>
                {/if}
            </div>

            <dl
                class="mt-14 grid grid-cols-2 gap-6 border-t border-white/15 pt-8 sm:grid-cols-4"
            >
                <div>
                    <dt class="text-xs font-semibold text-slate-300">
                        {t('preview.statCities')}
                    </dt>
                    <dd class="text-3xl font-bold">
                        {formatNumber(cities.length)}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-300">
                        {t('preview.statPlaces')}
                    </dt>
                    <dd class="text-3xl font-bold">
                        +{formatNumber(places.length)}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-300">
                        {t('preview.statLanguages')}
                    </dt>
                    <dd class="text-3xl font-bold">
                        +{formatNumber(locales.length)}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-300">
                        {t('preview.statSupport')}
                    </dt>
                    <dd class="text-3xl font-bold">
                        {t('preview.statSupportValue')}
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    <section class="bg-background py-16 sm:py-24">
        <div
            class="mx-auto grid max-w-6xl items-center gap-12 px-4 md:grid-cols-2 md:px-6"
        >
            <div>
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 dark:bg-emerald-950/50 px-3 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                >
                    <Compass class="size-4" />
                    {t('preview.aboutBadge')}
                </span>
                <h2 class="mt-4 text-3xl font-bold sm:text-4xl">
                    {t('preview.aboutTitle')}
                </h2>
                <p class="mt-4 text-base text-muted-foreground sm:text-lg">
                    {t('preview.aboutDesc')}
                </p>

                <ul class="mt-8 flex flex-col gap-5">
                    {#each aboutPoints as point (point.title)}
                        <li class="flex items-start gap-4">
                            <span
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <point.icon class="size-5" />
                            </span>
                            <span>
                                <span class="block font-bold">{point.title}</span>
                                <span class="block text-sm text-muted-foreground">
                                    {point.desc}
                                </span>
                            </span>
                        </li>
                    {/each}
                </ul>
            </div>

            <div class="relative">
                <img
                    src="/images/hero/3.jpg"
                    alt=""
                    class="h-72 w-full rounded-[24px] object-cover shadow-2xl shadow-slate-900/20 sm:h-96"
                    loading="lazy"
                    decoding="async"
                />
                <div
                    class="absolute -bottom-6 start-6 rounded-2xl bg-[#0b1e33] px-5 py-3.5 text-white shadow-xl"
                >
                    <p class="text-2xl font-bold">{t('preview.aboutStatValue')}</p>
                    <p class="text-xs text-slate-300">
                        {t('preview.aboutStatLabel')}
                    </p>
                </div>
                <div
                    class="absolute -top-5 end-4 rounded-2xl bg-card px-4 py-3 shadow-xl ring-1 ring-border/70"
                >
                    <p class="text-xl font-bold text-emerald-700 dark:text-emerald-400">
                        {t('preview.aboutFeatureValue')}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {t('preview.aboutFeatureLabel')}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="scroll-mt-20 bg-[#0b1e33] py-16 text-white sm:py-24">
        <div class="mx-auto max-w-6xl px-4 md:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-emerald-200 ring-1 ring-white/15"
                >
                    <Sparkles class="size-4" />
                    {t('preview.whyBadge')}
                </span>
                <h2 class="mt-4 text-3xl font-bold sm:text-4xl">
                    {t('preview.whyTitle')}
                </h2>
                <p class="mt-3 text-slate-300">{t('preview.whyDesc')}</p>
            </div>

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                {#each whyCards as card (card.title)}
                    <div
                        class="rounded-[20px] border border-white/10 bg-white/[0.04] p-6 transition hover:border-emerald-400/30 hover:bg-white/[0.07]"
                    >
                        <span
                            class="flex size-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400/25 to-teal-500/20 text-emerald-300 ring-1 ring-emerald-400/25"
                        >
                            <card.icon class="size-6" />
                        </span>
                        <h3 class="mt-5 text-lg font-bold">{card.title}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-300">
                            {card.desc}
                        </p>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <section id="destinations" class="scroll-mt-20 bg-muted/40 py-16 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 md:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 dark:bg-emerald-950/50 px-3 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                >
                    <MapPin class="size-4" />
                    {t('preview.destinationsBadge')}
                </span>
                <h2 class="mt-4 text-3xl font-bold sm:text-4xl">
                    {t('preview.destinationsTitle')}
                </h2>
                <p class="mt-3 text-muted-foreground">
                    {t('preview.destinationsDesc')}
                </p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {#each featuredCities as city (city.id)}
                    <Link
                        href={toUrl(placesRoute())}
                        class="group overflow-hidden rounded-[20px] bg-card shadow-sm ring-1 ring-border transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div class="relative h-44 overflow-hidden">
                            {#if cityImage(city.id) && !failedCityImages[city.id]}
                                <img
                                    src={cityImage(city.id) ?? ''}
                                    alt={cityName(city)}
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    loading="lazy"
                                    decoding="async"
                                    onerror={() =>
                                        (failedCityImages = {
                                            ...failedCityImages,
                                            [city.id]: true,
                                        })}
                                />
                            {:else}
                                <CityArt
                                    cityId={city.id}
                                    class="h-44"
                                    motifClass="h-24"
                                />
                            {/if}
                            <div
                                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-950/10 to-transparent"
                            ></div>
                            <span
                                class="absolute top-3 start-3 rounded-full bg-card/90 px-3 py-1 text-xs font-bold text-foreground"
                            >
                                {t('places.count', {
                                    count: formatNumber(
                                        placesByCity(city.id).length,
                                    ),
                                })}
                            </span>
                            <p
                                class="absolute bottom-3 start-4 text-xl font-bold text-white drop-shadow"
                            >
                                {cityName(city)}
                            </p>
                        </div>
                        <div class="flex items-center justify-between p-4">
                            <span class="text-xs font-medium text-muted-foreground">
                                {city.nameEn}
                            </span>
                            <span
                                class="inline-flex items-center gap-1 text-sm font-bold text-emerald-700 dark:text-emerald-400"
                            >
                                {t('preview.destinationLink')}
                                <ChevronLeft class="size-4 ltr:rotate-180" />
                            </span>
                        </div>
                    </Link>
                {/each}
            </div>
        </div>
    </section>

    <section class="bg-background py-16 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 md:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 dark:bg-emerald-950/50 px-3 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                >
                    <Star class="size-4" />
                    {t('preview.placesBadge')}
                </span>
                <h2 class="mt-4 text-3xl font-bold sm:text-4xl">
                    {t('preview.placesTitle')}
                </h2>
                <p class="mt-3 text-muted-foreground">{t('preview.placesDesc')}</p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {#each featuredPlaces as place (place.id)}
                    <div
                        class="overflow-hidden rounded-[20px] bg-card shadow-sm ring-1 ring-border transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <PlaceMedia place={place} class="h-40" overlay>
                            <span
                                class="absolute top-3 end-3 inline-flex items-center gap-1 rounded-full bg-card/95 px-2.5 py-1 text-xs font-bold text-foreground"
                            >
                                <Star class="size-3.5 fill-amber-400 text-amber-400" />
                                {place.rating}
                            </span>
                        </PlaceMedia>
                        <div class="p-4">
                            <p class="text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                {categoryLabel(place.category)} · {placeCity(place)}
                            </p>
                            <h3 class="mt-1.5 text-base font-bold text-foreground">
                                {placeName(place)}
                            </h3>
                            <p class="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                {placeDescription(place)}
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-sm font-bold text-foreground">
                                    {priceLabel(place)}
                                </span>
                                <Link
                                    href={toUrl(placesRoute())}
                                    class="rounded-lg bg-emerald-50 dark:bg-emerald-950/50 px-3 py-1.5 text-xs font-bold text-emerald-800 dark:text-emerald-300 transition hover:bg-emerald-100 dark:hover:bg-emerald-900/60"
                                >
                                    {t('preview.placeDetails')}
                                </Link>
                            </div>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <section id="plan" class="scroll-mt-20 bg-muted/40 py-16 sm:py-24">
        <div class="mx-auto max-w-3xl px-4 md:px-6">
            <div class="text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 dark:bg-emerald-950/50 px-3 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                >
                    <CalendarDays class="size-4" />
                    {t('preview.formBadge')}
                </span>
                <h2 class="mt-4 text-3xl font-bold sm:text-4xl">
                    {t('preview.formTitle')}
                </h2>
                <p class="mt-3 text-muted-foreground">{t('preview.formDesc')}</p>
            </div>

            <div
                class="mt-10 rounded-[24px] bg-card p-6 shadow-xl shadow-slate-900/5 ring-1 ring-border/70 sm:p-8"
            >
                {#if submitted}
                    <div
                        class="flex flex-col items-center gap-4 py-10 text-center"
                    >
                        <span
                            class="flex size-14 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                        >
                            <Check class="size-7" />
                        </span>
                        <p class="text-lg font-bold text-foreground">
                            {t('preview.formSuccess')}
                        </p>
                    </div>
                {:else}
                    <form class="grid gap-5 sm:grid-cols-2" onsubmit={onSubmit}>
                        <div>
                            <label class={labelClass} for="preview-name">
                                {t('preview.formName')}
                            </label>
                            <input
                                id="preview-name"
                                name="name"
                                type="text"
                                required
                                class={inputClass}
                                placeholder={t('preview.formNamePlaceholder')}
                            />
                        </div>
                        <div>
                            <label class={labelClass} for="preview-phone">
                                {t('preview.formPhone')}
                            </label>
                            <input
                                id="preview-phone"
                                name="phone"
                                type="tel"
                                required
                                class={inputClass}
                                placeholder={t('preview.formPhonePlaceholder')}
                            />
                        </div>
                        <div>
                            <label class={labelClass} for="preview-city">
                                {t('preview.formCity')}
                            </label>
                            <select
                                id="preview-city"
                                name="city"
                                required
                                class={inputClass}
                            >
                                <option value="">
                                    {t('preview.formCityPlaceholder')}
                                </option>
                                {#each cities as city (city.id)}
                                    <option value={city.id}>
                                        {cityName(city)}
                                    </option>
                                {/each}
                            </select>
                        </div>
                        <div>
                            <label class={labelClass} for="preview-date">
                                {t('preview.formDate')}
                            </label>
                            <input
                                id="preview-date"
                                name="date"
                                type="date"
                                required
                                class={inputClass}
                            />
                        </div>
                        <div>
                            <label class={labelClass} for="preview-travelers">
                                {t('preview.formTravelers')}
                            </label>
                            <input
                                id="preview-travelers"
                                name="travelers"
                                type="number"
                                min="1"
                                class={inputClass}
                                placeholder={t(
                                    'preview.formTravelersPlaceholder',
                                )}
                            />
                        </div>
                        <div>
                            <label class={labelClass} for="preview-budget">
                                {t('preview.formBudget')}
                            </label>
                            <input
                                id="preview-budget"
                                name="budget"
                                type="number"
                                min="0"
                                class={inputClass}
                                placeholder={t(
                                    'preview.formBudgetPlaceholder',
                                )}
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <label class={labelClass} for="preview-notes">
                                {t('preview.formNotes')}
                            </label>
                            <textarea
                                id="preview-notes"
                                name="notes"
                                rows="3"
                                class={inputClass}
                                placeholder={t(
                                    'preview.formNotesPlaceholder',
                                )}
                            ></textarea>
                        </div>
                        <button
                            type="submit"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-7 text-base font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-[0.98] sm:col-span-2"
                        >
                            <Send class="size-4 rtl:-scale-x-100" />
                            {t('preview.formSubmit')}
                        </button>
                    </form>
                {/if}
            </div>
        </div>
    </section>

    <section class="bg-background py-16 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 md:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 dark:bg-emerald-950/50 px-3 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                >
                    <MessageCircle class="size-4" />
                    {t('preview.testimonialsBadge')}
                </span>
                <h2 class="mt-4 text-3xl font-bold sm:text-4xl">
                    {t('preview.testimonialsTitle')}
                </h2>
                <p class="mt-3 text-muted-foreground">
                    {t('preview.testimonialsDesc')}
                </p>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                {#each testimonials as testimonial (testimonial.name)}
                    <figure
                        class="flex h-full flex-col rounded-[20px] bg-card p-6 shadow-sm ring-1 ring-border"
                    >
                        <div class="flex items-center gap-1">
                            {#each Array(5) as _}
                                <Star
                                    class="size-4 fill-amber-400 text-amber-400"
                                />
                            {/each}
                        </div>
                        <blockquote class="mt-4 flex-1 text-sm leading-relaxed text-muted-foreground">
                            “{testimonial.quote}”
                        </blockquote>
                        <figcaption class="mt-6 flex items-center gap-3">
                            <span
                                class="flex size-10 items-center justify-center rounded-full bg-[#0b1e33] text-sm font-bold text-white"
                            >
                                {initialsOf(testimonial.name)}
                            </span>
                            <span>
                                <span class="block text-sm font-bold text-foreground">
                                    {testimonial.name}
                                </span>
                                <span class="block text-xs text-muted-foreground">
                                    {testimonial.city}
                                </span>
                            </span>
                        </figcaption>
                    </figure>
                {/each}
            </div>
        </div>
    </section>

    <section id="contact" class="scroll-mt-20 bg-muted/40 py-16 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 md:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 dark:bg-emerald-950/50 px-3 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                >
                    <Phone class="size-4" />
                    {t('preview.contactBadge')}
                </span>
                <h2 class="mt-4 text-3xl font-bold sm:text-4xl">
                    {t('preview.contactTitle')}
                </h2>
                <p class="mt-3 text-muted-foreground">{t('preview.contactDesc')}</p>
            </div>

            <div class="mx-auto mt-12 max-w-2xl">
                <div
                    class="flex flex-col justify-between rounded-[20px] bg-card p-6 shadow-sm ring-1 ring-border sm:p-8"
                >
                    <ul class="flex flex-col gap-5">
                        <li class="flex items-start gap-4">
                            <span
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <MapPin class="size-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-bold">
                                    {t('preview.contactAddress')}
                                </span>
                                <span class="block text-sm text-muted-foreground">
                                    {t('preview.contactAddressValue')}
                                </span>
                            </span>
                        </li>
                        <li class="flex items-start gap-4">
                            <span
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <Phone class="size-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-bold">
                                    {t('preview.contactPhone')}
                                </span>
                                <span class="block text-sm text-muted-foreground" dir="ltr">
                                    +966 53 000 0000
                                </span>
                            </span>
                        </li>
                        <li class="flex items-start gap-4">
                            <span
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <Mail class="size-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-bold">
                                    {t('preview.contactEmail')}
                                </span>
                                <span class="block text-sm text-muted-foreground">
                                    hello@example.com
                                </span>
                            </span>
                        </li>
                        <li class="flex items-start gap-4">
                            <span
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <Clock class="size-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-bold">
                                    {t('preview.contactHours')}
                                </span>
                                <span class="block text-sm text-muted-foreground">
                                    {t('preview.contactHoursValue')}
                                </span>
                            </span>
                        </li>
                    </ul>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            href={toUrl(assistant())}
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-5 text-sm font-bold text-white transition hover:brightness-110 active:scale-[0.98]"
                        >
                            <MessageCircle class="size-4" />
                            {t('preview.contactChat')}
                        </Link>
                        <a
                            href="https://www.openstreetmap.org/?mlat=24.7136&mlon=46.6753#map=11/24.7136/46.6753"
                            target="_blank"
                            rel="noreferrer"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#0b1e33] px-5 text-sm font-bold text-white transition hover:bg-[#12293f] active:scale-[0.98]"
                        >
                            <MapPin class="size-4" />
                            {t('preview.contactMap')}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-[#08131f] pt-14 pb-bottom-nav text-slate-300 md:pb-8">
        <div class="mx-auto max-w-6xl px-4 md:px-6">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2.5 text-white">
                        <span
                            class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700"
                        >
                            <AppLogoIcon class="size-5 fill-current" />
                        </span>
                        <span class="text-sm font-bold">{t('app.name')}</span>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed">
                        {t('preview.footerAbout')}
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-white">
                        {t('preview.footerQuick')}
                    </h3>
                    <ul class="mt-4 flex flex-col gap-2.5 text-sm">
                        <li>
                            <a class="transition hover:text-white" href="#top">
                                {t('nav.home')}
                            </a>
                        </li>
                        <li>
                            <Link class="transition hover:text-white" href={toUrl(placesRoute())}>
                                {t('nav.places')}
                            </Link>
                        </li>
                        <li>
                            <Link class="transition hover:text-white" href={toUrl(assistant())}>
                                {t('nav.assistant')}
                            </Link>
                        </li>
                        <li>
                            <Link class="transition hover:text-white" href={toUrl(trips())}>
                                {t('nav.trips')}
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-white">
                        {t('preview.footerCities')}
                    </h3>
                    <ul class="mt-4 flex flex-col gap-2.5 text-sm">
                        {#each featuredCities.slice(0, 5) as city (city.id)}
                            <li>
                                <Link class="transition hover:text-white" href={toUrl(placesRoute())}>
                                    {cityName(city)}
                                </Link>
                            </li>
                        {/each}
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-white">
                        {t('preview.footerContact')}
                    </h3>
                    <ul class="mt-4 flex flex-col gap-2.5 text-sm">
                        <li class="flex items-center gap-2">
                            <MapPin class="size-4 shrink-0 text-emerald-400" />
                            {t('preview.contactAddressValue')}
                        </li>
                        <li class="flex items-center gap-2">
                            <Phone class="size-4 shrink-0 text-emerald-400" />
                            <span dir="ltr">+966 53 000 0000</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <Mail class="size-4 shrink-0 text-emerald-400" />
                            hello@example.com
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-6 text-xs text-slate-400 sm:flex-row"
            >
                <p>
                    © {new Date().getFullYear()}
                    {t('app.name')} — {t('preview.rights')}
                </p>
                <div class="flex items-center gap-4">
                    <Link
                        class="inline-flex items-center gap-1 font-semibold text-emerald-300 transition hover:text-emerald-200"
                        href="/welcome-classic"
                    >
                        <ArrowLeft class="size-3.5 ltr:rotate-180" />
                        {t('preview.backToCurrent')}
                    </Link>
                    <span class="inline-flex items-center gap-1">
                        <Users class="size-3.5" />
                        {formatNumber(cities.length)}
                    </span>
                </div>
            </div>
        </div>
    </footer>
</div>

<script lang="ts">
    import { Link, page, router, useHttp } from '@inertiajs/svelte';
    import Bell from '@lucide/svelte/icons/bell';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import CheckCheck from '@lucide/svelte/icons/check-check';
    import CloudSun from '@lucide/svelte/icons/cloud-sun';
    import Compass from '@lucide/svelte/icons/compass';
    import Heart from '@lucide/svelte/icons/heart';
    import Inbox from '@lucide/svelte/icons/inbox';
    import Menu from '@lucide/svelte/icons/menu';
    import Route from '@lucide/svelte/icons/route';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import UserPlus from '@lucide/svelte/icons/user-plus';
    import X from '@lucide/svelte/icons/x';
    import { onMount, untrack } from 'svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import BottomNav from '@/components/BottomNav.svelte';
    import GuestStartMenu from '@/components/GuestStartMenu.svelte';
    import InstallPrompt from '@/components/InstallPrompt.svelte';
    import ThemeToggle from '@/components/ThemeToggle.svelte';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import UserInfo from '@/components/UserInfo.svelte';
    import UserMenuContent from '@/components/UserMenuContent.svelte';
    import {
        formatNumber,
        getLocale,
        locales,
        setLocale,
        t,
        type LocaleCode,
    } from '@/lib/i18n.svelte';
    import { themeState } from '@/lib/theme.svelte';
    import { toUrl } from '@/lib/utils';
    import { update } from '@/routes/locale';
    import {
        index as notificationsIndex,
        read as notificationRead,
        readAll as notificationsReadAll,
    } from '@/routes/notifications';
    import {
        assistant,
        dashboard,
        favorites,
        home,
        login,
        places as placesRoute,
        register,
        translate,
        trips,
    } from '@/routes';
    import type { Component } from 'svelte';

    type NavKey =
        | 'home'
        | 'places'
        | 'assistant'
        | 'trips'
        | 'favorites'
        | 'translate';

    interface NotificationData {
        trip_id?: number;
        city_id?: number;
        event_id?: number;
        url?: string;
    }

    interface AppNotification {
        id: number;
        type: string;
        title: string;
        body: string;
        data: NotificationData | null;
        read_at: string | null;
        created_at: string;
    }

    interface NotificationsResponse {
        notifications: AppNotification[];
        unread_count: number;
    }

    interface UnreadCountResponse {
        unread_count: number;
    }

    type NotificationIcon = Component<{ class?: string }>;

    let { active = '' }: { active?: NavKey | '' } = $props();

    const auth = $derived(page.props.auth);
    const http = useHttp({ locale: '' });
    const { resolvedAppearance } = themeState();

    const themeLabel = $derived(
        resolvedAppearance() === 'dark'
            ? t('common.lightMode')
            : t('common.darkMode'),
    );

    let menuOpen = $state(false);

    const NOTIFICATIONS_POLL_MS = 120_000;

    const notificationIcons: Record<string, NotificationIcon> = {
        trip_reminder: Route,
        weather_alert: CloudSun,
        event: CalendarDays,
    };

    const notificationIconClasses: Record<string, string> = {
        trip_reminder:
            'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300',
        weather_alert: 'bg-sky-500/15 text-sky-600 dark:text-sky-300',
        event: 'bg-amber-500/15 text-amber-600 dark:text-amber-300',
    };

    let notificationsOpen = $state(false);
    let notifications = $state<AppNotification[]>([]);
    let unreadCount = $state(0);
    let notificationsLoading = $state(false);

    const notificationsHttp = useHttp<
        Record<string, never>,
        NotificationsResponse
    >({});
    const notificationActionHttp = useHttp<
        Record<string, never>,
        UnreadCountResponse
    >({});

    const unreadBadge = $derived(
        unreadCount > 99 ? '99+' : formatNumber(unreadCount),
    );

    const notificationsAriaLabel = $derived(
        unreadCount > 0
            ? `${t('notifications.title')} — ${t('notifications.unreadCount', { count: formatNumber(unreadCount) })}`
            : t('notifications.title'),
    );

    function notificationIcon(type: string): NotificationIcon {
        return notificationIcons[type] ?? Bell;
    }

    function notificationIconClass(type: string): string {
        return (
            notificationIconClasses[type] ?? 'bg-muted text-muted-foreground'
        );
    }

    function notificationTypeLabel(type: string): string {
        if (type === 'trip_reminder') {
            return t('notifications.trip');
        }

        if (type === 'weather_alert') {
            return t('notifications.weather');
        }

        if (type === 'event') {
            return t('notifications.event');
        }

        return t('notifications.title');
    }

    function relativeTime(value: string): string {
        const seconds = Math.round(
            (new Date(value).getTime() - Date.now()) / 1000,
        );
        const formatter = new Intl.RelativeTimeFormat(getLocale(), {
            numeric: 'auto',
        });
        const ranges: Array<[Intl.RelativeTimeFormatUnit, number]> = [
            ['year', 31_536_000],
            ['month', 2_592_000],
            ['week', 604_800],
            ['day', 86_400],
            ['hour', 3_600],
            ['minute', 60],
        ];

        for (const [unit, amount] of ranges) {
            if (Math.abs(seconds) >= amount) {
                return formatter.format(Math.round(seconds / amount), unit);
            }
        }

        return formatter.format(seconds, 'second');
    }

    function loadNotifications(): void {
        if (!page.props.auth.user) {
            return;
        }

        notificationsLoading = true;

        notificationsHttp
            .get(notificationsIndex.url(), {
                onSuccess: (response) => {
                    notifications = Array.isArray(response.notifications)
                        ? response.notifications
                        : [];
                    unreadCount =
                        typeof response.unread_count === 'number'
                            ? response.unread_count
                            : 0;
                },
                onFinish: () => {
                    notificationsLoading = false;
                },
            })
            .catch(() => {
                notificationsLoading = false;
            });
    }

    function optimisticallyMarkRead(ids: number[]): () => void {
        const previousNotifications = notifications;
        const previousUnread = unreadCount;
        const now = new Date().toISOString();
        let changed = 0;

        notifications = notifications.map((notification) => {
            if (ids.includes(notification.id) && notification.read_at === null) {
                changed += 1;

                return { ...notification, read_at: now };
            }

            return notification;
        });
        unreadCount = Math.max(0, previousUnread - changed);

        return () => {
            notifications = previousNotifications;
            unreadCount = previousUnread;
        };
    }

    function markNotificationRead(id: number): void {
        const rollback = optimisticallyMarkRead([id]);

        notificationActionHttp
            .post(notificationRead.url(id), {
                onSuccess: (response) => {
                    if (typeof response.unread_count === 'number') {
                        unreadCount = response.unread_count;
                    }
                },
                onError: rollback,
                onHttpException: rollback,
                onNetworkError: rollback,
            })
            .catch(rollback);
    }

    function markAllNotificationsRead(): void {
        if (unreadCount === 0) {
            return;
        }

        const rollback = optimisticallyMarkRead(
            notifications
                .filter((notification) => notification.read_at === null)
                .map((notification) => notification.id),
        );

        notificationActionHttp
            .post(notificationsReadAll.url(), {
                onSuccess: (response) => {
                    unreadCount =
                        typeof response.unread_count === 'number'
                            ? response.unread_count
                            : 0;
                },
                onError: rollback,
                onHttpException: rollback,
                onNetworkError: rollback,
            })
            .catch(rollback);
    }

    function openNotification(notification: AppNotification): void {
        notificationsOpen = false;

        if (notification.read_at === null) {
            markNotificationRead(notification.id);
        }

        const url = notification.data?.url;

        if (typeof url === 'string' && url !== '') {
            router.visit(url);
        }
    }

    onMount(() => {
        if (!page.props.auth.user) {
            return;
        }

        loadNotifications();

        const pollTimer = window.setInterval(() => {
            if (document.visibilityState === 'visible') {
                loadNotifications();
            }
        }, NOTIFICATIONS_POLL_MS);

        return () => {
            window.clearInterval(pollTimer);
        };
    });

    $effect(() => {
        if (notificationsOpen && page.props.auth.user) {
            untrack(loadNotifications);
        }
    });

    function onLocaleChange(event: Event): void {
        const value = (event.currentTarget as HTMLSelectElement)
            .value as LocaleCode;
        setLocale(value);

        if (page.props.auth.user) {
            http.locale = value;
            http.patch(update.url());
        }
    }

    const navLinks = $derived([
        {
            key: 'home' as const,
            label: t('nav.home'),
            href: toUrl(auth.user ? dashboard() : home()),
        },
        {
            key: 'places' as const,
            label: t('nav.places'),
            href: toUrl(placesRoute()),
        },
        {
            key: 'assistant' as const,
            label: t('nav.assistant'),
            href: toUrl(assistant()),
        },
        { key: 'trips' as const, label: t('nav.trips'), href: toUrl(trips()) },
        ...(auth.user
            ? [
                  {
                      key: 'favorites' as const,
                      label: t('nav.favorites'),
                      href: toUrl(favorites()),
                  },
              ]
            : []),
        {
            key: 'translate' as const,
            label: t('nav.translate'),
            href: toUrl(translate()),
        },
    ]);
</script>

<header
    class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-[#08131f]/85 backdrop-blur-md"
>
    <div
        class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-3 px-4 md:px-6"
    >
        <Link
            href={toUrl(home())}
            class="flex min-w-0 items-center gap-2.5 text-white"
        >
            <span
                class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700"
            >
                <AppLogoIcon class="size-5 fill-current" />
            </span>
            <span class="truncate text-sm font-bold sm:text-base">
                {t('app.name')}
            </span>
        </Link>

        <nav class="hidden items-center gap-6 text-sm font-medium lg:flex">
            {#each navLinks as link (link.key)}
                {#if link.key === active}
                    <a class="text-emerald-300" href={link.href}>
                        {link.label}
                    </a>
                {:else if link.key === 'assistant'}
                    <a
                        class="inline-flex items-center gap-1.5 font-bold text-emerald-300 transition hover:text-emerald-200"
                        href={link.href}
                    >
                        <Sparkles class="size-4" aria-hidden="true" />
                        {link.label}
                    </a>
                {:else}
                    <a
                        class="text-slate-200 transition hover:text-white"
                        href={link.href}
                    >
                        {link.label}
                    </a>
                {/if}
            {/each}
        </nav>

        <div class="flex items-center gap-2">
            <label class="relative hidden items-center sm:flex">
                <select
                    value={getLocale()}
                    onchange={onLocaleChange}
                    aria-label={t('nav.language')}
                    class="appearance-none rounded-xl border border-white/15 bg-white/10 py-2 ps-3 pe-6 text-xs font-semibold text-white transition hover:bg-white/15"
                >
                    {#each locales as locale (locale.code)}
                        <option class="text-foreground" value={locale.code}>
                            {locale.flag} {locale.label}
                        </option>
                    {/each}
                </select>
            </label>

            <ThemeToggle
                class="size-10 rounded-xl text-white hover:bg-white/10 hover:text-white"
            />

            {#if auth.user}
                <DropdownMenu bind:open={notificationsOpen}>
                    <DropdownMenuTrigger>
                        {#snippet child({ props })}
                            <button
                                {...props}
                                type="button"
                                class="relative inline-flex size-10 shrink-0 items-center justify-center rounded-xl text-white transition hover:bg-white/10"
                                aria-label={notificationsAriaLabel}
                                title={t('notifications.title')}
                            >
                                <Bell class="size-5" />
                                {#if unreadCount > 0}
                                    <span
                                        class="absolute -top-0.5 -end-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-[#08131f]"
                                    >
                                        {unreadBadge}
                                    </span>
                                {/if}
                            </button>
                        {/snippet}
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        align="center"
                        sideOffset={8}
                        class="w-[22rem] max-w-[min(calc(100vw-1.5rem),var(--bits-dropdown-menu-content-available-width))] overflow-hidden rounded-2xl border-border/60 p-0 shadow-xl shadow-black/10"
                    >
                        <div
                            class="flex items-center justify-between gap-3 border-b border-border/60 px-4 py-3"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold">
                                    {t('notifications.title')}
                                </span>
                                {#if unreadCount > 0}
                                    <span
                                        class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-300"
                                    >
                                        {formatNumber(unreadCount)}
                                    </span>
                                {/if}
                            </div>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-500/10 active:scale-[0.98] disabled:cursor-default disabled:opacity-40 disabled:hover:bg-transparent dark:text-emerald-300"
                                disabled={unreadCount === 0}
                                onclick={markAllNotificationsRead}
                            >
                                <CheckCheck class="size-3.5" />
                                {t('notifications.markAllRead')}
                            </button>
                        </div>

                        <div
                            class="max-h-[360px] overflow-y-auto overscroll-contain"
                        >
                            {#if notificationsLoading && notifications.length === 0}
                                <div
                                    class="space-y-1 p-2"
                                    role="status"
                                    aria-label={t('notifications.loading')}
                                >
                                    {#each [0, 1, 2, 3] as skeleton (skeleton)}
                                        <div
                                            class="flex items-start gap-3 rounded-xl p-3"
                                        >
                                            <div
                                                class="size-9 shrink-0 animate-pulse rounded-xl bg-muted"
                                            ></div>
                                            <div class="flex-1 space-y-2">
                                                <div
                                                    class="h-3 w-3/4 animate-pulse rounded-full bg-muted"
                                                ></div>
                                                <div
                                                    class="h-3 w-1/2 animate-pulse rounded-full bg-muted"
                                                ></div>
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {:else if notifications.length === 0}
                                <div
                                    class="flex flex-col items-center justify-center gap-2 px-6 py-10 text-center"
                                >
                                    <span
                                        class="flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground"
                                    >
                                        <Inbox class="size-6" />
                                    </span>
                                    <p class="text-sm font-semibold">
                                        {t('notifications.empty')}
                                    </p>
                                </div>
                            {:else}
                                <ul class="divide-y divide-border/50">
                                    {#each notifications as notification (notification.id)}
                                        {@const Icon = notificationIcon(
                                            notification.type,
                                        )}
                                        <li>
                                            <button
                                                type="button"
                                                class="flex w-full items-start gap-3 px-4 py-3 text-start transition hover:bg-muted/60 active:bg-muted {notification.read_at ===
                                                null
                                                    ? 'bg-emerald-500/[0.04]'
                                                    : ''}"
                                                onclick={() =>
                                                    openNotification(notification)}
                                            >
                                                <span
                                                    class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-xl {notificationIconClass(
                                                        notification.type,
                                                    )}"
                                                    aria-hidden="true"
                                                >
                                                    <Icon class="size-4" />
                                                    <span class="sr-only">
                                                        {notificationTypeLabel(
                                                            notification.type,
                                                        )}
                                                    </span>
                                                </span>
                                                <span class="min-w-0 flex-1">
                                                    <span
                                                        class="flex items-baseline justify-between gap-2"
                                                    >
                                                        <span
                                                            class="line-clamp-1 text-sm font-semibold"
                                                        >
                                                            {notification.title}
                                                        </span>
                                                        <span
                                                            class="shrink-0 text-[11px] text-muted-foreground"
                                                        >
                                                            {relativeTime(
                                                                notification.created_at,
                                                            )}
                                                        </span>
                                                    </span>
                                                    <span
                                                        class="mt-0.5 line-clamp-2 block text-xs leading-relaxed text-muted-foreground"
                                                    >
                                                        {notification.body}
                                                    </span>
                                                </span>
                                                {#if notification.read_at === null}
                                                    <span
                                                        class="mt-2 size-2 shrink-0 rounded-full bg-emerald-500"
                                                        aria-hidden="true"
                                                    ></span>
                                                {/if}
                                            </button>
                                        </li>
                                    {/each}
                                </ul>
                            {/if}
                        </div>
                    </DropdownMenuContent>
                </DropdownMenu>

                <DropdownMenu>
                    <DropdownMenuTrigger>
                        {#snippet child({ props })}
                            <button
                                {...props}
                                type="button"
                                class="flex items-center gap-2 rounded-xl px-2 py-1.5 text-white transition hover:bg-white/10"
                                aria-label={auth.user.name}
                            >
                                <UserInfo user={auth.user} />
                            </button>
                        {/snippet}
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <UserMenuContent user={auth.user} />
                    </DropdownMenuContent>
                </DropdownMenu>
            {:else}
                <Link
                    href={toUrl(login())}
                    class="hidden text-sm font-semibold text-slate-200 transition hover:text-white sm:inline-flex"
                >
                    {t('welcome.login')}
                </Link>
                <GuestStartMenu
                    label={t('welcome.start')}
                    buttonClass="hidden items-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-950/30 transition hover:brightness-110 active:scale-[0.98] sm:inline-flex"
                />
            {/if}

            <button
                type="button"
                class="inline-flex size-10 items-center justify-center rounded-xl text-white transition hover:bg-white/10 lg:hidden"
                aria-label={t('nav.menu')}
                onclick={() => (menuOpen = !menuOpen)}
            >
                {#if menuOpen}
                    <X class="size-5" />
                {:else}
                    <Menu class="size-5" />
                {/if}
            </button>
        </div>
    </div>

    {#if menuOpen}
        <div class="border-t border-white/10 bg-[#08131f]/95 px-4 pb-5 pt-2 lg:hidden">
            <nav class="flex flex-col text-base font-medium text-slate-100">
                {#each navLinks as link (link.key)}
                    <a
                        class="flex items-center gap-2 py-3 {link.key === active
                            ? 'text-emerald-300'
                            : link.key === 'assistant'
                              ? 'font-bold text-emerald-300'
                              : ''}"
                        href={link.href}
                        onclick={() => (menuOpen = false)}
                    >
                        {#if link.key === 'assistant'}
                            <Sparkles class="size-4" aria-hidden="true" />
                        {/if}
                        {link.label}
                    </a>
                {/each}
            </nav>

            <label class="mt-2 flex items-center">
                <select
                    value={getLocale()}
                    onchange={onLocaleChange}
                    aria-label={t('nav.language')}
                    class="w-full appearance-none rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 text-sm font-semibold text-white"
                >
                    {#each locales as locale (locale.code)}
                        <option class="text-foreground" value={locale.code}>
                            {locale.flag} {locale.label}
                        </option>
                    {/each}
                </select>
            </label>

            <div
                class="mt-2 flex items-center justify-between gap-3 rounded-xl border border-white/10 px-3 py-1.5"
            >
                <span class="text-sm font-semibold text-slate-200">
                    {themeLabel}
                </span>
                <ThemeToggle
                    class="size-9 rounded-lg text-white hover:bg-white/10 hover:text-white"
                />
            </div>

            {#if auth.user}
                <div class="mt-3 flex items-center justify-between gap-3 border-t border-white/10 pt-4">
                    <div class="flex items-center gap-2 text-white">
                        <UserInfo user={auth.user} />
                    </div>
                </div>
            {:else}
                <div class="mt-3 flex flex-col gap-2">
                    <Link
                        href={toUrl(register())}
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-5 py-3 text-sm font-bold text-white"
                    >
                        <UserPlus class="size-4" />
                        {t('landing.createAccount')}
                    </Link>
                    <Link
                        href={toUrl(placesRoute())}
                        onclick={() => (menuOpen = false)}
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 px-5 py-3 text-sm font-semibold text-white"
                    >
                        <Compass class="size-4" />
                        {t('landing.guestTour')}
                    </Link>
                    <Link
                        href={toUrl(login())}
                        class="py-2 text-center text-sm font-semibold text-slate-300"
                    >
                        {t('welcome.login')}
                    </Link>
                </div>
            {/if}
        </div>
    {/if}
</header>

<BottomNav />
<InstallPrompt />

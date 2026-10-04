import { router } from '@inertiajs/svelte';
import {
    index as favoritesIndex,
    sync as favoritesSync,
    toggle as favoritesToggle,
} from '@/routes/favorites';

const STORAGE_KEY = 'favorites';

const state = $state<{ ids: number[] }>({ ids: [] });

let authenticated = false;
let serverSynced = false;
let hydrating = false;
let hydrated = $state(false);

export function favoriteIds(): number[] {
    return state.ids;
}

export function isFavorite(placeId: number): boolean {
    return state.ids.includes(placeId);
}

export function favoritesCount(): number {
    return state.ids.length;
}

export function favoritesHydrated(): boolean {
    return hydrated;
}

export function toggleFavorite(placeId: number): boolean {
    const exists = state.ids.includes(placeId);
    const previous = state.ids;

    state.ids = exists
        ? state.ids.filter((id) => id !== placeId)
        : [...state.ids, placeId];

    if (!authenticated) {
        persist();

        return !exists;
    }

    if (!serverSynced && !hydrating) {
        void hydrateFromServer();
    }

    void persistToggle(placeId, previous);

    return !exists;
}

export function initializeFavorites(): void {
    if (typeof window === 'undefined') {
        return;
    }

    authenticated = hasAuthenticatedUser();
    state.ids = readStoredIds();

    if (authenticated) {
        void hydrateFromServer();
    } else {
        hydrated = true;
    }
}

router.on('navigate', (event) => {
    const props = (event.detail?.page?.props ?? {}) as {
        auth?: { user?: unknown };
    };
    const nowAuthenticated = props.auth?.user != null;

    if (nowAuthenticated && !serverSynced && !hydrating) {
        void hydrateFromServer();
    }

    if (!nowAuthenticated && authenticated) {
        state.ids = readStoredIds();
        hydrated = true;
        serverSynced = false;
    }

    authenticated = nowAuthenticated;
});

async function hydrateFromServer(): Promise<void> {
    if (hydrating) {
        return;
    }

    hydrating = true;

    try {
        const guestIds = readStoredIds();
        const response = await fetch(favoritesIndex.url(), {
            credentials: 'same-origin',
            headers: requestHeaders(),
        });

        if (!response.ok) {
            return;
        }

        const payload = (await response.json()) as { ids?: unknown };
        let ids = normalizeIds(payload.ids) ?? state.ids;

        if (guestIds.length > 0) {
            const syncedIds = await syncGuestIds(guestIds);

            if (syncedIds !== null) {
                ids = syncedIds;
                clearStored();
            }
        }

        state.ids = ids;
        serverSynced = true;
    } catch {
        // Keep the local state until the next successful request.
    } finally {
        hydrating = false;
        hydrated = true;
    }
}

async function persistToggle(
    placeId: number,
    previous: number[],
): Promise<void> {
    try {
        const response = await fetch(favoritesToggle.url(placeId), {
            method: 'POST',
            credentials: 'same-origin',
            headers: requestHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({}),
        });

        if (!response.ok) {
            state.ids = previous;

            return;
        }

        const payload = (await response.json()) as { ids?: unknown };
        const ids = normalizeIds(payload.ids);

        if (ids !== null) {
            state.ids = ids;
        }

        serverSynced = true;
        hydrated = true;
    } catch {
        state.ids = previous;
    }
}

async function syncGuestIds(ids: number[]): Promise<number[] | null> {
    try {
        const response = await fetch(favoritesSync.url(), {
            method: 'POST',
            credentials: 'same-origin',
            headers: requestHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ ids }),
        });

        if (!response.ok) {
            return null;
        }

        const payload = (await response.json()) as { ids?: unknown };

        return normalizeIds(payload.ids);
    } catch {
        return null;
    }
}

function persist(): void {
    if (typeof window !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(state.ids));
    }
}

function clearStored(): void {
    if (typeof window !== 'undefined') {
        localStorage.removeItem(STORAGE_KEY);
    }
}

function readStoredIds(): number[] {
    if (typeof window === 'undefined') {
        return [];
    }

    try {
        const parsed = JSON.parse(
            localStorage.getItem(STORAGE_KEY) ?? '[]',
        ) as unknown;

        return Array.isArray(parsed) ? (normalizeIds(parsed) ?? []) : [];
    } catch {
        return [];
    }
}

function normalizeIds(value: unknown): number[] | null {
    if (!Array.isArray(value)) {
        return null;
    }

    return [
        ...new Set(
            value.filter(
                (item): item is number =>
                    typeof item === 'number' && Number.isInteger(item),
            ),
        ),
    ];
}

function hasAuthenticatedUser(): boolean {
    if (typeof document === 'undefined') {
        return false;
    }

    const pageScript = document.querySelector(
        'script[data-page][type="application/json"]',
    );
    const raw = pageScript?.textContent;

    if (!raw) {
        return false;
    }

    try {
        const parsed = JSON.parse(raw) as {
            props?: { auth?: { user?: unknown } };
        };

        return parsed.props?.auth?.user != null;
    } catch {
        return false;
    }
}

function requestHeaders(
    extra: Record<string, string> = {},
): Record<string, string> {
    const headers: Record<string, string> = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...extra,
    };

    const token = xsrfToken();

    if (token !== null) {
        headers['X-XSRF-TOKEN'] = token;
    }

    return headers;
}

function xsrfToken(): string | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : null;
}

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import Heart from '@lucide/svelte/icons/heart';
    import LogOut from '@lucide/svelte/icons/log-out';
    import Settings from '@lucide/svelte/icons/settings';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import {
        DropdownMenuGroup,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuSeparator,
    } from '@/components/ui/dropdown-menu';
    import UserInfo from '@/components/UserInfo.svelte';
    import { t } from '@/lib/i18n.svelte';
    import { toUrl } from '@/lib/utils';
    import { favorites, logout } from '@/routes';
    import { dashboard as adminDashboard } from '@/routes/admin';
    import { edit } from '@/routes/profile';
    import type { User } from '@/types';

    let {
        user,
    }: {
        user: User;
    } = $props();

    function handleLogout(propsOnClick?: () => void) {
        return () => {
            propsOnClick?.();
            router.flushAll();
        };
    }
</script>

<DropdownMenuLabel class="p-0 font-normal">
    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
        <UserInfo {user} showEmail={true} />
    </div>
</DropdownMenuLabel>
<DropdownMenuSeparator />
<DropdownMenuGroup>
    <DropdownMenuItem>
        {#snippet child({ props })}
            <Link href={toUrl(favorites())} prefetch {...props}>
                <Heart class="mr-2 h-4 w-4" />
                {t('nav.favorites')}
            </Link>
        {/snippet}
    </DropdownMenuItem>
    {#if user.is_admin}
        <DropdownMenuItem>
            {#snippet child({ props })}
                <Link href={toUrl(adminDashboard())} prefetch {...props}>
                    <ShieldCheck class="me-2 h-4 w-4" />
                    {t('admin.title')}
                </Link>
            {/snippet}
        </DropdownMenuItem>
    {/if}
    <DropdownMenuItem>
        {#snippet child({ props })}
            <Link href={toUrl(edit())} prefetch {...props}>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        {/snippet}
    </DropdownMenuItem>
</DropdownMenuGroup>
<DropdownMenuSeparator />
<DropdownMenuItem>
    {#snippet child({ props })}
        <Link
            href={logout()}
            as="button"
            data-test="logout-button"
            {...props}
            onclick={handleLogout(props.onclick as () => void)}
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    {/snippet}
</DropdownMenuItem>

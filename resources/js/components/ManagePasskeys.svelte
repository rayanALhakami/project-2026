<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import KeyRound from '@lucide/svelte/icons/key-round';
    import { destroy } from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyRegistrationController';
    import PasskeyItem from '@/components/PasskeyItem.svelte';
    import PasskeyRegister from '@/components/PasskeyRegister.svelte';
    import type { Passkey } from '@/types/auth';

    export type Props = {
        canManagePasskeys?: boolean;
        passkeys?: Passkey[];
    };

    let { canManagePasskeys = false, passkeys = [] }: Props = $props();

    const handleDelete = (id: number, onError: () => void) => {
        router.delete(destroy.url(id), {
            preserveScroll: true,
            onError,
        });
    };

    const handleRegisterSuccess = () => {
        router.reload();
    };
</script>

{#if canManagePasskeys}
    <div
        class="rounded-[20px] bg-card p-6 shadow-sm ring-1 ring-border sm:p-8"
    >
        <h2 class="text-lg font-bold text-foreground">Passkeys</h2>
        <p class="mt-1 text-sm text-muted-foreground">
            Manage your passkeys for passwordless sign-in
        </p>

        <div class="mt-6 overflow-hidden rounded-xl ring-1 ring-border">
            {#if passkeys.length > 0}
                {#each passkeys as passkey (passkey.id)}
                    <PasskeyItem {passkey} onDelete={handleDelete} />
                {/each}
            {:else}
                <div class="p-8 text-center">
                    <div
                        class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                    >
                        <KeyRound class="h-7 w-7" />
                    </div>
                    <p class="font-bold text-foreground">No passkeys yet</p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Add a passkey to sign in without a password
                    </p>
                </div>
            {/if}
        </div>

        <div class="mt-4">
            <PasskeyRegister onSuccess={handleRegisterSuccess} />
        </div>
    </div>
{/if}

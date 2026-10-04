<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import { onDestroy } from 'svelte';
    import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.svelte';
    import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.svelte';
    import { Button } from '@/components/ui/button';
    import { twoFactorAuthState } from '@/lib/twoFactorAuth.svelte';
    import { disable, enable } from '@/routes/two-factor';

    export type Props = {
        canManageTwoFactor?: boolean;
        requiresConfirmation?: boolean;
        twoFactorEnabled?: boolean;
    };

    let {
        canManageTwoFactor = false,
        requiresConfirmation = false,
        twoFactorEnabled = false,
    }: Props = $props();

    const twoFactorAuth = twoFactorAuthState();
    let showSetupModal = $state(false);

    onDestroy(() => twoFactorAuth.clearTwoFactorAuthData());

    const primaryButton =
        'h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-5 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 hover:brightness-110';
</script>

{#if canManageTwoFactor}
    <div
        class="rounded-[20px] bg-card p-6 shadow-sm ring-1 ring-border sm:p-8"
    >
        <h2 class="text-lg font-bold text-foreground">
            Two-factor authentication
        </h2>
        <p class="mt-1 text-sm text-muted-foreground">
            Manage your two-factor authentication settings
        </p>

        {#if !twoFactorEnabled}
            <div class="mt-6 flex flex-col items-start justify-start space-y-4">
                <p class="text-sm text-muted-foreground">
                    When you enable two-factor authentication, you will be
                    prompted for a secure pin during login. This pin can be
                    retrieved from a TOTP-supported application on your phone.
                </p>

                <div>
                    {#if twoFactorAuth.hasSetupData()}
                        <Button
                            class={primaryButton}
                            onclick={() => (showSetupModal = true)}
                        >
                            <ShieldCheck class="size-4" />Continue setup
                        </Button>
                    {:else}
                        <Form
                            {...enable.form()}
                            onSuccess={() => (showSetupModal = true)}
                        >
                            {#snippet children({ processing })}
                                <Button
                                    type="submit"
                                    class={primaryButton}
                                    disabled={processing}
                                >
                                    Enable 2FA
                                </Button>
                            {/snippet}
                        </Form>
                    {/if}
                </div>
            </div>
        {:else}
            <div class="mt-6 flex flex-col items-start justify-start space-y-4">
                <p class="text-sm text-muted-foreground">
                    You will be prompted for a secure, random pin during login,
                    which you can retrieve from the TOTP-supported application
                    on your phone.
                </p>

                <div class="relative inline">
                    <Form {...disable.form()}>
                        {#snippet children({ processing })}
                            <Button
                                variant="destructive"
                                type="submit"
                                class="h-10 rounded-xl font-bold"
                                disabled={processing}
                            >
                                Disable 2FA
                            </Button>
                        {/snippet}
                    </Form>
                </div>

                <TwoFactorRecoveryCodes />
            </div>
        {/if}

        <TwoFactorSetupModal
            bind:isOpen={showSetupModal}
            {requiresConfirmation}
            {twoFactorEnabled}
        />
    </div>
{/if}
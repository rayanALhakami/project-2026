<script module lang="ts">
    import { edit } from '@/routes/security';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Security settings',
                href: edit(),
            },
        ],
    };
</script>

<script lang="ts">
    import {
        Form ,
        page,
    } from '@inertiajs/svelte';
    import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import { Button } from '@/components/ui/button';
    import { Label } from '@/components/ui/label';
    import ManageTwoFactor from '@/components/ManageTwoFactor.svelte';
    import ManagePasskeys from '@/components/ManagePasskeys.svelte';
    import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.svelte';
    const canManageTwoFactor = $derived(Boolean(page.props.canManageTwoFactor));
    const requiresConfirmation = $derived(
        Boolean(page.props.requiresConfirmation),
    );
    const twoFactorEnabled = $derived(Boolean(page.props.twoFactorEnabled));
    const canManagePasskeys = $derived(Boolean(page.props.canManagePasskeys));
    const passkeys = $derived(
        (Array.isArray(page.props.passkeys)
            ? page.props.passkeys
            : []) as ManagePasskeysProps['passkeys'],
    );

    let { passwordRules }: { passwordRules: string } = $props();

    const inputClass =
        'mt-1 h-11 w-full rounded-xl border-border bg-muted/60 text-foreground transition placeholder:text-muted-foreground focus-visible:border-emerald-500 focus-visible:ring-emerald-100 dark:focus-visible:ring-emerald-900/50';
    const labelClass = 'text-sm font-bold text-secondary-foreground';
    const submitClass =
        'h-11 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 hover:brightness-110';
</script>

<AppHead title="Security settings" />

<h1 class="sr-only">Security settings</h1>

<div
    class="rounded-[20px] bg-card p-6 shadow-sm ring-1 ring-border sm:p-8"
>
    <h2 class="text-lg font-bold text-foreground">Update password</h2>
    <p class="mt-1 text-sm text-muted-foreground">
        Ensure your account is using a long, random password to stay secure
    </p>

    <Form
        {...SecurityController.update.form()}
        class="mt-6 space-y-6"
        options={{ preserveScroll: true }}
        resetOnSuccess
        resetOnError={['password', 'password_confirmation', 'current_password']}
    >
        {#snippet children({ errors, processing })}
            <div class="grid gap-2">
                <Label for="current_password" class={labelClass}
                    >Current password</Label
                >
                <PasswordInput
                    id="current_password"
                    name="current_password"
                    class={inputClass}
                    autocomplete="current-password"
                    placeholder="Current password"
                />
                <InputError message={errors.current_password} />
            </div>

            <div class="grid gap-2">
                <Label for="password" class={labelClass}>New password</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    class={inputClass}
                    autocomplete="new-password"
                    placeholder="New password"
                    passwordrules={passwordRules}
                />
                <InputError message={errors.password} />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" class={labelClass}
                    >Confirm password</Label
                >
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    class={inputClass}
                    autocomplete="new-password"
                    placeholder="Confirm password"
                    passwordrules={passwordRules}
                />
                <InputError message={errors.password_confirmation} />
            </div>

            <div class="flex items-center gap-4">
                <Button
                    type="submit"
                    class={submitClass}
                    disabled={processing}
                    data-test="update-password-button"
                >
                    Save
                </Button>
            </div>
        {/snippet}
    </Form>
</div>

<ManageTwoFactor
    {canManageTwoFactor}
    {requiresConfirmation}
    {twoFactorEnabled}
/>

<ManagePasskeys {canManagePasskeys} {passkeys} />

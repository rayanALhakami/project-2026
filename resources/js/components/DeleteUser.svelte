<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Dialog,
        DialogClose,
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogTitle,
        DialogTrigger,
    } from '@/components/ui/dialog';
    import { Label } from '@/components/ui/label';
</script>

<div class="rounded-[20px] bg-card p-6 shadow-sm ring-1 ring-border sm:p-8">
    <h2 class="text-lg font-bold text-foreground">Delete account</h2>
    <p class="mt-1 text-sm text-muted-foreground">
        Delete your account and all of its resources
    </p>
    <div
        class="mt-6 space-y-4 rounded-xl bg-red-50 p-4 ring-1 ring-red-100 dark:bg-red-950/40 dark:ring-red-900/60"
    >
        <div class="relative space-y-0.5 text-red-700 dark:text-red-300">
            <p class="font-bold">Warning</p>
            <p class="text-sm">
                Please proceed with caution, this cannot be undone.
            </p>
        </div>
        <Dialog>
            <DialogTrigger>
                <Button
                    variant="destructive"
                    class="h-10 rounded-xl font-bold"
                    data-test="delete-user-button">Delete account</Button
                >
            </DialogTrigger>
            <DialogContent>
                <Form
                    {...ProfileController.destroy.form()}
                    class="space-y-6"
                    options={{ preserveScroll: true }}
                >
                    {#snippet children({ errors, processing })}
                        <div class="space-y-3">
                            <DialogTitle
                                >Are you sure you want to delete your account?</DialogTitle
                            >
                            <DialogDescription>
                                Once your account is deleted, all of its
                                resources and data will also be permanently
                                deleted. Please enter your password to confirm
                                you would like to permanently delete your
                                account.
                            </DialogDescription>
                        </div>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only"
                                >Password</Label
                            >
                            <PasswordInput
                                id="password"
                                name="password"
                                placeholder="Password"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose>
                                <Button
                                    variant="secondary"
                                    class="h-10 rounded-xl font-bold"
                                    >Cancel</Button
                                >
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                class="h-10 rounded-xl font-bold"
                                disabled={processing}
                                data-test="confirm-delete-user-button"
                            >
                                Delete account
                            </Button>
                        </DialogFooter>
                    {/snippet}
                </Form>
            </DialogContent>
        </Dialog>
    </div>
</div>

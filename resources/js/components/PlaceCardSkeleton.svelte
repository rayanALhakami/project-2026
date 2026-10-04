<script lang="ts">
    import { Skeleton } from '@/components/ui/skeleton';
</script>

<article
    class="flex flex-col overflow-hidden rounded-[20px] bg-card shadow-sm ring-1 ring-border"
>
    <Skeleton class="h-44 w-full rounded-none bg-muted" />
    <div class="flex flex-col gap-3 p-4">
        <Skeleton class="h-4 w-24 bg-muted" />
        <Skeleton class="h-5 w-3/5 bg-muted" />
        <Skeleton class="h-4 w-4/5 bg-muted" />
        <div
            class="mt-1 flex items-center justify-between gap-2 border-t border-border pt-3"
        >
            <Skeleton class="h-5 w-16 bg-muted" />
            <div class="flex gap-1.5">
                <Skeleton class="size-9 rounded-full bg-muted" />
                <Skeleton class="size-9 rounded-full bg-muted" />
            </div>
        </div>
    </div>
</article>

<script lang="ts">
    let {
        class: className = '',
        ...rest
    }: {
        class?: string;
        [key: string]: unknown;
    } = $props();
</script>

<img
    src="/logo/mark-transparent.png"
    alt="next trip"
    class="object-contain {className}"
    {...rest}
/>

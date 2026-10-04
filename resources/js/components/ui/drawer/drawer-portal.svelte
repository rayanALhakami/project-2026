<script lang="ts">
	import type { Snippet } from "svelte";
	import { Drawer as DrawerPrimitive } from "vaul-svelte";

	let { children }: { children?: Snippet } = $props();
</script>

<DrawerPrimitive.Portal>
	{@render children?.()}
</DrawerPrimitive.Portal>

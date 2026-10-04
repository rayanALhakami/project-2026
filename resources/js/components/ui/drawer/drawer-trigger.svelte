<script lang="ts">
	import type { ComponentProps } from "svelte";
	import { Drawer as DrawerPrimitive } from "vaul-svelte";

	let { ...restProps }: ComponentProps<typeof DrawerPrimitive.Trigger> = $props();
</script>

<DrawerPrimitive.Trigger data-slot="drawer-trigger" {...restProps} />

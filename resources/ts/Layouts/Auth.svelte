<script lang="ts">
  import type { Snippet } from "svelte";
  import { page } from "@inertiajs/svelte";

  import Flash from "../components/ui/Flash.svelte";

  /**
   * layouts/auth.blade.php を Svelte に移したもの。ログイン画面用。
   *
   * ルートテンプレート (app.blade.php) の <body> は
   * `flex min-h-full flex-col` なので、中央寄せはここで flex-1 して行う。
   * Blade 版は <body> 自体に中央寄せのクラスを置いていた。
   */
  type Props = { children?: Snippet };
  let { children }: Props = $props();

  const flash = $derived(page.props.flash as { message: string; status: string; icon: string } | null);
</script>

<Flash {flash} top="top-4" />

<div class="flex flex-1 items-center justify-center bg-slate-100 px-4 py-10">
  {@render children?.()}
</div>

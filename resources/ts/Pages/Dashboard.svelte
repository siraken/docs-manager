<script lang="ts">
  import { Link, page } from "@inertiajs/svelte";

  import PageHeader from "../components/ui/PageHeader.svelte";

  /**
   * TODO: ダッシュボードは静的な入り口のまま。発注書の件数や今月の売上など、
   *       既存のユースケースから引ける値を出す余地がある。
   */
  type Shortcut = { label: string; description: string; href: string; icon: string };
  type Props = { shortcuts: Shortcut[] };

  let { shortcuts }: Props = $props();

  const name = $derived((page.props.auth as { name: string } | null)?.name ?? "");
</script>

<PageHeader title="Welcome, {name}">
  {#snippet description()}よく使う機能へのショートカットです。{/snippet}
</PageHeader>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
  {#each shortcuts as shortcut (shortcut.href)}
    <Link
      href={shortcut.href}
      class="group rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-brand-300"
    >
      <span
        class="mb-3 grid h-10 w-10 place-items-center rounded-lg bg-brand-50 text-brand-700 transition group-hover:bg-brand-600 group-hover:text-white"
      >
        <i class="bi bi-{shortcut.icon}" aria-hidden="true"></i>
      </span>
      <p class="font-medium text-slate-900">{shortcut.label}</p>
      <p class="mt-1 text-sm text-slate-500">{shortcut.description}</p>
    </Link>
  {/each}
</div>

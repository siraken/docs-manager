<script lang="ts">
  import type { Snippet } from "svelte";
  import { page, router, Link } from "@inertiajs/svelte";

  import Dropdown from "../components/ui/Dropdown.svelte";
  import DropdownDivider from "../components/ui/DropdownDivider.svelte";
  import DropdownItem from "../components/ui/DropdownItem.svelte";
  import Flash from "../components/ui/Flash.svelte";

  /**
   * layouts/default.blade.php を Svelte に移したもの。
   *
   * ナビの項目・現在位置・ユーザー情報・フラッシュは、すべて Inertia の共有
   * データ (HandleInertiaRequests) から届く。Blade 版が session() や
   * request()->route()->named() を直接読んでいた部分がここに集約されている。
   *
   * まだ Blade のままの画面があるため、layouts/default.blade.php と
   * 見た目を揃えておく必要がある。
   */
  type Props = { children?: Snippet };
  let { children }: Props = $props();

  type NavItem = { label: string; href: string; active: boolean };
  type Auth = { name: string; email: string; avatarUrl: string } | null;

  const nav = $derived((page.props.nav ?? []) as NavItem[]);
  const auth = $derived((page.props.auth ?? null) as Auth);
  const menu = $derived((page.props.menu ?? {}) as Record<string, string>);
  const flash = $derived(page.props.flash as { message: string; status: string; icon: string } | null);

  let mobileOpen = $state(false);

  // ページが変わったらモバイルメニューは畳む
  $effect(() => {
    void page.url;
    mobileOpen = false;
  });

  function logout(): void {
    router.post(menu.logout);
  }

  const navLinkClass = (active: boolean): string =>
    active
      ? "rounded-lg px-3 py-2 text-sm transition bg-brand-50 font-semibold text-brand-800"
      : "rounded-lg px-3 py-2 text-sm transition text-slate-600 hover:bg-slate-100 hover:text-slate-900";

  const mobileLinkClass = (active: boolean): string =>
    active
      ? "block rounded-lg px-3 py-2 text-sm bg-brand-50 font-semibold text-brand-800"
      : "block rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100";
</script>

<Flash {flash} />

<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur">
  <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-16 items-center justify-between gap-4">
      <Link
        href={menu.home ?? "/"}
        class="flex items-center gap-2 text-sm font-semibold tracking-tight text-slate-900"
      >
        <span class="grid h-7 w-7 place-items-center rounded-lg bg-brand-600 text-xs font-bold text-white">N</span>
        Novalumo Console
      </Link>

      <!-- デスクトップ -->
      <ul class="hidden items-center gap-1 lg:flex">
        {#each nav as item (item.href)}
          <li><Link href={item.href} class={navLinkClass(item.active)}>{item.label}</Link></li>
        {/each}
      </ul>

      <div class="flex items-center gap-2">
        {#if auth}
          <Dropdown class="hidden lg:inline-block">
            {#snippet trigger()}
              <button
                type="button"
                class="flex items-center gap-2 rounded-lg py-1.5 pr-2 pl-1.5 text-sm text-slate-700 transition hover:bg-slate-100"
              >
                <img src={auth.avatarUrl} alt="" class="h-7 w-7 rounded-full bg-slate-200" width="28" height="28" />
                <span class="hidden max-w-32 truncate sm:block">{auth.name}</span>
                <i class="bi bi-chevron-down text-[10px] text-slate-400" aria-hidden="true"></i>
              </button>
            {/snippet}

            <div class="border-b border-slate-100 px-4 py-3">
              <p class="truncate text-sm font-medium text-slate-900">{auth.name}</p>
              <p class="truncate text-xs text-slate-500">{auth.email}</p>
            </div>

            <DropdownItem href={menu.files}>Files</DropdownItem>
            <DropdownDivider />
            <p class="px-4 pt-2 pb-1 text-[10px] font-semibold tracking-wide text-slate-400 uppercase">Settings</p>
            <DropdownItem href={menu.users}>User Management</DropdownItem>
            <DropdownItem href={menu.customers}>Customer Management</DropdownItem>
            <DropdownItem href={menu.contracts}>Contract Management</DropdownItem>
            <DropdownItem href={menu.accounts}>Account Management</DropdownItem>
            <DropdownItem href={menu.courses}>Course Management</DropdownItem>
            <DropdownItem href={menu.assignments}>Assignment Management</DropdownItem>
            <DropdownItem href={menu.settings}>Settings</DropdownItem>
            <DropdownDivider />
            <DropdownItem onclick={logout}>Logout</DropdownItem>
          </Dropdown>
        {/if}

        <!-- モバイル -->
        <button
          type="button"
          aria-expanded={mobileOpen}
          aria-label="メニューを開く"
          onclick={() => (mobileOpen = !mobileOpen)}
          class="rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 lg:hidden"
        >
          <i class="bi text-lg {mobileOpen ? 'bi-x-lg' : 'bi-list'}" aria-hidden="true"></i>
        </button>
      </div>
    </div>

    {#if mobileOpen}
      <div class="border-t border-slate-200 py-3 lg:hidden">
        <ul class="space-y-1">
          {#each nav as item (item.href)}
            <li><Link href={item.href} class={mobileLinkClass(item.active)}>{item.label}</Link></li>
          {/each}
        </ul>

        {#if auth}
          <div class="mt-3 border-t border-slate-200 pt-3">
            <p class="px-3 pb-2 text-xs text-slate-500">{auth.name}</p>
            <ul class="space-y-1">
              <li><Link href={menu.files} class={mobileLinkClass(false)}>Files</Link></li>
              <li><Link href={menu.users} class={mobileLinkClass(false)}>User Management</Link></li>
              <li><Link href={menu.customers} class={mobileLinkClass(false)}>Customer Management</Link></li>
              <li><Link href={menu.contracts} class={mobileLinkClass(false)}>Contract Management</Link></li>
              <li><Link href={menu.accounts} class={mobileLinkClass(false)}>Account Management</Link></li>
              <li><Link href={menu.courses} class={mobileLinkClass(false)}>Course Management</Link></li>
              <li><Link href={menu.assignments} class={mobileLinkClass(false)}>Assignment Management</Link></li>
              <li><Link href={menu.settings} class={mobileLinkClass(false)}>Settings</Link></li>
              <li>
                <button type="button" onclick={logout} class="{mobileLinkClass(false)} w-full text-left">
                  Logout
                </button>
              </li>
            </ul>
          </div>
        {/if}
      </div>
    {/if}
  </nav>
</header>

<main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
  {@render children?.()}
</main>

<script lang="ts">
  import { router } from "@inertiajs/svelte";

  /**
   * 発注書一覧のステータスピル。Blade の x-order-status を移植したもの。
   *
   * 次の状態はサーバーが保存済みの値から決めるので、現在値は送らない。
   * 移行前は window.slipSetter (status.ts) を onclick から呼び、成功したら
   * location.reload() していた。Inertia では元のページへのリダイレクトが返り、
   * その画面の props だけが取り直される。
   */
  type Props = {
    type: "issued" | "ordered";
    id: number;
    value: number;
    url: string;
  };

  let { type, id, value, url }: Props = $props();

  const LABELS: Record<string, Record<number, [string, string]>> = {
    issued: { 0: ["未発行", "muted"], 1: ["発行済み", "on"] },
    ordered: { 0: ["未受注", "muted"], 1: ["受注済み", "on"], 2: ["失注", "off"] },
  };

  const TONES: Record<string, string> = {
    muted: "bg-white text-slate-600 ring-slate-300 hover:bg-slate-50",
    on: "bg-brand-600 text-white ring-brand-600 hover:bg-brand-700",
    off: "bg-slate-700 text-white ring-slate-700 hover:bg-slate-800",
  };

  const entry = $derived(LABELS[type][value] ?? ["不明", "muted"]);
  const label = $derived(entry[0]);
  const tone = $derived(TONES[entry[1]]);

  let busy = $state(false);

  function advance(): void {
    // 二度押しで多重に投げないよう止めておく
    if (busy) {
      return;
    }

    busy = true;

    router.post(
      url,
      { id, type },
      {
        preserveScroll: true,
        onFinish: () => {
          busy = false;
        },
      },
    );
  }
</script>

<button
  type="button"
  disabled={busy}
  onclick={advance}
  class="inline-flex w-24 items-center justify-center gap-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
         transition select-none active:scale-95 disabled:opacity-60 {tone}"
>
  {#if value === 1}
    <i class="bi bi-check-lg text-[10px]" aria-hidden="true"></i>
  {/if}
  {label}
</button>

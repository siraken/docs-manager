<script lang="ts">
  /**
   * 受注前確認モーダル。
   *
   * 開閉のきっかけは Blade 側のボタンが投げる CustomEvent。
   * Blade (Alpine) と Svelte のどちらにも寄らない DOM イベントで受け渡している。
   *
   * マウント先の #projects-modal は <form id="MainForm"> の内側にあるため、
   * フッターの type="submit" はそのままフォームを送信できる。
   */
  const CHECKLISTS = [
    "ヒアリングでやることと予算感をすり合わせする",
    "契約書・NDAを取り交わす",
    "着手金、納品時残金で分けて支払いを受けるようにする",
    "なるべく納期短めの案件にする",
    "デザインはロジックで説明できるようにする",
    "スケジュールは想定の1.5倍〜2倍で出しておく",
  ];

  let open = $state(false);
  let checked = $state(CHECKLISTS.map(() => false));

  const doneCount = $derived(checked.filter(Boolean).length);
  const allChecked = $derived(doneCount === CHECKLISTS.length);

  function close() {
    open = false;
  }

  $effect(() => {
    const onOpen = () => {
      open = true;
    };
    const onKeydown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        close();
      }
    };

    window.addEventListener("open-projects-modal", onOpen);
    window.addEventListener("keydown", onKeydown);

    return () => {
      window.removeEventListener("open-projects-modal", onOpen);
      window.removeEventListener("keydown", onKeydown);
    };
  });
</script>

{#if open}
  <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:items-center">
    <!-- 背景。キーボードからは Escape で閉じられるので、ここは装飾扱いにする -->
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div class="fixed inset-0 bg-slate-900/50" onclick={close}></div>

    <div
      role="dialog"
      aria-modal="true"
      aria-labelledby="projects-modal-title"
      class="relative w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-slate-900/5 sm:max-w-lg"
    >
      <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
        <h2 id="projects-modal-title" class="text-base font-semibold text-slate-900">
          受注前確認
        </h2>
        <button
          type="button"
          aria-label="閉じる"
          onclick={close}
          class="rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
        >
          <i class="bi bi-x-lg text-sm" aria-hidden="true"></i>
        </button>
      </div>

      <div class="px-5 py-4">
        <p class="mb-3 text-sm text-slate-600">以下の確認事項を確認してください。</p>

        <ul class="space-y-2">
          {#each CHECKLISTS as check, index (check)}
            <li>
              <label
                for="check_{index}"
                class="flex cursor-pointer items-start gap-2.5 rounded-lg px-2 py-1.5 transition hover:bg-slate-50"
              >
                <input
                  type="checkbox"
                  id="check_{index}"
                  bind:checked={checked[index]}
                  class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-brand-600 focus:ring-brand-600"
                />
                <span class="text-sm text-slate-700">{check}</span>
              </label>
            </li>
          {/each}
        </ul>
      </div>

      <div class="flex items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-5 py-3">
        <p class="text-xs text-slate-500">
          {doneCount} / {CHECKLISTS.length} 件を確認済み
        </p>
        <button
          type="submit"
          id="submit_button"
          disabled={!allChecked}
          class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-brand-700 disabled:pointer-events-none disabled:opacity-50"
        >
          確認しました
        </button>
      </div>
    </div>
  </div>
{/if}

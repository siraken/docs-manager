import { createInertiaApp, type ResolvedComponent } from "@inertiajs/svelte";
import { mount } from "svelte";

import DefaultLayout from "./Layouts/Default.svelte";

/**
 * フロントのエントリ。
 *
 * 全画面が Inertia + Svelte に移行済みなので、ここは Inertia を起動するだけ。
 * ルーティングは Laravel が持ち、Inertia が #app の中身を差し替える。
 *
 * 移行中は Blade の画面と混在しており、`#app` の有無で分岐して Alpine を
 * 起動していた。Blade のビューが無くなったのでその分岐ごと消してある。
 */

const pages = import.meta.glob<ResolvedComponent>("./Pages/**/*.svelte", { eager: true });

void createInertiaApp({
  resolve: (name) => {
    const page = pages[`./Pages/${name}.svelte`];

    if (!page) {
      throw new Error(`Inertia のページが見つかりません: ${name}`);
    }

    // レイアウトはページ側で指定が無ければ既定のものを使う。
    // (ログイン画面は <script module> で Layouts/Auth を export している)
    // Inertia がレイアウトを保持するので、ページ遷移でナビは再マウントされない。
    page.layout ??= DefaultLayout;

    return page;
  },
  setup({ el, App, props }) {
    if (el) {
      mount(App, { target: el, props });
    }
  },
  progress: { color: "#00acc1" },
});

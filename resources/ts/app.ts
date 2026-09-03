import { createInertiaApp, type ResolvedComponent } from "@inertiajs/svelte";
import { mount } from "svelte";

import "./lib/alpine";
import "./lib/nfc-auth";
import "./lib/metamask-auth";

import DefaultLayout from "./Layouts/Default.svelte";

/**
 * フロントのエントリ。
 *
 * いまは Inertia の画面と Blade の画面が混在している。読み込まれる JS は
 * どちらでも同じこのファイルなので、`#app` (Inertia のマウント先) の有無で
 * どちらの世界かを判定する。
 *
 * - Inertia の画面: Pages/ 以下の Svelte がページ全体を描く
 * - Blade の画面: 従来どおり Alpine が動く
 *
 * 全画面の移行が済んだら、この分岐と Alpine の読み込みは消える。
 * (Svelte の「島」は無くなった。案件フォームが Inertia に移り、
 *  受注前確認モーダルがページの子要素になったため)
 */

const inertiaRoot = document.getElementById("app");

if (inertiaRoot) {
  const pages = import.meta.glob<ResolvedComponent>("./Pages/**/*.svelte", { eager: true });

  void createInertiaApp({
    resolve: (name) => {
      const page = pages[`./Pages/${name}.svelte`];

      if (!page) {
        throw new Error(`Inertia のページが見つかりません: ${name}`);
      }

      // レイアウトはページ側で指定が無ければ既定のものを使う。
      // (ページが <script module> で export const layout を持つ場合はそちらが優先)
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
}

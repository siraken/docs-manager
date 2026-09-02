import { vitePreprocess } from "@sveltejs/vite-plugin-svelte";

/**
 * Svelte 5。SvelteKit は使っておらず、Blade が描いた DOM に必要な画面だけ
 * コンポーネントをマウントする「島」構成 (マウントは resources/ts/app.ts)。
 *
 * vitePreprocess は <script lang="ts"> を Vite (esbuild) に通すためのもの。
 */
export default {
  preprocess: vitePreprocess(),
};

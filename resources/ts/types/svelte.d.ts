// SvelteKit を使っていないため *.svelte のアンビエント宣言が降ってこない。
// tsc は .svelte の中身までは見ない (それは svelte-check の担当) ので、
// import できることだけを宣言する。
declare module "*.svelte" {
  import type { Component } from "svelte";

  const component: Component<Record<string, never>>;

  export default component;
}

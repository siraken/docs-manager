<script lang="ts">
  /**
   * Blade の x-input を移植したもの。
   * 値は bind:value で双方向に扱う (Blade 版は value 属性だけだった)。
   */
  type Props = {
    type?: string;
    value?: string | number | null;
    class?: string;
    [key: string]: unknown;
  };

  let { type = "text", value = $bindable(""), class: extraClass = "", ...rest }: Props = $props();

  const CLASSES =
    "block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm" +
    " ring-1 ring-inset ring-slate-300 placeholder:text-slate-400" +
    " focus:ring-2 focus:ring-inset focus:ring-brand-600" +
    " disabled:bg-slate-50 disabled:text-slate-500" +
    " read-only:bg-slate-50 read-only:text-slate-600" +
    " file:mr-3 file:-my-2 file:-ml-3 file:rounded-l-lg file:border-0 file:bg-slate-100" +
    " file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700";
</script>

<!-- type は動的に変えないので、Svelte が要求する静的な type を満たすため分岐する -->
{#if type === "date"}
  <input type="date" bind:value class="{CLASSES} {extraClass}" {...rest} />
{:else if type === "number"}
  <input type="number" bind:value class="{CLASSES} {extraClass}" {...rest} />
{:else if type === "password"}
  <input type="password" bind:value class="{CLASSES} {extraClass}" {...rest} />
{:else if type === "email"}
  <input type="email" bind:value class="{CLASSES} {extraClass}" {...rest} />
{:else if type === "file"}
  <input type="file" class="{CLASSES} {extraClass}" {...rest} />
{:else}
  <input type="text" bind:value class="{CLASSES} {extraClass}" {...rest} />
{/if}

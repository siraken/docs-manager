<script lang="ts">
  type Props = {
    value?: string | null;
    rows?: number;
    class?: string;
    [key: string]: unknown;
  };

  let { value = $bindable(""), rows = 4, class: extraClass = "", ...rest }: Props = $props();

  const CLASSES =
    "block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm" +
    " ring-1 ring-inset ring-slate-300 placeholder:text-slate-400" +
    " focus:ring-2 focus:ring-inset focus:ring-brand-600";
</script>

<textarea bind:value {rows} class="{CLASSES} {extraClass}" {...rest}></textarea>

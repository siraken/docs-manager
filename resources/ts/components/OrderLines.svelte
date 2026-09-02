<script lang="ts">
  /**
   * 発注書の明細テーブル。lib/order-form.ts を Svelte に移したもの。
   *
   * 移行前は Blade が描いた <tr> を DOM API で走査し、行の追加は <template> の
   * 複製、金額は入力のたびに DOM へ書き戻す、という作りだった。行が状態として
   * 存在しなかったため、並べ替えも DOM の付け替えで表現していた。
   * ここでは行が配列そのものなので、追加・削除・並べ替えが配列操作になる。
   *
   * ここで計算する金額は**画面表示のためだけ**のもの。保存される金額は
   * サーバー側 (Domain\Order\Entity\OrderLine) が計算し直すので、この値は
   * 送信しない。税率の定義の正は Domain\Order\ValueObject\TaxRate。
   */
  import { blankOrderLine, type OrderLineDraft } from "../lib/order-line";

  type TaxOption = { value: number; label: string };

  type Props = {
    lines: OrderLineDraft[];
    taxOptions: TaxOption[];
  };

  let { lines = $bindable(), taxOptions }: Props = $props();

  /** 税区分 → 税率。サーバーの TaxRate と同じ値を表示用に持つ */
  const TAX_RATES: Record<number, number> = { 1: 0.1, 2: 0.08, 3: 0.08, 4: 0.05, 5: 0 };

  const CELL =
    "w-full bg-transparent px-1 py-1 text-sm text-slate-900 border-0 border-b border-dotted border-slate-300" +
    " focus:border-solid focus:border-brand-600 focus:outline-none";
  const READONLY = "w-full bg-transparent px-1 py-1 text-right text-sm font-medium text-slate-700";
  const SUM_INPUT = "w-full bg-transparent px-1 py-1 text-right text-sm font-semibold text-slate-900";

  /** 数量 × 単価。数として読めない入力は 0 に倒す */
  function amountOf(line: OrderLineDraft): number {
    const amount = Number(line.quantity) * Number(line.unitCost);

    return Number.isFinite(amount) ? amount : 0;
  }

  function taxOf(line: OrderLineDraft): number {
    return Math.floor(amountOf(line) * (TAX_RATES[line.taxId] ?? 0));
  }

  /** 税込金額。金額欄に出す値 */
  function totalOf(line: OrderLineDraft): number {
    return amountOf(line) + taxOf(line);
  }

  /** 数量・単価がどちらも空の行は「手つかず」とみなして集計に入れない */
  function isUntouched(line: OrderLineDraft): boolean {
    return line.quantity === "" && line.unitCost === "";
  }

  const filled = $derived(lines.filter((line) => !isUntouched(line)));
  const subtotal = $derived(filled.reduce((sum, line) => sum + amountOf(line), 0));
  const taxTotal = $derived(filled.reduce((sum, line) => sum + taxOf(line), 0));
  const total = $derived(subtotal + taxTotal);

  function addRow(): void {
    lines = [...lines, blankOrderLine()];
  }

  function removeRow(index: number): void {
    // 最後の 1 行は残す (移行前の order-form.ts と同じ)
    if (lines.length <= 1) {
      return;
    }

    lines = lines.filter((_, i) => i !== index);
  }

  // --- ドラッグでの並べ替え ---------------------------------------------

  let draggingIndex = $state<number | null>(null);
  let draggableIndex = $state<number | null>(null);

  /**
   * draggable は掴む直前に立てる。常時 true にすると入力欄の文字選択が
   * ドラッグに横取りされる (jquery-ui の cancel 既定と同じ考え方)。
   */
  function onPointerDown(event: PointerEvent, index: number): void {
    const target = event.target as HTMLElement;
    draggableIndex = target.closest("input, select, textarea, button, [role='button']") ? null : index;
  }

  function onDragStart(event: DragEvent, index: number): void {
    draggingIndex = index;
    // Firefox は dataTransfer に何か入れないとドラッグが始まらない
    event.dataTransfer?.setData("text/plain", "");

    if (event.dataTransfer) {
      event.dataTransfer.effectAllowed = "move";
    }
  }

  function onDragOver(event: DragEvent, index: number): void {
    if (draggingIndex === null || draggingIndex === index) {
      return;
    }

    // preventDefault しないとドロップ先として認識されない
    event.preventDefault();

    if (event.dataTransfer) {
      event.dataTransfer.dropEffect = "move";
    }

    const moved = [...lines];
    const [row] = moved.splice(draggingIndex, 1);
    moved.splice(index, 0, row);
    lines = moved;
    draggingIndex = index;
  }

  function onDragEnd(): void {
    draggingIndex = null;
    draggableIndex = null;
  }

  const yen = (value: number): string => value.toLocaleString("ja-JP");
</script>

<div class="overflow-x-auto">
  <table class="document-table w-full min-w-3xl">
    <thead>
      <tr>
        <th style="width: 3%" class="invisible border-none"></th>
        <th style="width: 32%">詳細</th>
        <th style="width: 12.5%">数量</th>
        <th style="width: 10%">単位</th>
        <th style="width: 12.5%">単価</th>
        <th style="width: 12%">税区分</th>
        <th style="width: 18%">金額</th>
      </tr>
    </thead>

    <tbody>
      {#each lines as line, index (index)}
        <tr
          class="sortable-tr"
          class:is-dragging={draggingIndex === index}
          draggable={draggableIndex === index}
          onpointerdown={(event) => onPointerDown(event, index)}
          ondragstart={(event) => onDragStart(event, index)}
          ondragover={(event) => onDragOver(event, index)}
          ondragend={onDragEnd}
        >
          <td class="action-cell">
            <span
              class="delete-row-button"
              role="button"
              tabindex="0"
              aria-label="行を削除"
              onclick={() => removeRow(index)}
              onkeydown={(event) => event.key === "Enter" && removeRow(index)}
            >
              &times;
            </span>
          </td>
          <td><input type="text" class={CELL} bind:value={line.itemName} /></td>
          <td><input type="text" class="{CELL} text-right" bind:value={line.quantity} /></td>
          <td><input type="text" placeholder="単位" class="{CELL} text-center" bind:value={line.unit} /></td>
          <td><input type="text" class="{CELL} text-right" bind:value={line.unitCost} /></td>
          <td>
            <select class={CELL} bind:value={line.taxId}>
              {#each taxOptions as option (option.value)}
                <option value={option.value}>{option.label}</option>
              {/each}
            </select>
          </td>
          <td>
            <!-- 表示専用。保存される金額はサーバーが計算し直す -->
            <span class="{READONLY} block">{isUntouched(line) ? "" : yen(totalOf(line))}</span>
          </td>
        </tr>
      {/each}
    </tbody>

    <tbody>
      <tr class="sum-tr">
        <td rowspan="3" class="sum-cell"></td>
        <td colspan="3" rowspan="3" class="sum-cell">
          <button
            type="button"
            onclick={addRow}
            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-sm
                   font-medium whitespace-nowrap text-slate-700 shadow-sm ring-1 ring-slate-300 transition hover:bg-slate-50"
          >
            <i class="bi bi-plus-lg" aria-hidden="true"></i>行の追加
          </button>
        </td>
        <td colspan="2" class="text-center text-sm text-slate-600">小計</td>
        <td><span class="{SUM_INPUT} block">{yen(subtotal)}</span></td>
      </tr>
      <tr class="sum-tr">
        <td colspan="2" class="text-center text-sm text-slate-600">消費税</td>
        <td><span class="{SUM_INPUT} block">{yen(taxTotal)}</span></td>
      </tr>
      <tr class="sum-tr">
        <td colspan="2" class="text-center text-sm font-semibold text-slate-700">合計</td>
        <td><span class="{SUM_INPUT} block">{yen(total)}</span></td>
      </tr>
    </tbody>
  </table>
</div>

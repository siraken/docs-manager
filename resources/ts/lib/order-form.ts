/**
 * 発注書フォームの明細テーブル。
 *
 * 金額計算・行の追加/削除・ドラッグでの並べ替えを担当する。
 * 以前は jQuery + jquery-ui の sortable で実装していたが、この 4 つの処理の
 * ためだけに 2 つのライブラリを積んでいたので素の DOM API に置き換えた。
 */

/** 税区分 select の value → 税率 */
const TAX_RATES: Record<string, number> = {
  "1": 0.1, // 10%
  "2": 0.08, // 軽減 8%
  "3": 0.08, // 8%
  "4": 0.05, // 5%
  "5": 0, // 対象外
};

const ROW_SELECTOR = "tr.sortable-tr";

/** 入力欄からのドラッグは並べ替えにしない (jquery-ui の cancel 既定と同じ挙動) */
const FIELD_SELECTOR = "input, select, textarea, button, [role='button']";

function setup(): void {
  const tbody = document.querySelector<HTMLTableSectionElement>("#sortable");
  const template =
    document.querySelector<HTMLTemplateElement>("#order-row-template");

  if (!tbody) {
    return;
  }

  const rows = () => Array.from(tbody.querySelectorAll<HTMLTableRowElement>(ROW_SELECTOR));

  const field = <T extends HTMLElement>(row: HTMLTableRowElement, name: string) =>
    row.querySelector<T>(`[name="${name}"]`);

  /**
   * 全行の金額と合計を計算し直す。
   *
   * 金額欄 (name="price[]") には**税込**の金額が入る。列見出しが「金額」で、
   * 小計・消費税・合計は別の行に出しているため。
   */
  function calcAll(): void {
    let subtotal = 0;
    let taxTotal = 0;
    let total = 0;

    for (const row of rows()) {
      const qty = field<HTMLInputElement>(row, "qty[]");
      const cost = field<HTMLInputElement>(row, "cost[]");
      const tax = field<HTMLSelectElement>(row, "tax[]");
      const price = field<HTMLInputElement>(row, "price[]");

      if (!qty || !cost || !tax || !price) {
        continue;
      }

      // 数量・単価がどちらも空の行は手つかずとみなして触らない
      if (qty.value === "" && cost.value === "") {
        continue;
      }

      const amount = Number(qty.value) * Number(cost.value);
      const taxAmount = amount * (TAX_RATES[tax.value] ?? 0);
      const amountIncludingTax = amount + taxAmount;

      // 数字として読めない入力のときは金額欄を空にする
      price.value = Number.isNaN(amount) ? "" : String(amountIncludingTax);

      subtotal += amount;
      taxTotal += taxAmount;
      total += amountIncludingTax;
    }

    setSummary("subtotal", subtotal);
    setSummary("taxTotal", taxTotal);
    setSummary("totalPrice", total);
  }

  /** 合計欄への書き込み。1 行でも数字として読めない入力があると NaN になるので 0 に倒す */
  function setSummary(id: string, value: number): void {
    const el = document.querySelector<HTMLInputElement>(`#${id}`);

    if (el) {
      el.value = String(Number.isNaN(value) ? 0 : value);
    }
  }

  // --- 行の追加 -------------------------------------------------------------
  // 行のマークアップは Blade の <template id="order-row-template">
  // (x-order-row コンポーネント) が唯一の定義。ここでは複製するだけ。
  // 関数宣言だと巻き上げのため上の tbody の null チェックが効かない。
  // アロー関数にして絞り込みを維持する。
  const addRow = (): void => {
    if (template) {
      tbody.appendChild(template.content.cloneNode(true));
    }
  };

  // Blade の「行の追加」ボタンが onclick="addRow()" で呼ぶ
  window.addRow = addRow;

  // --- 入力に応じた再計算 ---------------------------------------------------
  tbody.addEventListener("input", (event) => {
    if ((event.target as HTMLElement).classList.contains("calc")) {
      calcAll();
    }
  });

  // --- 行の削除 -------------------------------------------------------------
  tbody.addEventListener("click", (event) => {
    const button = (event.target as HTMLElement).closest(".delete-row-button");

    if (!button || rows().length <= 1) {
      return;
    }

    button.closest(ROW_SELECTOR)?.remove();
    calcAll();
  });

  // --- ドラッグでの並べ替え -------------------------------------------------
  let dragging: HTMLTableRowElement | null = null;

  // draggable は掴む直前に立てる。常時 true にすると入力欄の文字選択が
  // ドラッグに横取りされるため。
  tbody.addEventListener("pointerdown", (event) => {
    const target = event.target as HTMLElement;
    const row = target.closest<HTMLTableRowElement>(ROW_SELECTOR);

    if (row) {
      row.draggable = !target.closest(FIELD_SELECTOR);
    }
  });

  tbody.addEventListener("dragstart", (event) => {
    const row = (event.target as HTMLElement).closest<HTMLTableRowElement>(ROW_SELECTOR);

    if (!row) {
      return;
    }

    dragging = row;
    row.classList.add("is-dragging");

    // Firefox は dataTransfer に何か入れないとドラッグが始まらない
    event.dataTransfer?.setData("text/plain", "");

    if (event.dataTransfer) {
      event.dataTransfer.effectAllowed = "move";
    }
  });

  tbody.addEventListener("dragover", (event) => {
    if (!dragging) {
      return;
    }

    // preventDefault しないとドロップ先として認識されない
    event.preventDefault();

    if (event.dataTransfer) {
      event.dataTransfer.dropEffect = "move";
    }

    const over = (event.target as HTMLElement).closest<HTMLTableRowElement>(ROW_SELECTOR);

    if (!over || over === dragging) {
      return;
    }

    // 行の上半分なら手前、下半分なら後ろに差し込む
    const rect = over.getBoundingClientRect();
    const insertAfter = event.clientY > rect.top + rect.height / 2;

    tbody.insertBefore(dragging, insertAfter ? over.nextSibling : over);
  });

  tbody.addEventListener("drop", (event) => {
    event.preventDefault();
  });

  tbody.addEventListener("dragend", () => {
    if (dragging) {
      dragging.classList.remove("is-dragging");
      dragging.draggable = false;
      dragging = null;
    }
  });

  // 読み込み直後にも一度計算する (編集画面で既存の明細が入っている場合のため)
  calcAll();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", setup);
} else {
  setup();
}

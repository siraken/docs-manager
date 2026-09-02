/**
 * 発注書の明細行。フォームが編集する 1 行分の状態。
 *
 * 数値も文字列で持つ。input の値がそのまま入るためで、数として読めない入力
 * (途中まで打った "1," など) を保持できる必要がある。金額の計算は
 * OrderLines.svelte が Number() に通して行い、保存時はサーバーが計算し直す。
 */
export type OrderLineDraft = {
  itemName: string;
  quantity: string;
  unit: string;
  unitCost: string;
  taxId: number;
};

/** 空の明細行。税区分の既定は 10% (TaxRate::Standard) */
export function blankOrderLine(): OrderLineDraft {
  return { itemName: "", quantity: "", unit: "", unitCost: "", taxId: 1 };
}

/**
 * サーバーから届いた明細をフォームの行に直す。
 * 数値は input に入れるため文字列にする。
 */
export function toDraft(line: {
  itemName: string;
  quantity: number;
  unit: string | null;
  unitCost: number;
  taxId: number;
}): OrderLineDraft {
  return {
    itemName: line.itemName,
    quantity: String(line.quantity),
    unit: line.unit ?? "",
    unitCost: String(line.unitCost),
    taxId: line.taxId,
  };
}

// Blade の inline スクリプト (onclick) から呼ぶために window に生やしているもの。
// 実体はそれぞれ resources/ts/lib/ 以下にある。

interface Window {
  /** 発注書フォームの「行の追加」ボタン。実体は lib/order-form.ts */
  addRow: () => void;

  /** 発注書一覧のステータス切り替えのピル。実体は lib/status.ts */
  slipSetter: {
    status(
      el: HTMLElement,
      type: string,
      id: number,
      currentStatus: number
    ): void;
  };

  /** Blade から呼ぶユーティリティ。実体は lib/novalumo.ts */
  novalumo: unknown;
}

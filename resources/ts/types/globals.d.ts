// Blade の inline スクリプト (onclick) から呼ぶために window に生やしているもの。
// 実体はそれぞれ resources/ts/lib/ 以下にある。

interface Window {
  /** 発注書フォームの「行の追加」ボタン。実体は lib/order-form.ts */
  addRow: () => void;

  /** 発注書一覧のステータス切り替えのピル。実体は lib/status.ts
   *  次の状態はサーバーが保存済みの値から決めるので、現在値は渡さない。 */
  slipSetter: {
    status(el: HTMLElement, type: string, id: number): void;
  };

  /** Blade から呼ぶユーティリティ。実体は lib/novalumo.ts */
  novalumo: unknown;
}

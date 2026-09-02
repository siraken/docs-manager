// Blade の inline スクリプト (onclick) から呼ぶために window に生やしているもの。
// 実体はそれぞれ resources/ts/lib/ 以下にある。

interface Window {
  /** Blade から呼ぶユーティリティ。実体は lib/novalumo.ts */
  novalumo: unknown;
}

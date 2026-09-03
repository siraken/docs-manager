/**
 * NFC カードのシリアル番号を読む (ユーザー編集画面の Scan ボタン)。
 *
 * 移行前は window.novalumo として公開し、Blade の onclick から
 * input 要素を渡して value を直接書き換えていた。画面が Svelte になったので、
 * 読めた値をコールバックで返す形にしてある (DOM を直接触ると Svelte の
 * 状態と食い違う)。
 */
export function scanNfcSerialNumber(onRead: (serialNumber: string) => void): void {
  if (!("NDEFReader" in window)) {
    alert("NFC is not supported in your browser");

    return;
  }

  void (async () => {
    try {
      const reader = new NDEFReader();
      await reader.scan();

      reader.addEventListener("error", () => {
        alert("NFC の読み取りに失敗しました");
      });

      reader.addEventListener("reading", (event) => {
        onRead(String((event as NDEFReadingEvent).serialNumber));
      });
    } catch (error) {
      alert(String(error));
    }
  })();
}

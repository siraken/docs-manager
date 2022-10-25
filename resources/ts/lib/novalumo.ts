/**
 * Sign in with NFC
 */
export function setNfcNumber(el: HTMLInputElement) {
  if ("NDEFReader" in window) {
    try {
      const reader = new NDEFReader();
      (async () => {
        await reader.scan();
        reader.addEventListener("error", () => {
          alert("Error");
        });
        reader.addEventListener("reading", ({ message, serialNumber }: any) => {
          el.value = `${serialNumber}`;
        });
      })();
    } catch (error) {
      alert(error);
    }
  } else {
    alert("NFC is not supported in your browser");
    el.disabled = true;
  }
}

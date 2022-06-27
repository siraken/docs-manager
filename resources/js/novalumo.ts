import axios from "axios";
export function setNfcNumber(el: HTMLInputElement | HTMLTextAreaElement) {
  if ("NDEFReader" in window) {
    const reader = new NDEFReader();
    reader.addEventListener("error", () => {
      console.log("Error");
    });
    reader.addEventListener("reading", ({ message, serialNumber }: any) => {
      el.textContent = `${serialNumber}`;

      axios
        .post("/docs-manager/login-nfc", {
          serialNumber: serialNumber,
          pin: el.value,
        })
        .then((response) => {
          // alert(response.data.user.name);
          window.location.href = "/docs-manager/";
        })
        .catch((error) => {
          alert(error);
        });

      console.log(message);
      const record = message.records[0];
      const { data, encoding, recordType } = record;
      if (recordType === "text") {
        const textDecoder = new TextDecoder(encoding);
        const text = textDecoder.decode(data);
        el.textContent = text;
      }
    });
  } else {
    alert("NFC is not supported in your browser");
    el.disabled = true;
  }
}

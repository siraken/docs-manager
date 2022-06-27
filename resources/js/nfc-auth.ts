import axios from "axios";

window.addEventListener("load", () => {
  if ("NDEFReader" in window) {
    // Create a button to sign in with NFC
    const signInButton = document.createElement("button");
    signInButton.textContent = "Sign in with NFC";
    signInButton.style.display = "block";
    signInButton.style.zIndex = "10";
    signInButton.style.position = "fixed";
    signInButton.style.bottom = "20px";
    document.body.appendChild(signInButton);

    // Create a textarea to display the NFC tag data
    const tagDataTextarea = document.createElement("textarea");
    tagDataTextarea.setAttribute("readonly", "readonly");
    tagDataTextarea.setAttribute("rows", "10");
    tagDataTextarea.style.display = "block";
    tagDataTextarea.style.zIndex = "10";
    tagDataTextarea.style.position = "fixed";
    tagDataTextarea.style.bottom = "100px";
    tagDataTextarea.style.left = "20px";
    document.body.appendChild(tagDataTextarea);

    // input for asking PIN
    const pinInput = document.createElement("input");
    pinInput.setAttribute("type", "password");
    pinInput.setAttribute("placeholder", "PIN");
    pinInput.style.display = "block";
    pinInput.style.zIndex = "10";
    pinInput.style.position = "fixed";
    pinInput.style.bottom = "200px";
    pinInput.style.left = "20px";
    document.body.appendChild(pinInput);

    const reader = new NDEFReader();

    signInButton.addEventListener("click", async () => {
      tagDataTextarea.textContent = await "clicked read button";
      try {
        const reader = new NDEFReader();
        await reader.scan();
        tagDataTextarea.textContent = "scan started";

        reader.addEventListener("error", () => {
          console.log("Error");
        });

        reader.addEventListener("reading", ({ message, serialNumber }: any) => {
          tagDataTextarea.textContent += `> Serial Number: ${serialNumber}`;

          axios
            .post("/docs-manager/login-nfc", {
              serialNumber: serialNumber,
              pin: pinInput.value,
            })
            .then(function (response) {
              alert(response.data.user.name);
            })
            .catch(function (error) {
              alert(error);
            });

          console.log(message);
          const record = message.records[0];
          const { data, encoding, recordType } = record;
          if (recordType === "text") {
            const textDecoder = new TextDecoder(encoding);
            const text = textDecoder.decode(data);
            tagDataTextarea.textContent = text;
          }
        });
      } catch (error) {
        tagDataTextarea.textContent = String(error);
      }
    });
  }
});

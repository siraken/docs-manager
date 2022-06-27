import axios from "axios";

window.addEventListener("load", () => {
  // For debugging on PC
  // (window as any).NDEFReader = "";

  if ("NDEFReader" in window && window.location.href.match(/login/)) {
    // Create a box
    const signInWithNFC = document.createElement("div");
    signInWithNFC.className = "box";
    signInWithNFC.id = "signInWithNFC";
    signInWithNFC.style.display = "flex";
    signInWithNFC.style.flexDirection = "column";
    signInWithNFC.style.alignItems = "center";
    signInWithNFC.style.justifyContent = "center";
    signInWithNFC.style.gap = "0.25rem";
    signInWithNFC.style.padding = "0.5rem";
    signInWithNFC.style.position = "fixed";
    signInWithNFC.style.top = "0";
    signInWithNFC.style.right = "0";
    signInWithNFC.style.backgroundColor = "white";
    signInWithNFC.style.boxShadow = "0 0 5px rgba(0, 0, 0, 0.5)";

    // Create a text
    const signInWithNFCText = document.createElement("div");
    signInWithNFCText.className = "text";
    signInWithNFCText.innerHTML = "Sign in with NFC";
    signInWithNFCText.style.fontSize = "1.25rem";
    // signInWithNFCText.style.fontWeight = "bold";
    signInWithNFCText.style.textAlign = "center";
    signInWithNFC.appendChild(signInWithNFCText);

    // Create a textarea to display the NFC tag data
    const tagDataTextarea = document.createElement("textarea");
    tagDataTextarea.classList.add("form-control");
    tagDataTextarea.setAttribute("readonly", "readonly");
    tagDataTextarea.setAttribute("rows", "2");
    tagDataTextarea.style.display = "block";
    signInWithNFC.appendChild(tagDataTextarea);

    // Create a input to enter the PIN code
    const pinInput = document.createElement("input");
    pinInput.classList.add("form-control");
    pinInput.setAttribute("type", "password");
    pinInput.setAttribute("placeholder", "PIN");
    pinInput.style.display = "block";
    signInWithNFC.appendChild(pinInput);

    // Create a button to scan and sign in
    const signInButton = document.createElement("button");
    signInButton.textContent = "Scan and Sign In";
    signInButton.classList.add("btn", "btn-primary");
    signInButton.style.display = "block";
    signInWithNFC.appendChild(signInButton);

    document.body.appendChild(signInWithNFC);

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
              // alert(response.data.user.name);
              window.location.href = "/docs-manager/";
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

import axios from "axios";

// const DOCUMENT_ROOT = "/docs-manager";
const DOCUMENT_ROOT = "";

window.addEventListener("load", async () => {
  if ("ethereum" in window && window.location.href.match(/login/)) {
    if (confirm("Are you sure you want to sign in with MetaMask?")) {
      let address = "";
      try {
        address = await window.ethereum
          .request({ method: "eth_requestAccounts" })
          .then((data: any) => {
            return data[0];
          })
          .catch((error: Error) => {
            console.error("Error!");
          });
      } catch (error) {
        console.error(error);
      }

      // TODO: More secure way to get the address
      if (address) {
        console.info("Authenticating with address:", address);
        axios
          .post(`${DOCUMENT_ROOT}/login-metamask`, {
            address: address,
          })
          .then((response) => {
            window.location.href = `${DOCUMENT_ROOT}/`;
          })
          .catch((error) => {
            alert(error);
          });
      }
    }
  } else {
    console.error("Metamask is not supported in your browser");
  }
});

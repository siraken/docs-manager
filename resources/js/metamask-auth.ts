import axios from "axios";

const Chains = {
  1: "Mainnet",
  3: "Ropsten",
  4: "Rinkeby",
  42: "Kovan",
  1337: "Geth Private Chain (default)",
  61: "Ethereum Classic Mainnet",
  62: "Morden",
};

const DOCUMENT_ROOT =
  process.env.MIX_APP_ENV === "local" ? "" : "/docs-manager";

window.addEventListener("load", async () => {
  if ("ethereum" in window && window.location.href.match(/login/)) {
    if (confirm("Are you sure you want to sign in with MetaMask?")) {
      let address: string = "";
      try {
        address = await window.ethereum
          .request({ method: "eth_requestAccounts" })
          .then((accounts: any) => {
            if (accounts.length > 0) {
              if (window.ethereum.chainId !== "0x1") {
                alert("Please switch to the main network.");
              } else {
                return accounts[0];
              }
            } else {
              console.error("Not logged in");
            }
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

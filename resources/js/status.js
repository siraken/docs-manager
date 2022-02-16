const axiosBase = require("axios");
const axios = axiosBase.create({
    // baseURL: "http://127.0.0.1:8000",
    baseURL: "/docs-manager/",
    headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
    },
    responseType: "json",
});

const slipSetter = {
    /**
     *
     */
    status(el, type, id, currentStatus) {
        const csrf = document.getElementsByName("_token")[0].value;
        const url = "/orders/set-status";
        axios
            .post(url, {
                type: type,
                id: id,
                currentStatus: currentStatus,
                _csrfToken: csrf,
            })
            .then((res) => {
                if (res.data.status === 200) {
                    location.reload();
                } else {
                    console.log("Failed");
                }
            });
    },
};

window.slipSetter = slipSetter;

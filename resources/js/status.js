import { http } from "../ts/lib/http";

/**
 * 発注書一覧のステータス切り替え。
 *
 * **このファイルは現状どこからも読み込まれていない** (Vite の input にも、
 * どの Blade / TS からも入っていない)。orders/index と orders/trash のピルは
 * `onclick="slipSetter.status(...)"` を呼ぶため、実際にはクリックすると
 * `ReferenceError: slipSetter is not defined` になる。バンドルへの組み込みは
 * 後続 PR で行う。ここでは axios を落とすための書き換えだけをしている
 * (CommonJS の require → ESM、axios → ky)。
 */
const slipSetter = {
  status(el, type, id, currentStatus) {
    const csrf = document.getElementsByName("_token")[0].value;

    // 旧実装は axios の baseURL "/docs-manager/" と URL "/orders/set-status" を
    // 合成していた。その結果と同じパスをそのまま書いている
    const url = "/docs-manager/orders/set-status";

    http
      .post(url, {
        json: {
          type: type,
          id: id,
          currentStatus: currentStatus,
          _csrfToken: csrf,
        },
      })
      .json()
      .then((data) => {
        if (data.status === 200) {
          location.reload();
        } else {
          console.log("Failed");
        }
      });
  },
};

window.slipSetter = slipSetter;

import { http } from "./http";

/**
 * 発注書一覧 (orders/index, orders/trash) のステータス切り替え。
 *
 * Blade 側のピルが `onclick="slipSetter.status(this, ...)"` で呼ぶ。
 * 一覧の Blade は、このスクリプトがトークンを読めるように `@csrf` だけを
 * 単体で出力している。
 */

const DOCUMENT_ROOT =
  import.meta.env.VITE_APP_ENV === "local" ? "" : "/docs-manager";

type SetStatusResponse = {
  status?: number;
};

export const slipSetter: Window["slipSetter"] = {
  status(el, type, id, currentStatus) {
    const token =
      document.querySelector<HTMLInputElement>('input[name="_token"]')?.value ??
      "";

    // 二度押しで多重に投げないようボタンを止めておく
    if (el instanceof HTMLButtonElement) {
      el.disabled = true;
    }

    http
      .post(`${DOCUMENT_ROOT}/orders/set-status`, {
        json: {
          type: type,
          id: id,
          currentStatus: currentStatus,
          _token: token,
        },
      })
      .json<SetStatusResponse>()
      .then((data) => {
        if (data.status === 200) {
          location.reload();
        } else {
          console.error("ステータスの更新に失敗しました", data);
        }
      })
      .catch((error) => {
        console.error("ステータスの更新に失敗しました", error);
      })
      .finally(() => {
        if (el instanceof HTMLButtonElement) {
          el.disabled = false;
        }
      });
  },
};

window.slipSetter = slipSetter;

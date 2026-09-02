import { mount } from "svelte";

import "./lib/alpine";
import "./lib/order-form";
import "./lib/status";
import "./lib/nfc-auth";
import "./lib/metamask-auth";
import * as novalumo from "./lib/novalumo";

import ProjectsModal from "./components/ProjectsModal.svelte";

// Blade の inline スクリプトから window.novalumo として呼ばれる
window.novalumo = novalumo;

/**
 * Svelte コンポーネントのマウント。
 *
 * このアプリは Blade によるサーバーサイドレンダリングが主体で、Svelte は
 * 「特定の DOM に差し込む島」として使う。マウント先が無い画面では何もしない。
 *
 * 以前は React のコンポーネントが各ファイルの末尾で自分をマウントしていたが、
 * どこに何が生えるのか追いにくかったため、ここに集約している。
 */
const ISLANDS = [{ selector: "#projects-modal", component: ProjectsModal }];

for (const { selector, component } of ISLANDS) {
  const target = document.querySelector(selector);

  if (target) {
    mount(component, { target });
  }
}

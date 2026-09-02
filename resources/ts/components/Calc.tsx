import React, { useState, useMemo } from "react";
import { createRoot } from "react-dom/client";

// {/* website */}
// <section id="website" className="row">
// <div className="col-12">
//   <h2>Webサイト制作</h2>
//   {/* サイト設計 */}
//   <h3>サイト設計</h3>
//   <div className="row mb-3">
//     <div className="col-md-6">
//       <label>サイトマップ</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//     <div className="col-md-6">
//       <label>ワイヤーフレーム</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//     <div className="col-md-6">
//       <label>コンテンツ案</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//     <div className="col-md-6">
//       <label>コンテンツ制作</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//     <div className="col-md-6">
//       <label>コンテンツ流し込み</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//   </div>
//   {/* SEO */}
//   <h3>SEO</h3>
//   <div className="row mb-3">
//     <div className="col-md-6">
//       <label>分析</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//     <div className="col-md-6">
//       <label>マーケティング戦略</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//   </div>
//   {/* デザイン */}
//   <h3>デザイン</h3>
//   <div className="row mb-3">
//     <div className="col-md-6">
//       <label>デザイン</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//   </div>
//   {/* コーディング */}
//   <h3>コーディング</h3>
//   <div className="row mb-3">
//     <div className="col-md-6">
//       <label>コーディング</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//   </div>
//   {/* 環境構築 */}
//   <h3>環境構築</h3>
//   <div className="row mb-3">
//     <div className="col-md-6">
//       <label>サーバー</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//     <div className="col-md-6">
//       <label>ドメイン</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//     <div className="col-md-6">
//       <label>CMS</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//     <div className="col-md-6">
//       <label>お問い合わせ</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//     <div className="col-md-6">
//       <label>セキュリティ</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//     <div className="col-md-6">
//       <label>リダイレクト</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//   </div>
//   {/* テスト */}
//   <h3>テスト</h3>
//   <div className="row mb-3">
//     <div className="col-md-6">
//       <label>テスト</label>
//       <input type="number" className="form-control" placeholder="" />
//     </div>
//   </div>
//   {/* サポート */}
//   <h3>サポート</h3>
//   <div className="row mb-3">
//     <div className="col-md-6">
//       <label>年間サポート</label>
//       <select className="form-control">
//         <option value="" selected disabled>
//           選択してください
//         </option>
//         <option v-for="webSupportOption in webSupportOptions"></option>
//       </select>
//     </div>
//   </div>
//   <hr />
//   {/* total */}
//   <div className="row">
//     <div className="col">
//       <p className="h3 mb-0">
//         合計金額：
//         <span className="font-weight-bold">¥ @</span>
//       </p>
//       <p className="h5 mb-0">
//         消費税：
//         <span className="font-weight-bold">¥ </span>
//       </p>
//     </div>
//   </div>
// </div>
// </section>

const webOptions = [
  { text: "Basic - ¥35,400", value: 35400 },
  { text: "Standard - ¥72,400", value: 72400 },
  { text: "Advanced - ¥142,000", value: 142000 },
  { text: "Pro - ¥245,000", value: 245000 },
  { text: "Enterprise - ¥385,000", value: 385000 },
];
const webSupportOptions = [
  { text: "Basic - ¥21,600", value: 21600 },
  { text: "Basic Plus - ¥25,200", value: 25200 },
  { text: "Pro - ¥42,000", value: 42000 },
  { text: "Enterprise - ¥81,600", value: 81600 },
];
const systemOptions = [
  { text: "Basic - ¥75,000", value: 75000 },
  { text: "Standard - ¥128,000", value: 128000 },
  { text: "Enterprise - ¥580,000", value: 580000 },
];

type websiteProps = {
  price: number;
};

type systemProps = {
  price: number;
  plan: 0;
  member: number;
};

type unitProps = {
  day: number;
  month: number;
};

function Calc() {
  // states
  const [website, setWebsite] = useState<websiteProps>({ price: 0 });
  const [system, setSystem] = useState<systemProps>({
    price: 0,
    plan: 0,
    member: 0,
  });

  const [unit, setUnit] = useState<unitProps>({ day: 0, month: 0 });

  // computed
  const totalPrice = useMemo(() => system.price * 1.1, [system.price]);

  return (
    <>
      {/* system */}
      <h1>料金計算</h1>
      <div className="row">
        <div className="col-md-6">
          <label>人日単位</label>
          <div className="input-group mb-3">
            <span className="input-group-text">¥</span>
            <input
              type="number"
              className="form-control"
              value={unit.day}
              disabled={unit.month !== 0}
              onChange={(e) =>
                setUnit({ day: Number(e.target.value), month: unit.month })
              }
            />
          </div>
        </div>
        <div className="col-md-6">
          <label>人月単位</label>
          <div className="input-group mb-3">
            <span className="input-group-text">¥</span>
            <input
              type="number"
              className="form-control"
              value={unit.month}
              disabled={unit.day !== 0}
              onChange={(e) =>
                setUnit({ day: unit.day, month: Number(e.target.value) })
              }
            />
          </div>
        </div>
      </div>
      <div className="row">
        <div className="col-12">
          <label>タスク</label>
          {[1, 2, 3, 4, 5].map((i) => (
            <div className="input-group mb-1">
              {/* <span className="input-group-text">¥</span> */}
              <input
                type="text"
                className="form-control w-75"
                placeholder="内容"
              />
              <input
                type="text"
                className="form-control w-25"
                placeholder="工数"
              />
            </div>
          ))}
        </div>
      </div>
    </>
  );
}

export default Calc;

const rootElement = document.getElementById("calc");
if (rootElement) {
  createRoot(rootElement).render(<Calc />);
}

import React, { useState, useMemo } from "react";
import { createRoot } from "react-dom/client";

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

const labelClass = "mb-1.5 block text-sm font-medium text-slate-700";
const inputClass =
  "block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm" +
  " ring-1 ring-inset ring-slate-300 placeholder:text-slate-400" +
  " focus:ring-2 focus:ring-inset focus:ring-brand-600 disabled:bg-slate-50 disabled:text-slate-400";

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
      <h1 className="mb-6 text-xl font-semibold tracking-tight text-slate-900">
        料金計算
      </h1>

      <div className="mb-6 grid gap-4 sm:grid-cols-2">
        <div>
          <label htmlFor="unit-day" className={labelClass}>
            人日単位
          </label>
          <div className="flex">
            <span className="inline-flex items-center rounded-l-lg bg-slate-100 px-3 text-sm text-slate-500 ring-1 ring-inset ring-slate-300">
              ¥
            </span>
            <input
              id="unit-day"
              type="number"
              className={`${inputClass} rounded-l-none`}
              value={unit.day}
              disabled={unit.month !== 0}
              onChange={(e) =>
                setUnit({ day: Number(e.target.value), month: unit.month })
              }
            />
          </div>
        </div>

        <div>
          <label htmlFor="unit-month" className={labelClass}>
            人月単位
          </label>
          <div className="flex">
            <span className="inline-flex items-center rounded-l-lg bg-slate-100 px-3 text-sm text-slate-500 ring-1 ring-inset ring-slate-300">
              ¥
            </span>
            <input
              id="unit-month"
              type="number"
              className={`${inputClass} rounded-l-none`}
              value={unit.month}
              disabled={unit.day !== 0}
              onChange={(e) =>
                setUnit({ day: unit.day, month: Number(e.target.value) })
              }
            />
          </div>
        </div>
      </div>

      <div>
        <span className={labelClass}>タスク</span>
        <div className="space-y-2">
          {[1, 2, 3, 4, 5].map((i) => (
            <div key={i} className="flex gap-2">
              <input
                type="text"
                className={`${inputClass} basis-3/4`}
                placeholder="内容"
              />
              <input
                type="text"
                className={`${inputClass} basis-1/4`}
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

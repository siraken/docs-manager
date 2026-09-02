import * as React from "react";

const SHORTCUTS = [
  { label: "出張申請", href: "/trips" },
  { label: "出張旅費精算", href: "/expenses" },
  { label: "発注書作成", href: "/orders" },
];

const DashboardPage = () => {
  return (
    <>
      <h1 className="mb-6 text-xl font-semibold tracking-tight text-slate-900">
        Welcome, Name
      </h1>

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {SHORTCUTS.map((shortcut) => (
          <a
            key={shortcut.href}
            href={shortcut.href}
            className="rounded-xl bg-white p-5 font-medium text-slate-900 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-brand-300"
          >
            {shortcut.label}
          </a>
        ))}
      </div>
    </>
  );
};

export default DashboardPage;

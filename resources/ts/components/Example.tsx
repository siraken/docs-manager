import React from "react";
import { createRoot } from "react-dom/client";

function Example() {
  return (
    <div className="mx-auto max-w-2xl">
      <div className="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div className="border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-900">
          Example Component
        </div>
        <div className="px-5 py-4 text-sm text-slate-600">
          I'm an example component!
        </div>
      </div>
    </div>
  );
}

export default Example;

const rootElement = document.getElementById("example");
if (rootElement) {
  createRoot(rootElement).render(<Example />);
}

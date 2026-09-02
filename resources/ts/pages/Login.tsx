import * as React from "react";

const LoginPage = () => {
  return (
    <main className="mx-auto w-full max-w-sm">
      <div className="mb-6 flex flex-col items-center gap-3">
        <span className="grid h-11 w-11 place-items-center rounded-xl bg-brand-600 text-lg font-bold text-white">
          N
        </span>
        <h1 className="text-lg font-semibold tracking-tight text-slate-900">
          Novalumo Console
        </h1>
      </div>

      <div className="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <form method="post" className="space-y-4">
          <div>
            <label
              htmlFor="email"
              className="mb-1.5 block text-sm font-medium text-slate-700"
            >
              メールアドレス
            </label>
            <input
              type="email"
              id="email"
              name="email"
              autoComplete="username"
              placeholder="name@example.com"
              className="block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-brand-600"
            />
          </div>

          <div>
            <label
              htmlFor="password"
              className="mb-1.5 block text-sm font-medium text-slate-700"
            >
              パスワード
            </label>
            <input
              type="password"
              id="password"
              name="password"
              autoComplete="current-password"
              className="block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-brand-600"
            />
          </div>

          <button
            type="submit"
            className="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-brand-700"
          >
            サインイン
          </button>
        </form>
      </div>

      <p className="mt-6 text-center text-xs text-slate-400">&copy; Novalumo</p>
    </main>
  );
};

export default LoginPage;

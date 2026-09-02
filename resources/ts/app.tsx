import React from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import DashboardPage from "./pages/Dashboard";
import LoginPage from "./pages/Login";

import "./lib/alpine";
import "./lib/order-form";
import "./lib/status";
import "./lib/nfc-auth";
import "./lib/metamask-auth";
import * as novalumo from "./lib/novalumo";

import "./components/Example";
import "./components/Calc";
import "./components/ProjectsModal";

// Blade の inline スクリプトから window.novalumo として呼ばれる
(window as any).novalumo = novalumo;

const App = () => (
  <BrowserRouter>
    <Routes>
      <Route path="/" element={<DashboardPage />} />
      <Route path="/login" element={<LoginPage />} />
    </Routes>
  </BrowserRouter>
);

const rootElement = document.getElementById("app");
if (rootElement) {
  createRoot(rootElement).render(<App />);
}

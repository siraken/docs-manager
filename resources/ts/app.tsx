import React from "react";
import ReactDOM from "react-dom";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import DashboardPage from "./pages/Dashboard";
import LoginPage from "./pages/Login";

import "./lib/bootstrap";
import "./lib/jquery/jquery";
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

if (document.getElementById("app")) {
  ReactDOM.render(<App />, document.getElementById("app"));
}

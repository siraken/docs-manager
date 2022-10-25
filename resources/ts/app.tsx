import React from "react";
import ReactDOM from "react-dom";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import DashboardPage from "./pages/Dashboard";
import LoginPage from "./pages/Login";

require("./lib/bootstrap");

//  require("./status");
require("./lib/jquery/jquery");
require("./lib/nfc-auth");
require("./lib/metamask-auth");

(window as any).novalumo = require("./lib/novalumo");

require("./components/Example");
require("./components/Calc");
require("./components/ProjectsModal");

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

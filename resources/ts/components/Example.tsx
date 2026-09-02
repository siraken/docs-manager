import React from "react";
import { createRoot } from "react-dom/client";

function Example() {
  return (
    <div className="container">
      <div className="row justify-content-center">
        <div className="col-md-8">
          <div className="card">
            <div className="card-header">Example Component</div>

            <div className="card-body">I'm an example component!</div>
          </div>
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

import * as React from "react";

const DashboardPage = () => {
  return (
    <>
      <div className="row mb-3">
        <div className="col-12">
          <h1 className="h3">Welcome, Name</h1>
        </div>
      </div>

      <div className="row">
        <div className="col-12">
          <div className="d-flex gap-3">
            <a
              href="{{ route('trips.index') }}"
              className="btn btn-secondary shadow-sm p-3"
            >
              出張申請
            </a>
            <a
              href="{{ route('expenses.index') }}"
              className="btn btn-secondary shadow-sm p-3"
            >
              出張旅費精算
            </a>
            <a
              href="{{ route('orders.index') }}"
              className="btn btn-secondary shadow-sm p-3"
            >
              発注書作成
            </a>
          </div>
        </div>
      </div>
    </>
  );
};

export default DashboardPage;

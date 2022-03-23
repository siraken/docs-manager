<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvoiceHeader;
use App\Models\InvoiceDetail;

class InvoiceController extends Controller
{
    /**
     * Index
     */
    public function index()
    {
        $invoices = InvoiceHeader::all();
        return view('invoices/index', compact('invoices'));
    }

    /**
     * Create
     */
    public function create(Request $request)
    {
        $invoice = new InvoiceHeader();
        return view('invoices/create');
    }

    /**
     * Edit
     */
    public function edit(Request $request, $id = null)
    {
        $invoice = InvoiceHeader::find($id);
        return view('invoices/edit');
    }

    /**
     * PDF
     */
    public function pdf()
    {
        // do nothing
    }
}

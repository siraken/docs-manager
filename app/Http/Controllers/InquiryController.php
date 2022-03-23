<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inquiry;

class InquiryController extends Controller
{
    /**
     * Index
     *
     */
    public function index()
    {
        $inquiries = Inquiry::all();
        return view('inquiries/index', compact('inquiries'));
    }

    /**
     * Create
     *
     */
    public function create(Request $request)
    {
        $inquiry = new Inquiry();

        if ($request->isMethod('POST'))
        {
            if ($inquiry->fill($request->all())->save())
            {
                return redirect('/inquiries')->with('flash_message', 'Successful');
            }
        }

        return view('inquiries/form', compact('inquiry'));
    }

    /**
     * Edit
     *
     */
    public function edit(Request $request, $id = null)
    {
        $inquiry = Inquiry::find($id);

        if ($request->isMethod('POST'))
        {
            if ($inquiry->fill($request->all())->save())
            {
                return redirect('/inquiries')->with('flash_message', 'Successful');
            }
        }

        return view('inquiries/form', compact('inquiry'));
    }

    /**
     * View
     *
     */
    public function view($id)
    {
        $inquiry = Inquiry::find($id);
        return view('inquiries/view', compact('inquiry'));
    }

}

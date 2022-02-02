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
        if ($request->isMethod('POST'))
        {
            $inquiry = new Inquiry();
            if ($inquiry->fill($request->all())->save())
            {
                return redirect('/inquiries')->with('flash_message', 'Successful');
            }
        }

        return view('inquiries/create');
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

    /**
     * Truncate
     *
     */
    public function truncate(Request $request)
    {
        if (Inquiry::truncate())
        {
            return redirect('/inquiries')->with('flash_message', 'Truncate Successful');
        }
    }
}

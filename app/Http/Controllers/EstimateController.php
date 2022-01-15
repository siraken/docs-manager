<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstimateHeader;

class EstimateController extends Controller
{
    /**
     * Index
     *
     */
    public function index()
    {
        $estimates = EstimateHeader::all();
        return view('estimate/index', compact('estimates'));
    }

    /**
     * Create
     *
     */
    public function create()
    {
        $estimates = EstimateHeader::all();
        return view('estimate/create', compact('estimates'));
    }

    /**
     * Edit
     *
     */
    public function edit()
    {
        $estimates = EstimateHeader::all();
        return view('estimate/edit', compact('estimates'));
    }

    /**
     * Trash
     *
     */
    public function trash()
    {
        $estimates = EstimateHeader::all();
        return view('estimate/index', compact('estimates'));
    }



}

<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        // Fetch all companies
        $companies = Company::all();

        return response()->json(['companies' => $companies], 200);
    }
}

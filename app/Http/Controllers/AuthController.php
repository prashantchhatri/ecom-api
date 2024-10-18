<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function registerCompany(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'speciality' => 'nullable|string|max:255',
            'gst_no' => 'nullable|string|max:50',
            'registration_no' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the company
        $company = Company::create([
            'name' => $request->name,
            'speciality' => $request->speciality,
            'gst_no' => $request->gst_no,
            'registration_no' => $request->registration_no,
        ]);

        return response()->json(['company' => $company, 'message' => 'Company registered successfully'], 201);
    }

    public function registerUser(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:15|unique:users',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'company_id' => 'nullable|exists:companies,id|required_if:role_id,!=,1',
            'role_id' => 'required|exists:roles,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Superadmin users (role_id = 1) should have company_id = null
        $companyId = $request->role_id == 1 ? null : $request->company_id;
    
        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'city' => $request->city,
            'address' => $request->address,
            'pincode' => $request->pincode,
            'company_id' => $companyId,
            'role_id' => $request->role_id,
        ]);
    
        return response()->json(['user' => $user, 'message' => 'User registered successfully'], 201);
    }
    

    public function login(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Attempt to log the user in
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        // Generate a token for the user
        $user = Auth::user();
        $token = $user->createToken('authToken')->accessToken;
    
        return response()->json(['token' => $token, 'user' => $user], 200);
    }
}

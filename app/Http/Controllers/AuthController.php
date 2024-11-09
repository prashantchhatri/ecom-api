<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User, Company};
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\{Auth, Hash, Validator};

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
            'company_id' => 'required_unless:role_id,1|nullable|exists:companies,id',
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

        $roleName = Role::find($request->role_id)->role;
        $user->assignRole($roleName);
    
        return response()->json(['user' => $user, 'message' => ucfirst($roleName) . ' registered successfully'], 201);
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

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Logged out successfully.'], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();  // Get the authenticated user
    
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:users,phone,' . $user->id,
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Update user's details
        $user->update($request->only('name', 'phone', 'city', 'address', 'pincode'));
    
        return response()->json(['message' => 'Profile updated successfully', 'user' => $user], 200);
    }
    
    public function requestPasswordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $status = Password::sendResetLink($request->only('email'));
    
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to your email.'], 200);
        } else {
            return response()->json(['message' => 'Unable to send password reset link.'], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Reset password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully']);
        } else {
            return response()->json(['message' => 'Invalid token or email'], 400);
        }
    }

    


}

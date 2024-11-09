<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User, Company};
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\{Auth, Hash, Validator};

class AuthController extends Controller
{
        /**
     * @OA\Post(
     *     path="/api/register-company",
     *     tags={"Auth"},
     *     summary="Register a new company",
     *     description="Register a new company with company details",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="My Ecommerce"),
     *             @OA\Property(property="speciality", type="string", example="Electronics"),
     *             @OA\Property(property="gst_no", type="string", example="22AAAAA0000A1Z5"),
     *             @OA\Property(property="registration_no", type="string", example="123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Company registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="company", type="object"),
     *             @OA\Property(property="message", type="string", example="Company registered successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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
    
/**
 * @OA\Post(
 *     path="/api/register-user",
 *     tags={"Auth"},
 *     summary="Register a new user",
 *     description="Registers a user with details like name, email, password, phone, city, and role",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password", "phone", "city", "role_id"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
 *             @OA\Property(property="password", type="string", example="password123"),
 *             @OA\Property(property="phone", type="string", example="9876543210"),
 *             @OA\Property(property="city", type="string", example="New York"),
 *             @OA\Property(property="address", type="string", example="123 Main St"),
 *             @OA\Property(property="pincode", type="string", example="10001"),
 *             @OA\Property(property="company_id", type="integer", example=1),
 *             @OA\Property(property="role_id", type="integer", example=2)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User registered successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="user", type="object"),
 *             @OA\Property(property="message", type="string", example="User registered successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
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
    
/**
 * @OA\Post(
 *     path="/api/login",
 *     tags={"Auth"},
 *     summary="User login",
 *     description="Authenticates a user and returns an access token",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="token", type="string", example="your_access_token_here"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Invalid credentials")
 *         )
 *     )
 * )
 */

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

/**
 * @OA\Post(
 *     path="/api/logout",
 *     tags={"Auth"},
 *     summary="User logout",
 *     description="Logs out the authenticated user by revoking their token",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Logout successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Logged out successfully.")
 *         )
 *     )
 * )
 */

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Logged out successfully.'], 200);
    }

/**
 * @OA\Post(
 *     path="/api/update-profile",
 *     tags={"Auth"},
 *     summary="Update user profile",
 *     description="Updates the profile information of the authenticated user",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "phone", "city"},
 *             @OA\Property(property="name", type="string", example="John Doe Updated"),
 *             @OA\Property(property="phone", type="string", example="1234567890"),
 *             @OA\Property(property="city", type="string", example="New York"),
 *             @OA\Property(property="address", type="string", example="Updated Address"),
 *             @OA\Property(property="pincode", type="string", example="10002")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Profile updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
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
  
/**
 * @OA\Post(
 *     path="/api/password-reset/request",
 *     tags={"Auth"},
 *     summary="Request password reset",
 *     description="Sends a password reset link to the user's email",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email"},
 *             @OA\Property(property="email", type="string", example="johndoe@example.com")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password reset link sent",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Password reset link sent to your email.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Unable to send password reset link",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unable to send password reset link.")
 *         )
 *     )
 * )
 */
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

/**
 * @OA\Post(
 *     path="/api/password-reset/reset",
 *     tags={"Auth"},
 *     summary="Reset password",
 *     description="Resets the user's password using a reset token",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "token", "password", "password_confirmation"},
 *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
 *             @OA\Property(property="token", type="string", example="reset_token_here"),
 *             @OA\Property(property="password", type="string", example="newpassword123"),
 *             @OA\Property(property="password_confirmation", type="string", example="newpassword123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password reset successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Password reset successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid token or email",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Invalid token or email")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
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

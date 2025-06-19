<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Doctors;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\JWTAuth;
// use Tymon\JWTAuth\Facades\JWTAuth; // Ensure the facade is imported
use App\Models\RefreshToken;    
use Carbon\Carbon;

use Tymon\JWTAuth\Exceptions\JWTException; // Uncomment if using JWTException




class AuthController extends Controller
{
   

    // use Illuminate\Http\Request;
    // use Illuminate\Support\Facades\Hash;
    // use Illuminate\Validation\ValidationException;
    // use App\Models\User;
    
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function contacts()
    {
        $users = User::all();
        return response()->json($users);
       
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required', // Can be email or phone number
            'password' => 'required',
        ]);

        // Find user by email or phone number
        $user = User::where('email', $request->username)
                    ->orWhere('phoneNumber', $request->username)
                    ->first();

        // If user is not found, return a custom error message
        if (!$user) {
            throw ValidationException::withMessages([
                'username' => ['No account found with this email or phone number.'],
            ]);
        }

        // Check password and attempt JWT authentication
        $credentials = [
            'email' => $user->email, // Use the found user's email
            'password' => $request->password,
        ];

        if (!$accessToken = $this->jwt->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Generate refresh token
        $refreshToken = Str::random(64);
        $user = auth()->user();

        // Store refresh token in database
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $refreshToken,
            'expires_at' => Carbon::now()->addDays(14), 
        ]);

        // Hide password and other sensitive data
        $user->makeHidden(['password']);

        // Return user and JWT token
        // return response()->json([
        //     'user' => $user,
        //     'token' => $token,
        // ]);
        return response()->json([
            'message' => 'Logged in',
            'user' => $user,
            'access_token' => $accessToken,
            ])
            ->cookie('access_token', $accessToken, 15, null, null, true, true, false, 'strict')
            ->cookie('refresh_token', $refreshToken, 14 * 24 * 60, null, null, true, true, false, 'strict');
    }



    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['error' => 'Refresh token missing'], 401);
        }

        // Verify refresh token
        $tokenRecord = RefreshToken::where('token', $refreshToken)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$tokenRecord) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        // Generate new access token
        $user = User::find($tokenRecord->user_id);
        // $newAccessToken = JWTAuth::fromUser($user);
        $newAccessToken = $this->jwt->fromUser($user);
        

        // Optionally, issue a new refresh token and invalidate the old one
        $newRefreshToken = Str::random(64);
        $tokenRecord->update([
            'token' => $newRefreshToken,
            'expires_at' => Carbon::now()->addDays(14),
        ]);

        return response()->json(['message' => 'Token refreshed'])
            ->cookie('access_token', $newAccessToken, 15, null, null, true, true, false, 'strict')
            ->cookie('refresh_token', $newRefreshToken, 14 * 24 * 60, null, null, true, true, false, 'strict');
    }
      

    // Logout
    // public function logout(Request $request)
    // {
    //     $request->user()->tokens()->delete();

    //     return response()->json(['message' => 'Logged out successfully']);
    // }

    public function logout(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if ($refreshToken) {
            RefreshToken::where('token', $refreshToken)->delete();
        }

        return response()->json(['message' => 'Logged out'])
            ->cookie('access_token', '', -1)
            ->cookie('refresh_token', '', -1);
    }

    // Get authenticated user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }



public function register(Request $request)
{
    // Set default password
    $default_password = strtoupper(Str::random(2)) . mt_rand(1000000000, 9999999999);

    // Create user
    $user = User::create([
        'firstName' => $request->firstName,
        'lastName' => $request->lastName,
        'phoneNumber' => $request->phoneNumber,
        'email' => $request->email,
        'password' => Hash::make($default_password),
        'role' => $request->role,
    ]);

    Log::info('User created:', ['email' => $user->email]);

    // Send email
    try {
        Mail::to($user->email)->send(new WelcomeEmail($user->firstName, $user->lastName, $user->email, $default_password));
        Log::info('Email sent successfully to ' . $user->email);
    } catch (\Exception $e) {
        Log::error('Email sending failed: ' . $e->getMessage());
    }

    // Return response
    return response()->json([
        'message' => "User successfully created",
        'password' => $default_password,
    ]);
}


    public function changePassword(Request $request)
{
    // Validate input
    $request->validate([
        'currentPassword' => 'required',
        'newPassword' => 'required|min:6', // 'confirmed' ensures newPassword_confirmation is also sent
    ]);

    $user = Auth::user();

    // Check if the current password matches
    if (!Hash::check($request->currentPassword, $user->password)) {
        return response()->json(['message' => 'Current password is incorrect.'], 422);
    }

    // // Only update the fields if they are provided
    // if ($request->has('email')) {
    //     $user->email = $request->email;
    // }
    // if ($request->has('phoneNumber')) {
    //     $user->phoneNumber = $request->phoneNumber;
    // }
    // if ($request->has('firstName')) {
    //     $user->firstName = $request->firstName;
    // }
    // if ($request->has('lastName')) {
    //     $user->lastName = $request->lastName;
    // }

    // Update the user's password
    $user->password = Hash::make($request->newPassword);
    $user->save();

    return response()->json(['message' => 'Password changed successfully.']);
}



public function updateProfile(Request $request)
{
    // Find the patient by ID
    $user = User::where('email', $request->email)->first();

    
    if (!$user) {
        return response()->json([
            'error' => 'User not found',
        ], 404); // HTTP status code 404: Not Found
    }

    
    $data = $request->all();

    
    $user->update($data);

    
    return response()->json([
        'message' => 'User updated successfully',
        'data' => $user,
    ], 200); // HTTP status code 200: OK
}
    
}

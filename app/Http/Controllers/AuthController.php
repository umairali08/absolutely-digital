<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\registrationRequest;
use App\Models\Interests;
use App\Models\UserInterests;
use App\Models\UserVerifications;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verifyUser', 'Commands']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if (!auth()->user()->hasVerifiedEmail()) {
            return response()->json(['error' => 'Please verify your email first'], 422);
        }
        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegistrationRequest $request)
    {
        $data = $request->all();

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->address = $request->address;
        $user->dob = date('Y-m-d', strtotime($request->dob));
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        //Adding Interests
        foreach ($request->interests as $key => $interest){
            $interest_data[] = [
                'user_id' => $user->id,
                'interest_id' => $interest,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        UserInterests::insert($interest_data);

        $full_name = $data['first_name'].' '.$data['last_name'];

        $verification_code = rand(0, 999);
        DB::table('user_verifications')->insert(['users_id' => $user->id, 'token' => $verification_code]);

        $subject = "Please verify your email address.";
        Mail::send('emails.verify', ['name' => $full_name, 'verification_code' => $verification_code],
            function ($mail) use ($data, $subject, $full_name) {
                $mail->from(env('MAIL_FROM_ADDRESS', 'abc@gmail.com'));
                $mail->to($data['email'], $full_name);
                $mail->subject($subject);
            });

        return response()->json(['success' => true, 'message' => 'Thanks for signing up! Please check your email to complete your registration.']);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    /**
     * API Verify User
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyUser($verification_code)
    {
//        $check = DB::table('user_verifications')->where('token', $verification_code)->first();
        $check = UserVerifications::where('token', $verification_code)->first();
        if (!is_null($check)) {
            $user = User::find($check->users_id);

            if ($user->is_verified == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account already verified..'
                ]);
            }

//            $user->update(['is_verified' => 1, 'email_verified_at' => Carbon::now()]);
            $user->is_verified = 1;
            $user->email_verified_at = Carbon::now();
            $user->save();
            DB::table('user_verifications')->where('token', $verification_code)->delete();

            return response()->json([
                'success' => true,
                'message' => 'You have successfully verified your email address.'
            ]);
        }

        return response()->json(['success' => false, 'error' => "Verification code is invalid."]);
    }

    public function Commands()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        Artisan::call('route:clear');
        //Artisan::call('view:cache');
        return "asdasd";
    }
}

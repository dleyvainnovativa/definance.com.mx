<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\AccountController;
use Illuminate\Http\Request;
use App\Models\User;
// use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\JWT\IdTokenVerifier;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;



class AuthController extends Controller
{
    protected FirebaseAuth $auth;

    public function __construct(FirebaseAuth $auth)
    {
        $this->auth = $auth;
    }

    public function firebaseLogin(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $verifier = IdTokenVerifier::createWithProjectId(
                env("VITE_FIREBASE_PROJECT_ID")
            );

            // ✅ THIS is the correct leeway usage
            $verifiedToken = $verifier->verifyIdTokenWithLeeway(
                $request->token,
                10 // seconds
            );
            // $verifiedToken = $this->auth->verifyIdToken($request->token);
            // $firebaseUid = $verifiedToken->claims()->get('sub');
            $firebaseUid = $verifiedToken->payload()['sub'];



            $firebaseUser = $this->auth->getUser($firebaseUid);

            $user = User::where('firebase_uid', $firebaseUid)
                ->orWhere('email', $firebaseUser->email)
                ->first();

            if (!$user) {
                // New user
                $user = User::create([
                    'firebase_uid' => $firebaseUid,
                    'name' => $firebaseUser->displayName ?? 'Usuario',
                    'email' => $firebaseUser->email,
                ]);
                AccountController::setDefaults($user->id);
            } else {
                // Existing user → ensure firebase_uid is set
                if (!$user->firebase_uid) {
                    $user->update([
                        'firebase_uid' => $firebaseUid,
                    ]);
                }
            }


            session([
                'firebase_uid' => $firebaseUid,
                'user_id' => $user->id,
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}

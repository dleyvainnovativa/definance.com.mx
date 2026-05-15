<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\AccountController;
use App\Mail\AlertMail;
use App\Mail\ForgetMail;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
// use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\JWT\IdTokenVerifier;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Jenssegers\Agent\Agent;


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
            $verifiedToken = $verifier->verifyIdTokenWithLeeway(
                $request->token,
                10 // seconds
            );
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
                if (!$user->firebase_uid) {
                    $user->update([
                        'firebase_uid' => $firebaseUid,
                    ]);
                }
            }
            if ($_ENV['APP_ENV'] === 'production') {
                self::suspiciousLogin($request, $user);
            }

            session([
                'firebase_uid' => $firebaseUid,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
            ]);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    private static function suspiciousLogin(Request $request, User $user)
    {
        $currentIp = $request->ip();
        $currentUserAgent = $request->userAgent();
        $existingDevice = DB::table('login_devices')
            ->where('user_id', $user->id)
            ->where('ip_address', $currentIp)
            ->where('user_agent', $currentUserAgent)
            ->exists();
        if (!$existingDevice) {
            $agent = new Agent();
            $agent->setUserAgent($currentUserAgent);
            $device =
                $agent->platform() . ' - ' .
                $agent->browser();
            Mail::to($user->email)->send(
                new AlertMail(
                    now()->format('d-m-Y H:i:s'),
                    $device,
                    $currentIp
                )
            );
            DB::table('login_devices')->insert([
                'user_id' => $user->id,
                'ip_address' => $currentIp,
                'user_agent' => $currentUserAgent,
                'last_login_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function forget(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            $email = $request->email;
            $user = User::where('email', $email)->first();
            if (!$user) {
                throw new Exception("Correo no asociado a usuario");
            }
            $auth = app('firebase.auth');

            $link = $auth->getPasswordResetLink($email);

            Mail::to($email)->send(
                new ForgetMail($link, $email)
            );

            return response()->json([
                'success' => true,
                'message' => 'Correo enviado, revisa tu bandeja'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}

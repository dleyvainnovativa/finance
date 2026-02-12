<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Auth  as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\JWT\IdTokenVerifier;
use Kreait\Firebase\JWT\Error\IdTokenVerificationFailed;
use Lcobucci\JWT\Token\Plain;

class FirebaseJWTAuth
{

    protected $auth;

    public function __construct(FirebaseAuth $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Authentication token not provided.'], 401);
        }

        try {
            $verifier = IdTokenVerifier::createWithProjectId(
                env("VITE_FIREBASE_PROJECT_ID")
            );
            $verifiedToken = $verifier->verifyIdTokenWithLeeway(
                $token,
                10 // seconds
            );
            $firebaseUid = $verifiedToken->payload()['sub'];
            // $verifiedIdToken = $this->auth->verifyIdToken($token, $checkIfRevoked = true);
            // $firebaseUid = $verifiedIdToken->claims()->get('sub');

            $user = User::where('firebase_uid', $firebaseUid)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 401);
            }
            $request->setUserResolver(fn() => $user);
            $request->attributes->set('firebase_uid', $firebaseUid);
        } catch (RevokedIdToken $e) {
            return response()->json(['message' => 'Token has been revoked.'], 401);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => 'The token is invalid: ' . $e->getMessage()], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not verify token: ' . $e->getMessage()], 401);
        }
        return $next($request);
    }
}

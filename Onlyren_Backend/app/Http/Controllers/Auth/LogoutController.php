<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        Auth::user()->tokens->delete(); // Revoke all tokens
        return response()->json(['message' => 'Logged out successfully']);
    }
}

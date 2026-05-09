<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json(['user' => $user]);
    }
    public function edit(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update($data);
        session([
            'user_name' => $user->name,
        ]);

        return response()->json(['message' => 'Perfil actualizado correctamente']);
    }

    // public function password(Request $request)
    // {
    //     $user = $request->user();
    //     $data = $request->validate([
    //         'current_password' => 'required|string',
    //         'new_password' => 'required|string|min:8|confirmed',
    //     ]);

    //     if (!\Hash::check($data['current_password'], $user->password)) {
    //         return response()->json(['message' => 'La contraseña actual es incorrecta'], 400);
    //     }

    //     $user->update(['password' => \Hash::make($data['new_password'])]);

    //     return response()->json(['message' => 'Contraseña actualizada correctamente']);
    // }
}

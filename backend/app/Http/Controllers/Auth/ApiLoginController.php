<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiLoginController extends Controller
{
    public function login(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Intentar autenticar al usuario
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Si la autenticación es exitosa, devolver la información del usuario
            $user = Auth::user();
            return response()->json([
                'message' => 'Autenticación exitosa',
                'user' => $user,
            ]);
        } else {
            // Si las credenciales son incorrectas
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }
    }
}

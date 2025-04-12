<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request){
        // Validación datos que recibe en rquest
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Enviar solicitud al endpoint de Passport
        $response = Http::asForm()->post(env('APP_URL') . '/oauth/token', [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'username' => $request->email,
            'password' => $request->password,
            'scope' => '',
        ]);

        //si no da error, devuelve devuevle respuesta 
        if ($response->successful()) {
            return response()->json($response->json());
        }

        //si hay error devuelve error
        return response()->json([
            'message' => 'Credenciales inválidas',
        ], 401);

    }
}

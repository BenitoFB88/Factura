<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application and issue a Passport token.
     */
    public function login(Request $request)
    {
        // Validación básica (puedes descomentar si necesitas validar)
        // $request->validate([
        //     'email' => 'required|email',
        //     'password' => 'required',
        // ]);

        // Log de datos recibidos
        Log::info('[LOGIN] Datos recibidos:', $request->all());

        $client = new Client();
        // Verifica si la URL está bien formada
        $url = env('APP_URL') . '/oauth/token';
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return response()->json([
                'message' => 'La URL no es válida',
                'url' => $url
            ], 400);
        }

        Log::info('[LOGIN] URL de solicitud:', ['url' => $url]);


        $formParams = [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'username' => $request->input('email'),
            'password' => $request->input('password'),
            'scope' => '',
        ];

        try {
            // Log de payload
            Log::info('[LOGIN] Enviando solicitud a Passport', [
                'url' => $url,
                'payload' => $formParams
            ]);

            $response = $client->post($url, [
                'form_params' => $formParams
            ]);

            $body = json_decode((string) $response->getBody(), true);

            // Log de respuesta
            Log::info('[LOGIN] Respuesta exitosa:', $body);

            return response()->json($body);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Log para errores 400
            $responseBody = $e->getResponse()->getBody()->getContents();
            Log::error('[LOGIN] Error 400 - Bad Request', [
                'message' => $e->getMessage(),
                'response' => $responseBody,
                'payload' => $formParams
            ]);
            return response()->json([
                'message' => 'Error en la solicitud de token',
                'details' => json_decode($responseBody, true)
            ], 400);

        } catch (\Exception $e) {
            // Log para otros errores
            Log::error('[LOGIN] Error inesperado', [
                'message' => $e->getMessage(),
                'payload' => $formParams
            ]);

            return response()->json([
                'message' => 'Error inesperado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

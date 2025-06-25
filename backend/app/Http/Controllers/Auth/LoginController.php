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

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        \Log::info('Login request data:', $request->all());
        $email = $request->input('email') ?? $request->json('email');
        $password = $request->input('password') ?? $request->json('password');

        $request->merge(['email' => $email, 'password' => $password]);
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        Log::info('[LOGIN] Datos recibidos:', ['email' => $email]);

        $client = new Client();


        $url = config('app.url') . '/oauth/token';
        Log::info('url: '.$url);
        
        $formParams = [
            'grant_type' => 'password',
            'client_id' => config('services.passport.client_id'),
            'client_secret' => config('services.passport.client_secret'),
            'username' => $request->input('email'),
            'password' => $request->input('password'),
            'scope' => '',
        ];

        try {
            Log::info('[LOGIN] Enviando solicitud a Passport', [
                'url' => $url,
                'payload' => $formParams
            ]);

            $response = $client->post($url, [
                'form_params' => $formParams
            ]);

            $body = json_decode((string) $response->getBody(), true);

            Log::info('[LOGIN] Respuesta exitosa:', $body);

            return response()->json($body);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Captura de errores 400
            $responseBody = $e->getResponse()->getBody()->getContents();
            Log::error('[LOGIN] Error 400 - Bad Request', [
                'message' => $e->getMessage(),
                'response' => $responseBody,
                'payload' => $formParams
            ]);

            return response()->json([
                'message' => 'Error en la solicitud de token',
                'details' => json_decode($responseBody, true),
                'response_code' => $e->getResponse()->getStatusCode() // Agregado el código de estado de la respuesta
            ], 400);

        } catch (\Exception $e) {
            // Captura de otros errores
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

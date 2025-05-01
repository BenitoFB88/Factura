<?php 

namespace App\External;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class Icontador
{
    protected $token;

    public function __construct()
    {
        $this->token = $this->login(); // llama al login automáticamente
    }

    protected function login()
    {
        Log::info('Inicia login en Icontador');

        $client = new Client();

        $body = [
            'api_user_key' => env('API_USER_KEY'),
            'nombre_usuario' => env('NOMBRE_USUARIO'),
            'password' => env('PASSWORD_USUARIO'),
            'url_sistema' => env('URL_SISTEMA'),
        ];
        Log::info('body:'. json_encode($body));

        
        try {
            $response = $client->post('https://api.iqamp.cl/login', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($body),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::info('Respuesta completa de login:', $data);

            return $data['token'] ?? null;

        } catch (\Exception $e) {
            Log::error('Error intentando login icontador: ' . $e->getMessage());
            return null;
        }
    }

    public function getToken()
    {
        return $this->token;
    }
}

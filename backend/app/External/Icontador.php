<?php 

namespace App\External;


use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Carbon\Carbon;

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

            $url = env('URL_ICONTADOR').'login';

            $response = $client->post($url, [
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

    public function getCod()
    {//obtiene codigos de analisis 
        Log::info('Inicia obtención de códigos de análisis');

        $client = new Client();

        $body = [
            'identificadores' => [
                'API_KEY_EMPRESA' => env('API_KEY_EMPRESA'),
                'id_empresa_seleccionada' => (int) env('ID_EMPRESA_SALECCIONADA'),
                'id_cuenta' => 40101, //este  valor tiene que existir (no null) pero entrega todos
                'activo' => true
            ]
        ];

        try {
            $url = env('URL_ICONTADOR').'mi_contabilidad/codigos_analisis/listado';

            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getToken(),
                ],
                'body' => json_encode($body),
            ]);

            $respuesta = json_decode($response->getBody()->getContents(), true);
            $timestamp = Carbon::now()->timestamp;

            Log::info('Fecha de conexión (epoch): ' . $timestamp);
            Log::info('Respuesta de códigos de análisis:', $respuesta);
            
            //Retorno fecha en epoch y la respuesta
            return [
                'fecha_epoch' => $timestamp,
                'data' => $respuesta,
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo códigos de análisis: ' . $e->getMessage());
            return [
                'error' => true,
                'mensaje' => $e->getMessage(),
                'fecha_epoch' => Carbon::now()->timestamp,
            ];
        }
    }

    function public ActualizarCOD(array $codigos)
    {

    }
}

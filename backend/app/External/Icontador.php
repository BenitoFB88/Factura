<?php

namespace App\External;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
        Log::info('body:' . json_encode($body));


        try {

            $url = env('URL_ICONTADOR') . 'login';

            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($body),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            //Log::info('Respuesta completa de login:', $data);

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
            $url = env('URL_ICONTADOR') . 'mi_contabilidad/codigos_analisis/listado';
            Log::info('Url utilizada: ' . $url);

            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getToken(),
                ],
                'body' => json_encode($body),
            ]);

            $respuesta = json_decode($response->getBody()->getContents(), true);
            $timestamp = Carbon::now()->timestamp;

            //Log::info('Fecha de conexión (epoch): ' . $timestamp);
            //Log::info('Respuesta de códigos de análisis:', $respuesta);

            //Retorno fecha en epoch y la respuesta
            return [
                'fecha_epoch' => $timestamp,
                'data_codigo' => $respuesta,
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo códigos de análisis: ' . $e->getMessage());
            return [
                'error' => true,
                'mensaje' => $e->getMessage(),
                'fecha_epoch_error' => Carbon::now()->timestamp,
            ];
        }
    }

    public function getCuenta()
    {
        //obtiene cuentas maestras
        Log::info('1) Inicia obtención de Cuentas');

        $client = new Client();

        $body = [
            'identificadores' => [
                'API_KEY_EMPRESA' => env('API_KEY_EMPRESA'),
                'id_empresa_seleccionada' => (int) env('ID_EMPRESA_SALECCIONADA'),
            ]
        ];

        try {
            $url = env('URL_ICONTADOR') . 'mi_contabilidad/plan_cuentas/listado';

            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getToken(),
                ],
                'body' => json_encode($body),
            ]);

            $respuesta = json_decode($response->getBody()->getContents(), true);
            $timestamp = Carbon::now()->timestamp;

            //Log::info('Fecha de conexión (epoch): ' . $timestamp);
            Log::info("1.1) Respuesta obtener cuentas:\n" . json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            //Retorno fecha en epoch y la respuesta
            return [
                'fecha_epoch' => $timestamp,
                'data_cuenta' => $respuesta,
            ];

        } catch (\Exception $e) {
            Log::error('Error obteniendo Cuentas Maestras: ' . $e->getMessage());
            return [
                'error' => true,
                'mensaje' => $e->getMessage(),
                'fecha_epoch' => Carbon::now()->timestamp,
            ];
        }
    }

    public function extraerCuentas($estructura)
    {//La funcion modifica la función de getcuenta para devolver solo la cuenta y el nombre.

        $resultado = [];
        $cuentasUnicas = [];

        Log::info('Iniciando recorrido de estructura de cuentas...');

        $recorrer = function ($nodo, $nivel = 0) use (&$recorrer, &$resultado, &$cuentasUnicas) {
            if (is_array($nodo)) {
                foreach ($nodo as $key => $item) {

                    if (isset($item['cuentas'])) {
                        $recorrer($item['cuentas'], $nivel + 1);
                    }

                    if (isset($item['codigo_completo']) && isset($item['nombre'])) {
                        $codigo = (string) $item['codigo_completo'];

                        if (!isset($cuentasUnicas[$codigo])) {
                            $cuentasUnicas[$codigo] = true; // lo marcamos como visto

                            $resultado[] = [
                                'codigo_completo' => $codigo,
                                'nombre' => $item['nombre'],
                            ];
                        }
                    }

                    if (is_array($item)) {
                        $recorrer($item, $nivel + 1);
                    }
                }
            }
        };

        $recorrer($estructura);

        Log::info('✔ Total cuentas únicas extraídas: ' . count($resultado));
        return $resultado;
    }

    public function separadorCuentaCodigo(array $codigos)
    {
        if (!isset($codigos['data_codigo']['respuesta']['data'])) {
            Log::warning('La estructura de datos no es válida');
            return;
        }

        $resultado = [];

        foreach ($codigos['data_codigo']['respuesta']['data'] as $item) {
            if (!isset($item['codigo']) || !isset($item['nombre'])) {
                continue; // salta si falta algo
            }

            $codigoCompleto = trim($item['codigo']);
            $nombre = trim($item['nombre']);

            if (strlen($codigoCompleto) < 5) {
                continue;
            }

            $prefijo = substr($codigoCompleto, 0, 5);

            // Guardar en array agrupado por prefijo
            $resultado[] = [
                'codigo_5digitos' => $prefijo,
                'nombre' => $nombre,
                'codigo_completo' => $codigoCompleto,
            ];

        }
        //Log::info('Codigos agrupados por prefijo de 5 dígitos:', $resultado);
        return $resultado;
    }

    public function actualizararCOD($codigos)
    {
        Log::info('===============================================');
        Log::info('Iniciando actualización de códigos de análisis');

        // 1. Obtener códigos existentes desde BBDD
        $codigosBBDD = DB::table('iecodanalises')->select('id_iempresario')->get()->pluck('id_iempresario')->toArray();

        // 2. Obtener cuentas para vincular con id_iecuentas
        $cuentas = DB::table('iecuentas')->select('id', 'id_iempresario')->get()->keyBy('id_iempresario');

        $nuevosCodigos = [];

        foreach ($codigos as $codigo) {
            $codigoCompleto = $codigo['codigo_completo'];
            $nombre = $codigo['nombre'];
            $codigo5 = $codigo['codigo_5digitos'];

            if (strpos($codigoCompleto, '-') !== false || strlen($codigoCompleto) > 8) {
                continue;
            }

            // 3. Verificar si ya existe en la tabla
            if (in_array($codigoCompleto, $codigosBBDD)) {
                Log::debug("✅ Ya existe código: {$codigoCompleto} - {$nombre}");
                continue;
            }

            // 4. Verificar si existe el código_5digitos como id_iempresario en iecuentas
            if (!isset($cuentas[$codigo5])) {
                Log::warning("⚠️ Cuenta no encontrada para prefijo {$codigo5} al procesar {$codigoCompleto} - {$nombre}");
                continue;
            }

            $id_iecuentas = $cuentas[$codigo5]->id;

            Log::info("🆕 Nuevo código agregado: {$codigoCompleto} - {$nombre}");

            $nuevosCodigos[] = [
                'id_iempresario' => $codigoCompleto,
                'nombre' => $nombre,
                'id_iecuentas' => $id_iecuentas,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $cantidadNuevoCOD = count($nuevosCodigos);
        // 5. Insertar todos juntos si hay nuevos
        if (!empty($nuevosCodigos)) {
            DB::table('iecodanalises')->insert($nuevosCodigos);
            Log::info('🟢 Códigos insertados correctamente: ' . count($nuevosCodigos));

        } else {
            Log::info('ℹ️ No hay códigos nuevos para insertar.');
        }
        return $cantidadNuevoCOD;
    }

    public function actualizarCuentas($cuentas)
    {
        //1. Obtengo las cuentas de bbdd
        $cuentasBBDD = DB::table('iecuentas')->select('id', 'id_iempresario', 'nombre')->get();

        // 2. Log del total y algunas muestras
        Log::info('Total cuentas en BBDD: ' . $cuentasBBDD->count());
        Log::debug('Muestra de 5 cuentas BBDD:', $cuentasBBDD->take(5)->toArray());

        $cuentasBBDDIndexed = $cuentasBBDD->keyBy('id_iempresario');

        // 3. Convertir a array indexado por id_iempresario
        $cuentasBBDDIndexed = $cuentasBBDD->keyBy('id_iempresario');

        // 4. Comparar con lo que vino de la API
        foreach ($cuentas as $cuentaAPI) {
            $codigo = $cuentaAPI['codigo_completo'];
            $nombre = $cuentaAPI['nombre'];

            if (!$cuentasBBDDIndexed->has($codigo)) {
                Log::info("🆕 Cuenta nueva encontrada: {$codigo} - {$nombre}");

                //Si deseas insertarla, usa los nombres correctos de columnas en tu tabla:
                DB::table('iecuentas')->insert([
                    'id_iempresario' => $codigo,
                    'nombre' => $nombre,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::info("agregada");

            } else {
                Log::debug("✅ Ya existe en BBDD: {$codigo} - {$nombre}");
            }
        }
    }


}

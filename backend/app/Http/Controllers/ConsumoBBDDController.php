<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsumoBBDDController extends Controller
{
    public function prueba(){
      try{
        $users = DB::table('users')
                    ->get();  

        return response()->json($users);

      }catch( \Exception $e){

        Log::error('Error: '.$e);

        return response()->json([
            'error'=>'Ocurrio un error'
        ],500);
      }
    }
}

<?php

/**
 * Description of JwtAuth
 *
 * @author kevin
 */

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth {

    public $key;

    public function __construct() {
        $this->key = 'esta_es_la_clave_del_token-777';
    }

    public function signup($email, $password, $getToken = null) {
        
        $user = User::where([
                    'email' => $email,
                    'password' => $password
                ])->first();
        //comprobamos si los datos son correctos
        $signup = false;
        if (is_object($user)) {
            $signup = true;
        }
        //Generamos el token con los datos del usuario identificado
        if ($signup) {
            $token = array(
                'sub'       => $user->id,
                'email'     => $user->email,
                'name'      => $user->name,
                'surname'   => $user->surname,
                'description' => $user->description,
                'image'     => $user->image, 
                'iat'       => time(),
                'exp'       => time()+(1000) //time() + (7 * 24 * 60 * 60)
            );
            
            $jwt = JWT::encode($token, $this->key, 'HS256'); //algoritmo de codificacion HS256
            //decodificamos el token
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);//vamos a tener un objeto con la informacion del usuario
            
            // devolvemos los datos decodificados(token)
            if (is_null($getToken)) {
                $data = $jwt;
            } else {
                $data = $decoded;
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Login incorrecto'
            );
        }
        return $data;
    }
    public function checkToken($jwt, $getIdentity = false) {
        $auth = false;
        try {
            $jwt = str_replace('"','', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e){
            $auth = false;
        }
        
        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        }else{
            $auth = false;
        }
        
        if ($getIdentity) {
            return $decoded;
        }
        
        return $auth;
    }

}

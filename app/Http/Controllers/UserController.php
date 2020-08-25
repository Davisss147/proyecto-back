<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller {

    public function register(Request $request) {
        //recojemos los datos que nos llega
        $json = $request->input('json', null);
        $params = json_decode($json); // nos crea un objeto
        $params_array = json_decode($json, true); // nos crea un array

        if (!empty($params) && !empty($params_array)) {
            $params_array = array_map('trim', $params_array);
            $validate = \Validator::make($params_array, [
                        'name' => 'required | alpha', //requerido y solo abcedario
                        'surname' => 'required | alpha',
                        'email' => 'required | email | unique:users', // requerido, formato email y verificamos si ya existe
                        'password' => 'required'
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                //encriptamos el password
                $pwd = hash('sha256', $params->password);

                //creamos el nuevo usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROL_USER';
                $user->save();
                //devolvemos un mensaje si se ha creado correctamente
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario  se ha creado correctamente'
                );
            }
        } else {
            //mensaje de error en el registro
            $data = array(
                'status' => 'error',
                'code' => 406,
                'message' => 'Los datos enviados no son correctos'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {
        $jwtAuth = new \JwtAuth();

        //recibimos los datos post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //validamos los datos
        $validate = \Validator::make($params_array, [
                    'email' => 'required | email',
                    'password' => 'required'
        ]);
        //
        if ($validate->fails()) {
            $signup = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no se ha podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            $pwd = hash('sha256', $params->password);
            $signup = $jwtAuth->signup($params->email, $pwd);

            if (!empty($params->gettoken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }


        return response()->json($signup, 200);
    }

    // actualizar los datos del usuario
    public function update(Request $request) {
        //recogemos el token por el header
        $token = $request->header('Authorization');
        //va a venir el token en cada una de nuestras peticiones
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //recojemos los datos del post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);


        if ($checkToken && !empty($params_array)) {


            //sacar usuario que ingreso a la aplicacion
            $user = $jwtAuth->checkToken($token, true);

            //validamos los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required | alpha', //requerido y solo abcedario
                        'surname' => 'required | alpha',
                        'email' => 'required | email | unique:users,' . $user->sub // requerido, formato email y verificamos si ya existe
            ]);
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['create_at']);
            unset($params_array['remember_token']);

            //actualizar el usuario
            $user_update = User::where('id', $user->sub)->update($params_array);
            //Devolvemos la data
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Usuario Actualizado',
                'user' => $user,
                'changes' => $params_array
            );
        } else {
            $data = array(
                'code' => 406,
                'status' => 'error',
                'message' => 'Primero Inicie Sesion!!'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function uploadI(Request $request) {

        $image = $request->file('file0');

        //validar que solo se suban imagenes
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //subida de imagenes en el servidor
        if (!$image || $validate->fails()) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            );
        } else {
            $image_name = time() .$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name,
                'message' => 'Imagen subida con exito'
            );
        }
        return response()->json($data, $data['code']);
    }

    //obtener la imagen
    public function getImage($filename) {
        $isset = \Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 402,
                'status' => 'error',
                'message' => 'No existe la imagen'
            );
        }
        return response()->json($data, $data['code']);
    }

    // OBTENER UN USUARIO
    public function userId($id) {
        $user = User::find($id);

        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'No existe el usuario'
            );
        }
        return response()->json($data, $data['code']);
    }
    //sacar todos los usuarios
    public function usert() {
        $users = User::all();

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'users' => $users
        ]);
    }

}

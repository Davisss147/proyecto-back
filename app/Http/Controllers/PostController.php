<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct() {
        $this->middleware('api.auth', ['except' => [
                'index',
                'show',
                'getImage',
                'getPostByCategory',
                'getPostByUser'
        ]]);
    }

    public function index() {
        //listar todos los post
        $posts = Post::all()->load('category');

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'posts' => $posts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
//
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //guardar un psot
        $json = $request->input('json', null); //recojemos los datos que nos llegan
        $params_array = json_decode($json, true); //lo decodificamos y comvertimos en array
        $params = json_decode($json);

        if (!empty($params_array)) {

            //verificamos que el usuario esta logeado en el sistema
            $user = $this->getIdentity($request);
            //validamos la informacion
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
                        'image' => 'required'
            ]);
            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el post faltan datos'
                ];
            } else {
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Se han guardado el post correctamente',
                    'post' => $post
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ningun dato'
            ];
        }
        return response()->json($data, $data['code']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //ver solo un post por id
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'posts' => $post
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No existe ese post'
            ];
        }
        return response()->json($data, $data['code']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
//
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {

        $json = $request->input('json', null); //recojemos los datos que nos llegan
        $params_array = json_decode($json, true); //lo decodificamos y comvertimos en array

        if (!empty($params_array)) {

            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha actualizado el post',
                    'post' => $post
                ];
            } else {
                //quitamos lo que no queremos actualizar
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['create_at']);
                //verificamos que el usuario esta logeado en el sistema
                $user = $this->getIdentity($request);
                //Buscamos el registro ha actualizar
                $post = Post::where('id', $id)
                        ->where('user_id', $user->sub)
                        ->first();

                if (!empty($post) && is_object($post)) {
                    //Actualizamos el registro en concreto
                    $post->update($params_array);

                    $data = [
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Se ha actualizado el post',
                        'Post' => $post
                    ];
                } else {
                    $data = [
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'Datos enviados incorrectamente',
                    ];
                }
                return response()->json($data, $data['code']);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request) {

        //verificamos que el usuario esta logeado en el sistema
        $user = $this->getIdentity($request);
        //Buscamos el registro ha eliminar
        $post = Post::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        //
        if (!empty($post)) {

            $post->delete();

            $data = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Se a borrado el post exitosamente',
                'post' => $post
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'El post no existe',
            ];
        }
        return response()->json($data, $data['code']);
    }

    private function getIdentity($request) {
        //verificamos que el usuario esta logeado en el sistema
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    //Subir una imagen 
    public function uploadI(Request $request) {
        $image = $request->file('file0');

        $validate = \Validator::make($request->all(), [
                    'file0' => 'required | image | mimes:jpg,jpeg,png,gif'
        ]);
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName(); // time() para que las imagenes sean unicas

            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'message' => 'La imagen se ha subido correctamente',
                'image' => $image_name
            ];
        }
        return response()->json($data, $data['code']);
    }

    //obtener la imagen subida
    public function getImage($filename) {
        $isset = \Storage::disk('images')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('images')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No existe la imagen'
            );
        }
        return response()->json($data, $data['code']);
    }

    //Conseguir un post por categoria
    public function getPostByCategory($id) {
        $post = Post::where('category_id', $id)->get();

        return response()->json([
                    'status' => 'success',
                    'user' => $post
                        ], 200);
    }

    //Conseguir un post por usuario
    public function getPostByUser($id) {
        $post = Post::where('user_id', $id)->get();

        return response()->json([
                    'status' => 'success',
                    'user' => $post
                        ], 200);
    }

}

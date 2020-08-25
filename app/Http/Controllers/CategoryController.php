<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index() {
        //nos muestra todas las categorias
        $categories = Category::all();

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'categories' => $categories
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
        //creamos una nueva categoria
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //validamos el campo
            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);
            
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'La categoria no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => $category
                ];
            }
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha ingresado ninguna categoria'
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
        //mostramos una categoria por id
        $category = Category::find($id);

        if (is_object($category)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'category' => $category
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La categoria no existe'
            );
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
        //actualizar una categoria
        $json = $request->input('json', null);//recojemos los datos que nos llegan
        $params_array = json_decode($json, true); //lo decodificamos y comvertimos en array
        
        if(!empty($params_array)){
            //validamos los datos que nos llegan
            $validate = \Validator::make($params_array, [
                'name'  => 'required'
            ]);
            //quitamos los campos que no deseamos actualizar
            unset($params_array['id']);
            unset($params_array['create_at']);
            //actualizamos el registro
            $category = Category::where('id', $id)->update($params_array);
            $data = array(
                'code' => 200,
                'status' => 'success',
                'categoria' => $params_array
            );
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha ingresado ninguna categoria'
            );
        }
        return response()->json($data, $data['code']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

}

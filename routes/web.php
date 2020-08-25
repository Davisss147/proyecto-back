<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return view('welcome');
});
//prueba de rutas
Route::get('/prueba', 'PostController@testOrm');

//Rutas de usuario
Route::post('/api/register', 'UserController@register');
Route::post('/api/login', 'UserController@login');
Route::put('/api/user/update', 'UserController@update');
Route::post('/api/user/upload', 'UserController@uploadI')->middleware(ApiAuthMiddleware::class);

Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
Route::get('/api/user/userId/{id}', 'UserController@userId');

//Rutas de Categorias
Route::resource('/api/category', 'CategoryController');

//Rutas de Post
Route::resource('/api/post', 'PostController');
Route::post('/api/post/upload', 'PostController@uploadI');
Route::get('/api/post/image/{filename}', 'PostController@getImage');
Route::get('/api/post/category/{id}', 'PostController@getPostByCategory');
Route::get('/api/post/user/{id}', 'PostController@getPostByUser');
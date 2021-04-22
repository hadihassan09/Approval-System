<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Support\Facades\Auth;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/login', 'AuthController@login');
$router->post('/register', 'AuthController@register');
$router->get('/testToken', ['middleware' => 'auth', function (){
    return Auth::user();
}]);

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/testToken', function (){
        return Auth::user();
    });
    $router->get('/logout', 'AuthController@logout');
});

$router->group(['middleware' => 'auth', 'prefix'=> 'api/'], function () use ($router) {
    $router->get('posts', 'PostController@index');
    $router->get('posts/{id:[\d]+}', 'PostController@show');
    $router->post('posts', 'PostController@store');
    $router->put('posts/{id}', 'PostController@update');
    $router->delete('posts/{id}', 'PostController@destroy');

    $router->get('posts/unapproved', 'PostController@unapprovedPosts');
    $router->get('posts/{id:[\d]+}/approve', 'PostController@approvePost');
});

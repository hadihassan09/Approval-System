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
    $router->get('data', 'DataController@index');
    $router->get('data/{id:[\d]+}', 'DataController@show');
    $router->post('data', 'DataController@store');
    $router->put('data/{id}', 'DataController@update');
    $router->delete('data/{id}', 'DataController@destroy');

    $router->get('data/unapproved', 'DataController@unapprovedData');
    $router->get('data/{id:[\d]+}/approve', 'DataController@approveData');
});

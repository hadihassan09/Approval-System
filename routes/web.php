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

    // News
    $router->get('news', 'NewsController@index');
    $router->post('news', 'NewsController@store');
    $router->put('news/{id}', 'NewsController@update');
    $router->delete('news/{id}', 'NewsController@destroy');

    // Countries
    $router->get('countries', 'CountryController@index');
    $router->post('countries', 'CountryController@store');
    $router->put('countries/{id}', 'CountryController@update');
    $router->delete('countries/{id}', 'CountryController@destroy');

    // Languages
    $router->get('languages', 'LanguageController@index');
    $router->post('languages', 'LanguageController@store');
    $router->put('languages/{id}', 'LanguageController@update');
    $router->delete('languages/{id}', 'LanguageController@destroy');

    // Regions
    $router->get('regions', 'RegionController@index');
    $router->post('regions', 'RegionController@store');
    $router->put('regions/{id}', 'RegionController@update');
    $router->delete('regions/{id}', 'RegionController@destroy');

    $router->get('requests', 'AdminController@unapprovedRequests');
    $router->get('requests/{id:[\d]+}/approve', 'AdminController@approveRequest');
});

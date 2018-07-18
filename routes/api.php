<?php
/** @var Router $router */

use Laravel\Lumen\Routing\Router;

/* Public Routes */
$router->get('/', function () {

//    return \App\User::all();

    \Illuminate\Support\Facades\Mail::to(\App\User::first())->send(new \App\Mail\Welcome(\App\User::first()));

    return response()->json(['message' => 'Welcome to Lumen API Starter']);
});

/* Auth Routes */
$router->group(['prefix' => 'auth', 'as' => 'auth'], function (Router $router) {

    /* Defaults */
    $router->post('/register', [
        'as' => 'register',
        'uses' => 'AuthController@register',
    ]);
    $router->post('/login', [
        'as' => 'login',
        'uses' => 'AuthController@login',
    ]);
    $router->get('/verify/{token}', [
        'as' => 'verify',
        'uses' => 'AuthController@verify'
    ]);

    $router->get('/refresh', [
        'as' => 'refresh',
        'uses' => 'AuthController@refresh'
    ]);

    /* Password Reset */
    $router->post('/password/forgot', [
        'as' => 'password.forgot',
        'uses' => 'AuthController@passwordForgot'
    ]);
    $router->post('/password/reset/{token}', [
        'as' => 'password.reset',
        'uses' => 'AuthController@passwordReset'
    ]);

    /* Protected User Endpoint */
    $router->get('/user', [
        'uses' => 'AuthController@getUser',
        'as' => 'user',
        'middleware' => 'auth'
    ]);
});

/* Protected Routes */
$router->group(['middleware' => 'auth'], function (Router $router) {

    //

});

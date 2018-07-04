<?php
/** @var Router $router */

use Laravel\Lumen\Routing\Router;

/* Public Routes */
$router->get('/', function () {
    return response()->json(['message' => 'Welcome to Lumen API Starter']);
});
$router->post('/auth/register', [
    'as' => 'auth.register',
    'uses' => 'AuthController@register',
]);
$router->post('/auth/login', [
    'as' => 'auth.login',
    'uses' => 'AuthController@login',
]);
$router->get('/auth/refresh', [
    'as' => 'auth.refresh',
    'uses' => 'AuthController@refresh'
]);

/* Protected Routes */
$router->group([
    'middleware' => 'auth',
], function (Router $router) {

    /* User Endpoints */
    $router->get('/auth/user', [
        'uses' => 'AuthController@getUser',
        'as' => 'auth.user'
    ]);

    /* Article Endpoints */
    $router->get('/articles', [
        'uses' => 'ArticlesController@index',
        'as' => 'articles.index'
    ]);
    $router->get('/articles/{id}', [
        'uses' => 'ArticlesController@find',
        'as' => 'articles.find'
    ]);
    $router->post('/articles', [
        'uses' => 'ArticlesController@create',
        'as' => 'articles.create'
    ]);
    $router->put('/articles/{id}', [
        'uses' => 'ArticlesController@update',
        'as' => 'articles.update'
    ]);
    $router->delete('/articles/{id}', [
        'uses' => 'ArticlesController@delete',
        'as' => 'articles.delete'
    ]);
});

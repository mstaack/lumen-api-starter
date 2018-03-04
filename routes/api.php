<?php
/** @var Router $router */

use Laravel\Lumen\Routing\Router;

/* Public Routes */
$router->post('/auth/login', [
    'as' => 'auth.login',
    'uses' => 'AuthController@login',
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
    $router->delete('/auth/logout', [
        'uses' => 'AuthController@logout',
        'as' => 'auth.logout'
    ]);

    /* Article Endpoints */
    $router->get('/articles', [
        'uses' => 'ArticlesController@index',
        'as' => 'articles.index'
    ]);
    $router->post('/articles', [
        'uses' => 'ArticlesController@create',
        'as' => 'articles.create'
    ]);
    $router->get('/articles/{id}', [
        'uses' => 'ArticlesController@find',
        'as' => 'articles.find'
    ]);
    $router->delete('/articles/{id}', [
        'uses' => 'ArticlesController@delete',
        'as' => 'articles.delete'
    ]);
});

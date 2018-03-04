<?php
/** @var Router $router */

use Laravel\Lumen\Routing\Router;

$router->post('/auth/login', [
    'as' => 'api.auth.login',
    'uses' => 'AuthController@postLogin',
]);

$router->group([
    'middleware' => 'auth:api',
], function (Router $router) {
});

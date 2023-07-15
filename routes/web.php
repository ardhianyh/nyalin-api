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

$router->get('/', function () use ($router) {
    return 'ok its works';
});

$router->group(['prefix' => 'v1'], function () use ($router) {
    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@register');
    $router->get('/logout', 'AuthController@logout');

    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('/type', 'TypeController@index');
        $router->get('/type/{id}', 'TypeController@find');
        $router->post('/type', 'TypeController@create');
        $router->put('/type/{id}', 'TypeController@update');
        $router->delete('/type/{id}', 'TypeController@delete');

        $router->get('/user', 'UserController@index');
        $router->get('/user/{id}', 'UserController@find');
        $router->put('/user/{id}', 'UserController@update');
        $router->delete('/user/{id}', 'UserController@delete');
        $router->put('/user/password/{id}', 'UserController@changePassword');

        $router->get('/category', 'CategoryController@index');
        $router->get('/category/type/{id}', 'CategoryController@findByType');
        $router->get('/category/{id}', 'CategoryController@find');
        $router->post('/category', 'CategoryController@create');
        $router->put('/category/{id}', 'CategoryController@update');
        $router->delete('/category/{id}', 'CategoryController@delete');

        $router->get('/transaction', 'TransactionController@index');
        $router->get('/transaction/filter', 'TransactionController@filter');
        $router->get('/transaction/user', 'TransactionController@findByUserId');
        $router->get('/transaction/category/{id}', 'TransactionController@findByCategory');
        $router->get('/transaction/{id}', 'TransactionController@find');
        $router->post('/transaction', 'TransactionController@create');
        $router->put('/transaction/{id}', 'TransactionController@update');
        $router->delete('/transaction/{id}', 'TransactionController@delete');

        $router->get('/note', 'NoteController@index');
        $router->get('/note/user', 'NoteController@findByUserId');
        $router->get('/note/{id}', 'NoteController@find');
        $router->get('/note/keyword/{keyword}', 'NoteController@findByKeyword');
        $router->get('/note/tag/{tag}', 'NoteController@findByTag');
        $router->post('/note', 'NoteController@create');
        $router->put('/note/{id}', 'NoteController@update');
        $router->delete('/note/{id}', 'NoteController@delete');

        $router->get('/mobile/homepage', 'CustomController@homepage');
        $router->get('/mobile/transaction', 'CustomController@transactionpage');
    });
});

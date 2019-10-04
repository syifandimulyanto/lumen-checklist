<?php

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
    return $router->app->version();
});

// Auth
$router->post('login', 'AuthController@login');

$router->group(['middleware' => 'auth'], function() use ($router) {

    //checklist/
    $router->group(['prefix' => 'checklists'], function () use ($router) {

        $router->group(['prefix' => 'templates'], function () use ($router) {
            // checklist/templates
            // Templates data
            $router->get('/', 'TaskController@index');
            $router->get('/{templateId}', 'TaskController@show');
            $router->post('/', 'TaskController@store');
            $router->patch('/{templateId}', 'TaskController@update');
            $router->delete('/{templateId}', 'TaskController@destroy');
        });

        // Items data
        $router->get('/items', 'ItemController@index');
        $router->post('/{checklistId}/items', 'ItemController@store');
        $router->get('/{checklistId}/items/{itemId}', 'ItemController@show');
        $router->patch('/{checklistId}/items/{itemId}', 'ItemController@update');
        $router->delete('/{checklistId}/items/{itemId}', 'ItemController@destroy');

        $router->get('/{checklistId}/items', 'ItemController@indexChecklist');

        $router->get('/items/summaries', 'ItemController@summaries');
        $router->post('/complete', 'ItemController@complete');
        $router->post('/incomplete', 'ItemController@incomplete');

        $router->post('/{checklistId}/items/_bulk', 'ItemController@bulkUpdate');

        // Checklist data
        $router->get('/', 'ChecklistController@index');
        $router->post('/', 'ChecklistController@store');
        $router->get('/{checklistId}', 'ChecklistController@show');
        $router->patch('/{checklistId}', 'ChecklistController@update');
        $router->delete('/{checklistId}', 'ChecklistController@destroy');

    });
});





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

    // Templates data
    $router->get('checklists/templates', 'TaskController@index');
    $router->get('checklists/templates/{templateId}', 'TaskController@show');
    $router->post('checklists/templates', 'TaskController@store');
    $router->patch('checklists/templates/{templateId}', 'TaskController@update');
    $router->delete('checklists/templates/{templateId}', 'TaskController@destroy');

    // Items data
    $router->get('checklists/items', 'ItemController@index');
    $router->post('checklists/{checklistId}/items', 'ItemController@store');
    $router->get('checklists/{checklistId}/items/{itemId}', 'ItemController@show');
    $router->patch('checklists/{checklistId}/items/{itemId}', 'ItemController@update');
    $router->delete('checklists/{checklistId}/items/{itemId}', 'ItemController@destroy');

    $router->get('checklists/{checklistId}/items', 'ItemController@indexChecklist');

    $router->get('checklists/items/summaries', 'ItemController@summaries');
    $router->post('checklists/complete', 'ItemController@complete');
    $router->post('checklists/incomplete', 'ItemController@incomplete');

    $router->post('checklists/{checklistId}/items/_bulk', 'ItemController@bulkUpdate');

    // Checklist data
    $router->get('checklists', 'ChecklistController@index');
    $router->post('checklists', 'ChecklistController@store');
    $router->get('checklists/{checklistId}', 'ChecklistController@show');
    $router->patch('checklists/{checklistId}', 'ChecklistController@update');
    $router->delete('checklists/{checklistId}', 'ChecklistController@destroy');

});





<?php

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for the module.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
	$api->group(['prefix'=>'category', 'middleware' => 'cors'], function($api){
		$api->get('/', 'Modules\Category\Http\Controllers\CategoryController@index');
	    $api->post('/', 'Modules\Category\Http\Controllers\CategoryController@store');
	    $api->delete('/', 'Modules\Category\Http\Controllers\CategoryController@destroy');
	    $api->get('/show', 'Modules\Category\Http\Controllers\CategoryController@show');
	    $api->patch('/', 'Modules\Category\Http\Controllers\CategoryController@update');
	});
});

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
	$api->group(['prefix'=>'post', 'middleware' => 'cors'], function($api){
		$api->get('/', 'Modules\Post\Http\Controllers\PostController@index');
	    $api->post('/', 'Modules\Post\Http\Controllers\PostController@store');
	    $api->delete('/', 'Modules\Post\Http\Controllers\PostController@destroy');
	    $api->get('/show', 'Modules\Post\Http\Controllers\PostController@show');
	    $api->patch('/', 'Modules\Post\Http\Controllers\PostController@update');
	    $api->patch('/status', 'Modules\Post\Http\Controllers\PostController@updateStatus');
	    $api->post('/featured', 'Modules\Post\Http\Controllers\PostController@setFeatured');
	    $api->post('/featured/unset', 'Modules\Post\Http\Controllers\PostController@unsetFeatured');
	    $api->get('/featured', 'Modules\Post\Http\Controllers\PostController@getFeatured');
	});
});

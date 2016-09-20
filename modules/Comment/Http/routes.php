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
	$api->group(['prefix'=>'comment'], function($api){
		$api->get('/', 'Modules\Comment\Http\Controllers\CommentController@index');
	    $api->post('/', 'Modules\Comment\Http\Controllers\CommentController@store');
	    $api->delete('/', 'Modules\Comment\Http\Controllers\CommentController@destroy');
	    $api->get('/show', 'Modules\Comment\Http\Controllers\CommentController@show');
	    $api->patch('/', 'Modules\Comment\Http\Controllers\CommentController@update');
	});
});


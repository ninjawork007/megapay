<?php

Route::group(['namespace' => 'Infoamin\Installer\Http\Controllers'], function()
{
	Route::get('/', 'WelcomeController@welcome');

	Route::group(['prefix' => 'install', 'middleware' => ['web']], function()
	{
		Route::get('database', 'DatabaseController@create');
		Route::get('requirements','RequirementsController@requirements');
		Route::get('permissions','PermissionsController@checkPermissions');
		Route::match(array('GET','POST'),'verify-envato-purchase-code','PermissionsController@verifyPurchaseCode');
		Route::get('seedmigrate', 'DatabaseController@seedMigrate');
		Route::post('database', 'DatabaseController@store');
		Route::get('register', 'UserController@createUser');
		Route::post('register', 'UserController@storeUser');
		Route::get('finish', 'FinalController@finish');
	});
});
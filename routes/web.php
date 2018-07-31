<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/**
 * Groups of routes that needs authentication to access.
 */
Route::group(['middleware' => 'auth'], function () {

    Auth::routes(); 

    Route::resource('stories', 'StoriesController');

    Route::get('/home', 'StoriesController@index');
    
}); 
/**
 * Groups of routes that does not needs authentication to access.
 */

Route::get('/', function () {
    return view('auth/login');
});

Route::get('/404', function () {
    return view('404');
});


Route::get('login', function () {
    return view('auth/login');
});

// Catch any other route
Route::any('/{any}', function() {
    if(Auth::guest()) {
        // Redirect all unauthed users, visiting any page to login
        return redirect('/login');
    } else {
        // redirect to 404
        return redirect('/404');
    }
})->where('any', '.*');

Route::post('login', [ 'as' => 'login', 'uses' => 'Auth\LoginController@login']);

/*// We've written Auth destinations manually to branch out the login page, which is the only page accecible without auth
    Route::get('/register', function () {
        return view('auth/register');
    });*/



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

    # Story
    Route::resource('stories', 'StoriesController');

    # Characters
    Route::resource('stories.characters', 'CharactersController');

    # Phone numbers
    Route::resource('stories.phone_numbers', 'PhoneNumbersController');

    # Texts
    Route::resource('stories.texts', 'TextsController');
    Route::get('stories/{story}/texts/{phone_number}/edit/{text}', 'TextsController@edit');

    # Photos
    Route::resource('stories.photos', 'PhotosController');

    # Phone log
    Route::resource('stories.phonelogs', 'PhoneLogsController');

    # Module: News
    Route::resource('stories/{story}/modules/news', 'NewsController');

    # Variables
    Route::resource('stories.variables', 'VariablesController');

    Route::get('/home', 'StoriesController@index');

    // AJAX
    Route::post('/toggle-sidebar', 'GeneralAjaxController@ToggleSidebarSession');
    Route::post('/add-news-section', 'GeneralAjaxController@AddNewsSection');
    
}); 
/**
 * Groups of routes that does not needs authentication to access.
 */

Route::get('/', function () {
    if(Auth::guest()) {
        return view('auth/login');
    } else {
        return redirect('/home');
    }
});

Route::get('/404', function () {
    return view('404');
});


Route::get('login', function () {
    if(Auth::guest()) {
        return view('auth/login');
    } else {
        return redirect('/home');
    }
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



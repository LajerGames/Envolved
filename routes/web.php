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

    # Builder
    Route::get('stories/{story}/builder/{tabID}', 'BuilderController@index');
    Route::get('stories/{story}/builder/arch/{archID}', 'BuilderController@show');

    # Builder - arch "crud"
    Route::post('stories/{story}/builder/{tabID}', 'StoryArchesController@store');
    Route::put('stories/{story}/builder/{tabID}', 'StoryArchesController@update');
    Route::delete('stories/{story}/builder/{tabID}', 'StoryArchesController@destroy');

    # Builder - story point "crud"
    // These are hidden away down at the bottom with the rest of the AJAX, most of this is AJAX - There are reasons get off my back!!!

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

    # Settings
    Route::get('stories/{story}/editor_settings/edit', 'SettingsEditorsController@edit');
    Route::put('stories/{story}/editor_settings/edit', 'SettingsEditorsController@update');

    # Backups
    Route::resource('stories/{story}/backup', 'BackupController');
    Route::post('stories/{story}/backup/{backup_id}', 'BackupController@implement');
    # Export
    Route::post('stories/{story}/export', 'ExportController@exportSQLite');
    # Backup
    Route::post('stories/{story}/initiate-backup', 'BackupController@store');
    Route::post('stories/{story}/confirm-backup', 'BackupController@confirm');

    Route::get('/home', 'StoriesController@index');

    // AJAX
    Route::post('/toggle-sidebar', 'GeneralAjaxController@ToggleSidebarSession');
    Route::post('/add-news-section', 'GeneralAjaxController@AddNewsSection');
    Route::post('/add-tab', 'GeneralAjaxController@AddTab');
    Route::post('/prepare-story-modal', 'GeneralAjaxController@PrepareSaveModal');

    // Story points (still AJAX)
    Route::post('/handle-story-point-reference', 'StoryPointsController@HandleReferenceAjax');
    Route::post('/insert-story-point', 'StoryPointsController@InsertStoryPoint');
    Route::post('/update-story-point-leads-to', 'StoryPointsController@UpdateStoryPointLeadsTo');
    Route::post('/update-story-point-specialized-input', 'StoryPointsController@RenderStoryPointFormTypeInputsAjax');
    Route::post('/save-story-point-form', 'StoryPointsController@SaveStoryPointForm');
    Route::post('/render-story-point-type-form', 'StoryPointsController@RenderStoryPointTypeForm');
    Route::post('/render-story-point-container', 'StoryPointsController@GetStoryPointAndRenderContainer');
    Route::post('/update-story-point-variable-input', 'StoryPointsController@RenderStoryPointFormTypeInputsTypeAjax');
    Route::post('/update-story-point-variable-refresh-operators', 'StoryPointsController@RenderStoryPointFormTypeInputsChangeVariableOperatorOptionsAjax');
    Route::post('/update-story-point-variable-condition-choose-operator', 'StoryPointsController@RenderStoryPointFormTypeVariableConditionRenderRecordRenderOperatorAjax');
    Route::post('/update-story-point-redirect', 'StoryPointsController@RenderStoryPointFormRedirectTypeAjax');
    Route::post('/get-character-and-storypoint-settings', 'StoryPointsController@GetStoryPointOrCharacterSettingsAjax');
    Route::post('/get-story-points-to-delete-via-story-point-id', 'StoryPointsController@GetStoryPointsToDeleteViaStoryPointID');
    Route::post('/delete-story-points-via-story-point-id', 'StoryPointsController@DeleteStoryPointsViaStoryPointID');

    
    
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



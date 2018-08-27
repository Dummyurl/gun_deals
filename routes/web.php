<?php
$ADMIN_PREFIX = "admin";
Route::model('user', 'App\Models\User');

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

Route::get('/', function (){    
    return view('welcome');
});

Route::get('test', 'TestController@index');

Route::get('clear-cache', function () {
	$exitCode = Artisan::call('cache:clear');
	$exitCode = Artisan::call('view:clear');
	$exitCode = Artisan::call('route:clear');
	$exitCode = Artisan::call('config:clear');
	$exitCode = Artisan::call('debugbar:clear');
	return ["status" => 1, "msg" => "Cache cleared successfully!"];
});

/***************    Admin routes  **********************************/
Route::group(['prefix' => $ADMIN_PREFIX], function(){
    
    Route::get('/', 'admin\AdminLoginController@getLogin')->name("admin_login");
    Route::get('login', 'admin\AdminLoginController@getLogin')->name("admin_login");
    Route::post('login', 'admin\AdminLoginController@postLogin')->name("check_admin_login");
    
    Route::group(['middleware' => 'admin_auth'], function(){
        
        Route::get('logout', 'admin\AdminLoginController@getLogout')->name("admin_logout");

        Route::get('dashboard', 'admin\AdminController@index')->name("admin_dashboard");

        Route::get('change-password', 'admin\AdminController@changePassword')->name("admin_change_password");
        Route::post('change-password', 'admin\AdminController@postChangePassword')->name("admin_update_password");

        Route::get('profile', 'admin\AdminController@editProfile')->name("admin_edit_profile");
        Route::post('profile', 'admin\AdminController@updateProfile')->name("admin_update_profile");
        
	Route::get('user-type-rights', 'admin\AdminController@rights')->name("list-assign-rights");
	Route::post('user-type-rights', 'admin\AdminController@rights')->name("assign-rights");        

	Route::any('modules/data', 'admin\AdminModulesController@data')->name('modules.data');
	Route::resource('modules', 'admin\AdminModulesController');
        
	Route::any('module-pages/data', 'admin\AdminModulePagesController@data')->name('module-pages.data');
	Route::resource('module-pages', 'admin\AdminModulePagesController');

	Route::any('admin-actions/data', 'admin\AdminActionController@data')->name('admin-actions.data');
	Route::resource('admin-actions', 'admin\AdminActionController'); 

	Route::any('admin-userlogs/data', 'admin\AdminUserLogsController@data')->name('admin-userlogs.data');
	Route::resource('admin-userlogs', 'admin\AdminUserLogsController');

	Route::any('user-actions/data', 'admin\UserActionController@data')->name('user-actions.data');
	Route::resource('user-actions', 'admin\UserActionController'); 

	Route::any('countries/data', 'admin\CountriesController@data')->name('countries.data');
	Route::resource('countries', 'admin\CountriesController'); 

	Route::any('states/data', 'admin\StatesController@data')->name('states.data');
	Route::resource('states', 'admin\StatesController'); 
        
	Route::any('cities/data', 'admin\CitiesController@data')->name('cities.data');
	Route::resource('cities', 'admin\CitiesController');        
	Route::any('cities/getstates', 'admin\CitiesController@getstates')->name('getstates');

	Route::any('admin-users/data', 'admin\AdminUserController@data')->name('admin-users.data');
	Route::resource('admin-users', 'admin\AdminUserController'); 

	Route::any('users/data', 'admin\UsersController@data')->name('users.data');
 	Route::resource('users', 'admin\UsersController');

	Route::any('deal-sources/data', 'admin\DealSourcesController@data')->name('deal-sources.data');
	Route::resource('deal-sources', 'admin\DealSourcesController');
        
	Route::any('deals/data', 'admin\DealsController@data')->name('deals.data');
	Route::resource('deals', 'admin\DealsController');        

	Route::any('galleryofguns/data', 'admin\GalleryGunsController@data')->name('galleryofguns.data');
	Route::resource('galleryofguns', 'admin\GalleryGunsController');        

	Route::any('products/pending', 'admin\ProductsController@pending')->name('products.pending');
	Route::any('products/data', 'admin\ProductsController@data')->name('products.data');
	Route::resource('products', 'admin\ProductsController');        


    });    
});

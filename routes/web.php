<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

$routeOptions = [];
if (env('DISABLE_REGISTRATION', 'false') == 'true') {
    $routeOptions['register'] = false;
}
Auth::routes($routeOptions);

Route::get('/home', 'HomeController@index')->name('home');

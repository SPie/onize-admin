<?php

use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Users\Profile;
use App\Http\Livewire\Users\Register;
use Illuminate\Routing\Router;

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
 * @var Router $router
 */

$router->get('/register')->name(Register::NAME_REGISTER)->uses(Register::class);

$router->get('/login')->name(Login::NAME_LOGIN)->uses(Login::class);

$router->group(['middleware' => ['auth']], function (Router $router) {
    $router->view('/', 'welcome')->name('home');
    $router->get('/profile')->name(Profile::NAME_PROFILE)->uses(Profile::class);
});
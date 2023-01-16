<?php

use App\Http\Controllers\mainController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|\
*/

Route::get('/', function () {
    return view('login');
});

Route::get('/home', [mainController::class, 'home'])->name('home');

Route::post('/userlogin', [mainController::class, 'userlogin'])->name('userlogin');

Route::get('/kitchenexpress', [mainController::class, 'kitchenexpress'])->name('kitchenexpress');

Route::get('/addtocart/{productid}/{userid}', [mainController::class, 'addtocart'])->name('addtocart');

Route::get('/proceedtocart', [mainController::class, 'proceedtocart'])->name('proceedtocart');

Route::get('/addquantity/{productid}/{cartid}', [mainController::class, 'addquantity'])->name('addquantity');

Route::get('/subtractquantity/{productid}/{cartid}', [mainController::class, 'subtractquantity'])->name('subtractquantity');

Route::get('/payment/{cartid}', [mainController::class, 'payment'])->name('payment');

Route::get('/profile', [mainController::class, 'profile'])->name('profile');

Route::get('/order-summarry/{cartid}', [mainController::class, 'order_summary'])->name('order_summary');
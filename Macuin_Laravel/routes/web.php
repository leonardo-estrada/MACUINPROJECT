<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('catalogo');
});

Route::view('/login', 'auth.login')->name('login');
Route::view('/registro', 'auth.registro')->name('registro');

Route::view('/catalogo', 'cliente.catalogo')->name('catalogo');
Route::view('/checkout', 'cliente.checkout')->name('checkout');
Route::view('/historial', 'cliente.historial')->name('historial');

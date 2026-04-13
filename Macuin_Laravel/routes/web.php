<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CarritoController;


Route::get('/', function () {
    return redirect()->route('catalogo');
});

Route::view('/login', 'auth.login')->name('login');
Route::view('/registro', 'auth.registro')->name('registro');

Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');

Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
Route::post('/carrito/vaciar', [CarritoController::class, 'vaciar'])->name('carrito.vaciar');
Route::post('/carrito/checkout', [CarritoController::class, 'checkout'])->name('carrito.checkout');

Route::view('/historial', 'cliente.historial')->name('historial');

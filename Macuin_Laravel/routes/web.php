<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ClienteAuthController;
use App\Http\Controllers\HistorialController;


Route::get('/', function () {
    return redirect()->route('catalogo');
});

Route::get('/login', [ClienteAuthController::class, 'loginForm'])->name('login');
Route::post('/login', [ClienteAuthController::class, 'login']);
Route::get('/registro', [ClienteAuthController::class, 'registroForm'])->name('registro');
Route::post('/registro', [ClienteAuthController::class, 'registro']);
Route::get('/recuperar-password', [ClienteAuthController::class, 'recuperarForm'])->name('password.request');
Route::post('/recuperar-password', [ClienteAuthController::class, 'recuperar'])->name('password.reset');
Route::post('/logout', [ClienteAuthController::class, 'logout'])->name('logout');

Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');

Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
Route::post('/carrito/actualizar', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
Route::post('/carrito/{id}/eliminar', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
Route::post('/carrito/vaciar', [CarritoController::class, 'vaciar'])->name('carrito.vaciar');
Route::post('/carrito/checkout', [CarritoController::class, 'checkout'])->name('carrito.checkout');

Route::get('/historial', [HistorialController::class, 'index'])->name('historial');

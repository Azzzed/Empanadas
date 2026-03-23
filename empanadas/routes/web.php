<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------
// Módulo POS
// ---------------------------------------------------------------
Route::prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('index');
    Route::post('/venta', [PosController::class, 'procesarVenta'])->name('venta.procesar');
    Route::get('/comprobante/{sale}', [PosController::class, 'comprobante'])->name('comprobante');

    // AJAX endpoints
    Route::get('/buscar-productos', [PosController::class, 'buscarProductos'])->name('productos.buscar');
    Route::get('/buscar-clientes', [PosController::class, 'buscarClientes'])->name('clientes.buscar');
    Route::post('/cliente-rapido', [PosController::class, 'crearClienteRapido'])->name('cliente.rapido');
});

// ---------------------------------------------------------------
// Módulo Administración
// ---------------------------------------------------------------
Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard redirige al POS por ahora
    Route::get('/', fn() => redirect()->route('admin.reports.index'))->name('dashboard');

    // Productos
    Route::resource('products', ProductController::class);
    Route::patch('/products/{product}/toggle-activo', [ProductController::class, 'toggleActivo'])
         ->name('products.toggle');

    // Clientes
    Route::resource('customers', CustomerController::class);

    // Informes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

// Redirect raíz al POS
Route::get('/', fn() => redirect()->route('pos.index'));

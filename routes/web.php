<?php
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantOperationController;

Route::get('/', fn() => redirect()->route('tenants.index'));
Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
Route::post('/tenants/{tenant}/status', [TenantController::class, 'updateStatus'])->name('tenants.status');
Route::post('/tenants/{tenant}/provision', [TenantController::class, 'provision'])->name('tenants.provision');
Route::post('/tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
Route::post('/tenants/{tenant}/resume', [TenantController::class, 'resume'])->name('tenants.resume');
Route::post('/tenants/{tenant}/unprovision', [TenantController::class, 'unprovision'])->name('tenants.unprovision');


Route::get('/tenants/{tenant}/operations',[TenantOperationController::class, 'index'])->name('tenants.operations');
Route::get('/operations/{operation}',[TenantOperationController::class, 'show'])->name('operations.show');
<?php

use App\Livewire\Cafe\CafeTableIndex;
use App\Livewire\Cafe\ProductCategoryIndex;
use App\Livewire\Cafe\ProductIndex;
use App\Livewire\Cafe\SupplierIndex;
use App\Livewire\Settings\PermissionIndex;
use App\Livewire\Settings\RoleIndex;
use App\Livewire\Users\UserIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // User Management
    Route::get('users', UserIndex::class)->name('users.index');

    // Role & Permission Management (RBAC)
    Route::get('roles', RoleIndex::class)->name('roles.index');
    Route::get('permissions', PermissionIndex::class)->name('permissions.index');

    // Cafe Management
    Route::get('cafe/product-categories', ProductCategoryIndex::class)->name('cafe.product-categories.index');
    Route::get('cafe/products', ProductIndex::class)->name('cafe.products.index');
    Route::get('cafe/tables', CafeTableIndex::class)->name('cafe.tables.index');
    Route::get('cafe/suppliers', SupplierIndex::class)->name('cafe.suppliers.index');
});

require __DIR__ . '/settings.php';

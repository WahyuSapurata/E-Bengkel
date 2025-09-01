<?php

use App\Http\Controllers\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', [Dashboard::class, 'landing_page'])->name('landing_page');

Route::group([
    'prefix' => 'login',
    'middleware' => ['guest'],
    'as' => 'login.'
], function () {
    Route::get('/login-akun', [App\Http\Controllers\Auth::class, 'show'])->name('login-akun');
    Route::post('/login-proses', [App\Http\Controllers\Auth::class, 'login_proses'])->name('login-proses');
});

Route::group([
    'prefix' => 'superadmin',
    'middleware' => ['ceklogin'],
    'as' => 'superadmin.'
], function () {
    Route::get('/dashboard-superadmin', [App\Http\Controllers\Dashboard::class, 'dashboard_superadmin'])->name('dashboard-superadmin');

    Route::prefix('master-data')->group(function () {
        Route::get('/kategori', [App\Http\Controllers\KategoriController::class, 'index'])->name('kategori');
        Route::get('/kategori-get', [App\Http\Controllers\KategoriController::class, 'get'])->name('kategori-get');
        Route::post('/kategori-store', [App\Http\Controllers\KategoriController::class, 'store'])->name('kategori-store');
        Route::get('/kategori-edit/{params}', [App\Http\Controllers\KategoriController::class, 'edit'])->name('kategori-edit');
        Route::put('/kategori-update/{params}', [App\Http\Controllers\KategoriController::class, 'update'])->name('kategori-update');
        Route::delete('/kategori-delete/{params}', [App\Http\Controllers\KategoriController::class, 'delete'])->name('kategori-delete');

        Route::get('/subkategori', [App\Http\Controllers\SubKategoriController::class, 'index'])->name('subkategori');
        Route::get('/subkategori-get', [App\Http\Controllers\SubKategoriController::class, 'get'])->name('subkategori-get');
        Route::post('/subkategori-store', [App\Http\Controllers\SubKategoriController::class, 'store'])->name('subkategori-store');
        Route::get('/subkategori-edit/{params}', [App\Http\Controllers\SubKategoriController::class, 'edit'])->name('subkategori-edit');
        Route::put('/subkategori-update/{params}', [App\Http\Controllers\SubKategoriController::class, 'update'])->name('subkategori-update');
        Route::delete('/subkategori-delete/{params}', [App\Http\Controllers\SubKategoriController::class, 'delete'])->name('subkategori-delete');

        Route::get('/suplayer', [App\Http\Controllers\SuplayerController::class, 'index'])->name('suplayer');
        Route::get('/suplayer-get', [App\Http\Controllers\SuplayerController::class, 'get'])->name('suplayer-get');
        Route::post('/suplayer-store', [App\Http\Controllers\SuplayerController::class, 'store'])->name('suplayer-store');
        Route::get('/suplayer-edit/{params}', [App\Http\Controllers\SuplayerController::class, 'edit'])->name('suplayer-edit');
        Route::put('/suplayer-update/{params}', [App\Http\Controllers\SuplayerController::class, 'update'])->name('suplayer-update');
        Route::delete('/suplayer-delete/{params}', [App\Http\Controllers\SuplayerController::class, 'delete'])->name('suplayer-delete');

        Route::get('/jasa', [App\Http\Controllers\JasaController::class, 'index'])->name('jasa');
        Route::get('/jasa-get', [App\Http\Controllers\JasaController::class, 'get'])->name('jasa-get');
        Route::post('/jasa-store', [App\Http\Controllers\JasaController::class, 'store'])->name('jasa-store');
        Route::get('/jasa-edit/{params}', [App\Http\Controllers\JasaController::class, 'edit'])->name('jasa-edit');
        Route::put('/jasa-update/{params}', [App\Http\Controllers\JasaController::class, 'update'])->name('jasa-update');
        Route::delete('/jasa-delete/{params}', [App\Http\Controllers\JasaController::class, 'delete'])->name('jasa-delete');

        Route::get('/produk', [App\Http\Controllers\ProdukController::class, 'index'])->name('produk');
        Route::get('/produk-get', [App\Http\Controllers\ProdukController::class, 'get'])->name('produk-get');
        Route::post('/produk-store', [App\Http\Controllers\ProdukController::class, 'store'])->name('produk-store');
        Route::get('/produk-edit/{params}', [App\Http\Controllers\ProdukController::class, 'edit'])->name('produk-edit');
        Route::post('/produk-update/{params}', [App\Http\Controllers\ProdukController::class, 'update'])->name('produk-update');
        Route::delete('/produk-delete/{params}', [App\Http\Controllers\ProdukController::class, 'delete'])->name('produk-delete');

        Route::get('/price-history/{params}', [App\Http\Controllers\ProdukController::class, 'price_history'])->name('price-history');
        Route::get('/get-price-history/{params}', [App\Http\Controllers\ProdukController::class, 'get_price_history'])->name('get-price-history');

        Route::get('/opname-stock/{params}', [App\Http\Controllers\ProdukController::class, 'opname_stock'])->name('opname-stock');
        Route::post('/store_opname', [App\Http\Controllers\ProdukController::class, 'store_opname'])->name('store_opname');

        Route::get('/produk-price/{params}', [App\Http\Controllers\ProdukPriceController::class, 'index'])->name('produk-price');
        Route::get('/produk-price-get/{params}', [App\Http\Controllers\ProdukPriceController::class, 'get'])->name('produk-price-get');
        Route::post('/produk-price-store', [App\Http\Controllers\ProdukPriceController::class, 'store'])->name('produk-price-store');
        Route::get('/produk-price-edit/{params}', [App\Http\Controllers\ProdukPriceController::class, 'edit'])->name('produk-price-edit');
        Route::post('/produk-price-update/{params}', [App\Http\Controllers\ProdukPriceController::class, 'update'])->name('produk-price-update');
        Route::delete('/produk-price-delete/{params}', [App\Http\Controllers\ProdukPriceController::class, 'delete'])->name('produk-price-delete');

        Route::get('/costumer', [App\Http\Controllers\CostumerController::class, 'index'])->name('costumer');
        Route::get('/costumer-get', [App\Http\Controllers\CostumerController::class, 'get'])->name('costumer-get');
        Route::post('/costumer-store', [App\Http\Controllers\CostumerController::class, 'store'])->name('costumer-store');
        Route::get('/costumer-edit/{params}', [App\Http\Controllers\CostumerController::class, 'edit'])->name('costumer-edit');
        Route::post('/costumer-update/{params}', [App\Http\Controllers\CostumerController::class, 'update'])->name('costumer-update');
        Route::delete('/costumer-delete/{params}', [App\Http\Controllers\CostumerController::class, 'delete'])->name('costumer-delete');

        Route::get('/outlet', [App\Http\Controllers\OutletController::class, 'index'])->name('outlet');
        Route::get('/outlet-get', [App\Http\Controllers\OutletController::class, 'get'])->name('outlet-get');
        Route::post('/outlet-store', [App\Http\Controllers\OutletController::class, 'store'])->name('outlet-store');
        Route::get('/outlet-edit/{params}', [App\Http\Controllers\OutletController::class, 'edit'])->name('outlet-edit');
        Route::post('/outlet-update/{params}', [App\Http\Controllers\OutletController::class, 'update'])->name('outlet-update');
        Route::delete('/outlet-delete/{params}', [App\Http\Controllers\OutletController::class, 'delete'])->name('outlet-delete');

        Route::get('/karyawan', [App\Http\Controllers\KaryawanController::class, 'index'])->name('karyawan');
        Route::get('/karyawan-get', [App\Http\Controllers\KaryawanController::class, 'get'])->name('karyawan-get');
        Route::post('/karyawan-store', [App\Http\Controllers\KaryawanController::class, 'store'])->name('karyawan-store');
        Route::get('/karyawan-edit/{params}', [App\Http\Controllers\KaryawanController::class, 'edit'])->name('karyawan-edit');
        Route::put('/karyawan-update/{params}', [App\Http\Controllers\KaryawanController::class, 'update'])->name('karyawan-update');
        Route::delete('/karyawan-delete/{params}', [App\Http\Controllers\KaryawanController::class, 'delete'])->name('karyawan-delete');
    });

    Route::prefix('transaksi')->group(function () {
        Route::get('/pembelian', [App\Http\Controllers\PembelianController::class, 'index'])->name('pembelian');
        Route::get('/pembelian-get', [App\Http\Controllers\PembelianController::class, 'get'])->name('pembelian-get');
        Route::post('/pembelian-store', [App\Http\Controllers\PembelianController::class, 'store'])->name('pembelian-store');
        Route::get('/pembelian-edit/{params}', [App\Http\Controllers\PembelianController::class, 'edit'])->name('pembelian-edit');
        Route::post('/pembelian-update/{params}', [App\Http\Controllers\PembelianController::class, 'update'])->name('pembelian-update');
        Route::delete('/pembelian-delete/{params}', [App\Http\Controllers\PembelianController::class, 'delete'])->name('pembelian-delete');

        Route::get('/pembelian-get-produk-by-suplayer/{params}', [App\Http\Controllers\PembelianController::class, 'getProdukBySuplayer'])->name('pembelian-get-produk-by-suplayer');

        Route::get('/po-pusat', [App\Http\Controllers\PoPusatController::class, 'index'])->name('po-pusat');
        Route::get('/po-pusat-get', [App\Http\Controllers\PoPusatController::class, 'get'])->name('po-pusat-get');
        Route::post('/po-pusat-store', [App\Http\Controllers\PoPusatController::class, 'store'])->name('po-pusat-store');
        Route::get('/po-pusat-edit/{params}', [App\Http\Controllers\PoPusatController::class, 'edit'])->name('po-pusat-edit');
        Route::post('/po-pusat-update/{params}', [App\Http\Controllers\PoPusatController::class, 'update'])->name('po-pusat-update');
        Route::delete('/po-pusat-delete/{params}', [App\Http\Controllers\PoPusatController::class, 'delete'])->name('po-pusat-delete');

        Route::get('/po-pusat-get-produk-by-suplayer/{params}', [App\Http\Controllers\PoPusatController::class, 'getProdukBySuplayer'])->name('po-pusat-get-produk-by-suplayer');

        Route::get('/form-po/{params}', [App\Http\Controllers\PembelianController::class, 'form_po'])->name('form-po');

        Route::get('/po-vw-outlet', [App\Http\Controllers\PoOutletController::class, 'vw_pusat'])->name('po-vw-outlet');
        Route::get('/po-vw-outlet-get', [App\Http\Controllers\PoOutletController::class, 'get_vw_outlet'])->name('po-vw-outlet-get');
        Route::post('/aprove-po-outlet/{params}', [App\Http\Controllers\PoOutletController::class, 'aprove_po_outlet'])->name('aprove-po-outlet');

        Route::get('/pengiriman', [App\Http\Controllers\PengirimanBarangController::class, 'index'])->name('pengiriman');
        Route::get('/pengiriman-get', [App\Http\Controllers\PengirimanBarangController::class, 'get'])->name('pengiriman-get');
        Route::post('/pengiriman-store', [App\Http\Controllers\PengirimanBarangController::class, 'store'])->name('pengiriman-store');
        Route::get('/pengiriman-edit/{params}', [App\Http\Controllers\PengirimanBarangController::class, 'edit'])->name('pengiriman-edit');
        Route::post('/pengiriman-update/{params}', [App\Http\Controllers\PengirimanBarangController::class, 'update'])->name('pengiriman-update');
        Route::delete('/pengiriman-delete/{params}', [App\Http\Controllers\PengirimanBarangController::class, 'delete'])->name('pengiriman-delete');

        Route::get('/hutang', [App\Http\Controllers\HutangController::class, 'index'])->name('hutang');
        Route::get('/hutang-get', [App\Http\Controllers\HutangController::class, 'get'])->name('hutang-get');
        Route::get('/hutang-edit/{params}', [App\Http\Controllers\HutangController::class, 'edit'])->name('hutang-edit');
        Route::post('/hutang-update/{params}', [App\Http\Controllers\HutangController::class, 'update'])->name('hutang-update');
        Route::delete('/hutang-delete/{params}', [App\Http\Controllers\HutangController::class, 'delete'])->name('hutang-delete');
    });

    Route::prefix('accounting')->group(function () {
        Route::get('/akun', [App\Http\Controllers\CoaController::class, 'index'])->name('akun');
        Route::get('/akun-get', [App\Http\Controllers\CoaController::class, 'get'])->name('akun-get');
        Route::post('/akun-store', [App\Http\Controllers\CoaController::class, 'store'])->name('akun-store');
        Route::get('/akun-edit/{params}', [App\Http\Controllers\CoaController::class, 'edit'])->name('akun-edit');
        Route::post('/akun-update/{params}', [App\Http\Controllers\CoaController::class, 'update'])->name('akun-update');
        Route::delete('/akun-delete/{params}', [App\Http\Controllers\CoaController::class, 'delete'])->name('akun-delete');

        Route::get('/gaji', [App\Http\Controllers\GajiController::class, 'index'])->name('gaji');
        Route::get('/gaji-get', [App\Http\Controllers\GajiController::class, 'get'])->name('gaji-get');
        Route::post('/gaji-store', [App\Http\Controllers\GajiController::class, 'store'])->name('gaji-store');
        Route::get('/gaji-edit/{params}', [App\Http\Controllers\GajiController::class, 'edit'])->name('gaji-edit');
        Route::post('/gaji-update/{params}', [App\Http\Controllers\GajiController::class, 'update'])->name('gaji-update');
        Route::delete('/gaji-delete/{params}', [App\Http\Controllers\GajiController::class, 'delete'])->name('gaji-delete');

        Route::get('/biaya', [App\Http\Controllers\BiayaController::class, 'index'])->name('biaya');
        Route::get('/biaya-get', [App\Http\Controllers\BiayaController::class, 'get'])->name('biaya-get');
        Route::post('/biaya-store', [App\Http\Controllers\BiayaController::class, 'store'])->name('biaya-store');
        Route::get('/biaya-edit/{params}', [App\Http\Controllers\BiayaController::class, 'edit'])->name('biaya-edit');
        Route::post('/biaya-update/{params}', [App\Http\Controllers\BiayaController::class, 'update'])->name('biaya-update');
        Route::delete('/biaya-delete/{params}', [App\Http\Controllers\BiayaController::class, 'delete'])->name('biaya-delete');

        Route::get('/vw-jurnal-umum', [App\Http\Controllers\ReportController::class, 'vw_jurnal_umum'])->name('vw-jurnal-umum');
        Route::get('/get-jurnal-umum', [App\Http\Controllers\ReportController::class, 'get_jurnal_umum'])->name('get-jurnal-umum');
    });
});


Route::group([
    'prefix' => 'outlet',
    'middleware' => ['ceklogin'],
    'as' => 'outlet.'
], function () {
    Route::get('/dashboard-outlet', [App\Http\Controllers\Dashboard::class, 'dashboard_outlet'])->name('dashboard-outlet');

    Route::get('/produk', [App\Http\Controllers\ProdukController::class, 'vw_outlet'])->name('produk');
    Route::get('/produk-get', [App\Http\Controllers\ProdukController::class, 'get_outlet'])->name('produk-get');

    Route::get('/opname-stock/{params}', [App\Http\Controllers\ProdukController::class, 'opname_stock_outlet'])->name('opname-stock');
    Route::post('/store_opname', [App\Http\Controllers\ProdukController::class, 'store_opname_outlet'])->name('store_opname');

    Route::get('/kasir-outlet', [App\Http\Controllers\KasirOutletController::class, 'index'])->name('kasir-outlet');
    Route::get('/kasir-outlet-get', [App\Http\Controllers\KasirOutletController::class, 'get'])->name('kasir-outlet-get');
    Route::post('/kasir-outlet-store', [App\Http\Controllers\KasirOutletController::class, 'store'])->name('kasir-outlet-store');
    Route::get('/kasir-outlet-edit/{params}', [App\Http\Controllers\KasirOutletController::class, 'edit'])->name('kasir-outlet-edit');
    Route::post('/kasir-outlet-update/{params}', [App\Http\Controllers\KasirOutletController::class, 'update'])->name('kasir-outlet-update');
    Route::delete('/kasir-outlet-delete/{params}', [App\Http\Controllers\KasirOutletController::class, 'delete'])->name('kasir-outlet-delete');

    Route::get('/po-outlet', [App\Http\Controllers\PoOutletController::class, 'index'])->name('po-outlet');
    Route::get('/po-outlet-get', [App\Http\Controllers\PoOutletController::class, 'get'])->name('po-outlet-get');
    Route::post('/po-outlet-store', [App\Http\Controllers\PoOutletController::class, 'store'])->name('po-outlet-store');
    Route::get('/po-outlet-edit/{params}', [App\Http\Controllers\PoOutletController::class, 'edit'])->name('po-outlet-edit');
    Route::post('/po-outlet-update/{params}', [App\Http\Controllers\PoOutletController::class, 'update'])->name('po-outlet-update');
    Route::delete('/po-outlet-delete/{params}', [App\Http\Controllers\PoOutletController::class, 'delete'])->name('po-outlet-delete');

    Route::get('/do-vw-outlet', [App\Http\Controllers\PengirimanBarangController::class, 'vw_outlet'])->name('do-vw-outlet');
    Route::get('/do-vw-outlet-get', [App\Http\Controllers\PengirimanBarangController::class, 'get_vw_outlet'])->name('do-vw-outlet-get');
    Route::post('/aprove-do-outlet/{params}', [App\Http\Controllers\PengirimanBarangController::class, 'aprove_do_outlet'])->name('aprove-do-outlet');

    Route::get('/transfer', [App\Http\Controllers\TransferBarangController::class, 'index'])->name('transfer');
    Route::get('/transfer-get', [App\Http\Controllers\TransferBarangController::class, 'get'])->name('transfer-get');
    Route::post('/transfer-store', [App\Http\Controllers\TransferBarangController::class, 'store'])->name('transfer-store');
    Route::get('/transfer-edit/{params}', [App\Http\Controllers\TransferBarangController::class, 'edit'])->name('transfer-edit');
    Route::post('/transfer-update/{params}', [App\Http\Controllers\TransferBarangController::class, 'update'])->name('transfer-update');
    Route::delete('/transfer-delete/{params}', [App\Http\Controllers\TransferBarangController::class, 'delete'])->name('transfer-delete');
});

Route::group([
    'prefix' => 'kasir',
    'middleware' => ['ceklogin'],
    'as' => 'kasir.'
], function () {
    Route::get('/dashboard-kasir', [App\Http\Controllers\PenjualanController::class, 'index'])->name('dashboard-kasir');

    Route::get('/produk-get', [App\Http\Controllers\PenjualanController::class, 'get_produk'])->name('produk-get');
    Route::post('/penjualan-store', [App\Http\Controllers\PenjualanController::class, 'store'])->name('penjualan-store');

    Route::get('/get-jasa', [App\Http\Controllers\PenjualanController::class, 'get_jasa'])->name('get-jasa');
    Route::get('/get-stock', [App\Http\Controllers\PenjualanController::class, 'get_stock'])->name('get-stock');

    Route::post('/closing', [App\Http\Controllers\ClosingKasirController::class, 'store'])->name('closing');
});

Route::get('/logout', [App\Http\Controllers\Auth::class, 'logout'])->name('logout');

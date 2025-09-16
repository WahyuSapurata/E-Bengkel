<?php

use App\Http\Controllers\Api\BotProdukController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/bot/test', function () {
    return response()->json(['msg' => 'API bot jalan!']);
});

Route::get('/bot/outlet', [BotProdukController::class, 'get_outlet']);
Route::get('/bot/produk', [BotProdukController::class, 'search']);

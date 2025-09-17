<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class CetakLabelRak extends Controller
{
    public function index()
    {
        $module = 'Cetak Label Rak';
        return view('pages.cetaklabelrak.index', compact('module'));
    }

    public function get_produk($params)
    {
        $produk = Produk::where('kode', $params)->first();
        if ($produk) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'kode' => $produk->kode,
                    'nama_barang' => $produk->nama_barang,
                    'merek' => $produk->merek,
                    'satuan' => $produk->satuan,
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
    }
}

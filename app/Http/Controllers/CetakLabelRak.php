<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\ProdukPrice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

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

        if (!$produk) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        // Ambil harga jual default dari DB
        $defaultHargaJual = Produk::select(DB::raw('
    ROUND(
        (CAST(hrg_modal AS DECIMAL(15,2)) + (CAST(hrg_modal AS DECIMAL(15,2)) * CAST(profit AS DECIMAL(15,2)) / 100)) / 1000
    ) * 1000 as harga_jual
'))->where('uuid', $produk->uuid)->first()->harga_jual;

        // Ambil harga jual spesifik dari tabel ProdukPrice jika ada
        $produk_price = ProdukPrice::where('uuid_produk', $produk->uuid)
            ->orderBy('qty', 'asc')
            ->get();

        // Gabungkan harga default dan harga spesifik
        $harga_jual = [];

        // Tambahkan default dulu
        $harga_jual[] = [
            'qty' => 1,
            'harga_jual' => $defaultHargaJual,
        ];

        // Tambahkan harga dari ProdukPrice
        foreach ($produk_price as $price) {
            $harga_jual[] = [
                'qty' => $price->qty,
                'harga_jual' => $price->harga_jual,
            ];
        }

        // Return response
        return response()->json([
            'status' => 'success',
            'data' => [
                'kode' => $produk->kode,
                'nama_barang' => $produk->nama_barang,
                'merek' => $produk->merek,
                'satuan' => $produk->satuan,
                'harga_jual' => $harga_jual,
            ]
        ]);
    }

    public function cetakLabelRak(Request $request)
    {
        $produkList = collect($request->input('produk', []));

        if (empty($produkList)) {
            return back()->with('error', 'Tidak ada produk untuk dicetak');
        }

        // return view('tools.cetaklabelrak', compact('produkList'));

        $pdf = Pdf::loadView('tools.cetaklabelrak', compact('produkList'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('label-rak.pdf');
    }
}

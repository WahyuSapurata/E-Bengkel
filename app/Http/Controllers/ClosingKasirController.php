<?php

namespace App\Http\Controllers;

use App\Helpers\JurnalHelper;
use App\Models\ClosingKasir;
use App\Models\Coa;
use App\Models\Penjualan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClosingKasirController extends Controller
{
    /**
     * Proses Closing Kasir
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'total_fisik'       => 'required', // uang fisik dari kasir
            ],
            [
                'total_fisik.required' => 'Kolom total fisik harus di isi.'
            ]
        );

        $tanggal = Carbon::now()->format('d-m-Y');

        $penjualans = Penjualan::where('uuid_outlet', $request->uuid_kasir_outlet)
            ->whereDate('tanggal_transaksi', $tanggal)
            ->with('details') // relasi ke detail_penjualans
            ->get();

        // Total penjualan dihitung dari detail
        $totalPenjualan = $penjualans->sum(function ($p) {
            return $p->details->sum('total_harga');
        });

        // Total cash & transfer berdasarkan metode pembayaran
        $totalCash = $penjualans->where('pembayaran', 'Tunai')->sum(function ($p) {
            return $p->details->sum('total_harga');
        });

        $totalTransfer = $penjualans->where('pembayaran', 'Transfer Bank')->sum(function ($p) {
            return $p->details->sum('total_harga');
        });


        // Hitung selisih antara sistem vs fisik
        $selisih = $request->total_fisik - $totalCash;

        // Simpan ke tabel closing_kasirs
        $closing = ClosingKasir::create([
            'uuid_kasir_outlet' => $request->uuid_kasir_outlet,
            'tanggal_closing'  => $tanggal,
            'total_penjualan'  => $totalPenjualan,
            'total_cash'       => $totalCash,
            'total_transfer'   => $totalTransfer,
            'total_fisik'      => $request->total_fisik,
            'selisih'          => $selisih,
        ]);

        // === Catat Jurnal Closing ===
        $kasOutlet = Coa::where('nama', 'Kas Outlet')->firstOrFail();
        $kas       = Coa::where('nama', 'Kas')->firstOrFail();

        // Buat nomor bukti khusus closing
        $no_bukti = 'CLS-' . strtoupper(Str::random(6));

        // Setor seluruh cash dari kas outlet ke kas pusat
        if ($totalCash > 0) {
            JurnalHelper::create(
                $tanggal,
                $no_bukti,
                'Closing Kasir',
                [
                    ['uuid_coa' => $kas->uuid,       'debit'  => $totalCash],
                    ['uuid_coa' => $kasOutlet->uuid, 'kredit' => $totalCash],
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'data'   => $closing
        ]);
    }

    // /**
    //  * Lihat riwayat closing kasir
    //  */
    // public function index(Request $request)
    // {
    //     $closings = ClosingKasir::orderBy('tanggal_closing', 'desc')->get();

    //     return response()->json([
    //         'status' => 'success',
    //         'data'   => $closings
    //     ]);
    // }
}

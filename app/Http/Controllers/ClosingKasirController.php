<?php

namespace App\Http\Controllers;

use App\Helpers\JurnalHelper;
use App\Models\ClosingKasir;
use App\Models\Coa;
use App\Models\KasirOutlet;
use App\Models\Outlet;
use App\Models\Penjualan;
use App\Models\StatusBarang;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                'total_fisik' => 'required', // uang fisik dari kasir
            ],
            [
                'total_fisik.required' => 'Kolom total fisik harus di isi.'
            ]
        );

        $kasir = KasirOutlet::where('uuid_user', Auth::user()->uuid)->firstOrFail();
        $tanggal = Carbon::now()->format('d-m-Y');

        $closingDates = ClosingKasir::where('uuid_kasir_outlet', $kasir->uuid_user)
            ->pluck('tanggal_closing')
            ->toArray();

        $penjualans = Penjualan::where('uuid_outlet', $kasir->uuid_outlet)
            ->where('created_by', Auth::user()->nama)
            ->whereNotIn('tanggal_transaksi', $closingDates)
            ->with('detailPenjualans') // ambil detail
            ->get();

        // Total penjualan dihitung dari detail + jasa
        $totalPenjualan = $penjualans->sum(function ($p) {
            $totalDetail = $p->detailPenjualans->sum('total_harga');
            $totalJasa = 0;
            if ($p->uuid_jasa) {
                // Decode JSON menjadi array
                $uuidJasaArray = $p->uuid_jasa;

                if (!empty($uuidJasaArray)) {
                    // Ambil semua jasa yang UUID-nya ada di array
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();

                    // Jumlahkan harga semua jasa
                    $totalJasa = $jasa->sum('harga');
                }
            }
            return $totalDetail + $totalJasa;
        });

        // Total cash & transfer termasuk jasa
        $totalCash = $penjualans->where('pembayaran', 'Tunai')->sum(function ($p) {
            $totalDetail = $p->detailPenjualans->sum('total_harga');
            $totalJasa = 0;
            if ($p->uuid_jasa) {
                // Decode JSON menjadi array
                $uuidJasaArray = $p->uuid_jasa;

                if (!empty($uuidJasaArray)) {
                    // Ambil semua jasa yang UUID-nya ada di array
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();

                    // Jumlahkan harga semua jasa
                    $totalJasa = $jasa->sum('harga');
                }
            }
            return $totalDetail + $totalJasa;
        });

        $totalTransfer = $penjualans->where('pembayaran', 'Transfer Bank')->sum(function ($p) {
            $totalDetail = $p->detailPenjualans->sum('total_harga');
            $totalJasa = 0;
            if ($p->uuid_jasa) {
                // Decode JSON menjadi array
                $uuidJasaArray = $p->uuid_jasa;

                if (!empty($uuidJasaArray)) {
                    // Ambil semua jasa yang UUID-nya ada di array
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();

                    // Jumlahkan harga semua jasa
                    $totalJasa = $jasa->sum('harga');
                }
            }
            return $totalDetail + $totalJasa;
        });

        // Hitung selisih antara sistem vs fisik
        $selisih = $request->total_fisik - $totalCash;

        // Simpan ke tabel closing_kasirs
        $closing = ClosingKasir::create([
            'uuid_kasir_outlet' => $request->uuid_kasir_outlet,
            'tanggal_closing'   => $tanggal,
            'total_penjualan'   => $totalPenjualan,
            'total_cash'        => $totalCash,
            'total_transfer'    => $totalTransfer,
            'total_fisik'       => $request->total_fisik,
            'selisih'           => $selisih,
        ]);

        StatusBarang::create([
            'uuid_log_barang' => $closing->uuid,
            'ref' => 'closing',
            'ketarangan' => Auth::user()->nama . ' Telah melakukan closing',
        ]);

        // Catat Jurnal Closing
        $kasOutlet = Coa::where('nama', 'Kas Outlet')->firstOrFail();
        $kas       = Coa::where('nama', 'Kas')->firstOrFail();
        $no_bukti  = 'CLS-' . strtoupper(Str::random(6));

        // Setor seluruh cash dari kas outlet ke kas pusat
        if ($totalCash > 0) {
            JurnalHelper::create(
                $tanggal,
                $no_bukti,
                'Closing Kasir',
                [
                    ['uuid_coa' => $kas->uuid,       'debit'  => $totalCash],
                    ['uuid_coa' => $kasOutlet->uuid, 'kredit' => $totalCash],
                ],
                $kasir->uuid_outlet
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
    public function index($params)
    {
        $kasir = KasirOutlet::where('uuid_user', Auth::user()->uuid)->firstOrFail();
        $outlet = Outlet::where('uuid_user', $kasir->uuid_outlet)->first();
        $tanggal = Carbon::now()->format('d-m-Y');

        $closingDates = ClosingKasir::where('uuid_kasir_outlet', $kasir->uuid_user)
            ->where('uuid', $params)
            ->pluck('tanggal_closing')
            ->toArray();

        $penjualans = Penjualan::where('uuid_outlet', $kasir->uuid_outlet)
            ->where('created_by', Auth::user()->nama)
            ->whereIn('tanggal_transaksi', $closingDates)
            ->with('detailPenjualans')
            ->get();

        // Hitung total penjualan termasuk jasa
        $totalPenjualan = $penjualans->sum(function ($p) {
            $totalDetail = $p->detailPenjualans->sum('total_harga');
            $totalJasa = 0;
            if ($p->uuid_jasa) {
                // Decode JSON menjadi array
                $uuidJasaArray = $p->uuid_jasa;

                if (!empty($uuidJasaArray)) {
                    // Ambil semua jasa yang UUID-nya ada di array
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();

                    // Jumlahkan harga semua jasa
                    $totalJasa = $jasa->sum('harga');
                }
            }
            return $totalDetail + $totalJasa;
        });

        $totalCash = $penjualans->where('pembayaran', 'Tunai')->sum(function ($p) {
            $totalDetail = $p->detailPenjualans->sum('total_harga');
            $totalJasa = 0;
            if ($p->uuid_jasa) {
                // Decode JSON menjadi array
                $uuidJasaArray = $p->uuid_jasa;

                if (!empty($uuidJasaArray)) {
                    // Ambil semua jasa yang UUID-nya ada di array
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();

                    // Jumlahkan harga semua jasa
                    $totalJasa = $jasa->sum('harga');
                }
            }
            return $totalDetail + $totalJasa;
        });

        $totalTransfer = $penjualans->where('pembayaran', 'Transfer Bank')->sum(function ($p) {
            $totalDetail = $p->detailPenjualans->sum('total_harga');
            $totalJasa = 0;
            if ($p->uuid_jasa) {
                // Decode JSON menjadi array
                $uuidJasaArray = $p->uuid_jasa;

                if (!empty($uuidJasaArray)) {
                    // Ambil semua jasa yang UUID-nya ada di array
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();

                    // Jumlahkan harga semua jasa
                    $totalJasa = $jasa->sum('harga');
                }
            }
            return $totalDetail + $totalJasa;
        });

        $totalNonTunai = $totalTransfer;

        $detailNonTunai = $penjualans->where('pembayaran', '!=', 'Tunai')->map(function ($p) {
            $totalDetail = $p->detailPenjualans->sum('total_harga');
            $totalJasa = 0;
            if ($p->uuid_jasa) {
                // Decode JSON menjadi array
                $uuidJasaArray = $p->uuid_jasa;

                if (!empty($uuidJasaArray)) {
                    // Ambil semua jasa yang UUID-nya ada di array
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();

                    // Jumlahkan harga semua jasa
                    $totalJasa = $jasa->sum('harga');
                }
            }
            return [
                'jenis'      => $p->pembayaran,
                'no_invoice' => $p->no_bukti,
                'nominal'    => $totalDetail + $totalJasa,
            ];
        })->values();

        $summaryReport = [
            'tanggal'            => $tanggal,
            'kasir'              => Auth::user()->nama,
            'saldo_awal'         => 0,
            'penjualan_non_tunai' => $totalNonTunai,
            'penjualan_tunai'    => $totalCash,
            'total_penjualan'    => $totalPenjualan,
            'detail_non_tunai'   => $detailNonTunai,
            'total_non_tunai'    => $totalNonTunai,
            'setoran_tunai'      => $totalCash,
            'batal'              => 0
        ];

        $pdf = Pdf::loadView('kasir.sumaryreport.index', [
            'report' => $summaryReport,
            'outlet' => $outlet->nama_outlet,
            'alamat' => $outlet->alamat
        ]);

        return $pdf->stream('summary-report.pdf');
    }

    // public function history_summary($params)
    // {
    //     // Sama dengan index, bisa dipanggil ulang dari index() untuk menghindari duplikasi
    //     return $this->index($params);
    // }


    public function sumaryreport()
    {
        $module = 'Sumary Report';
        $data = ClosingKasir::all();
        $data->map(function ($item) {
            $kasir = KasirOutlet::where('uuid_user', $item->uuid_kasir_outlet)->firstOrFail();

            $item->kasir = User::where('uuid', $kasir->uuid_user)->first()->nama;
            // $item->uuid_kasir = $item->uuid_kasir_outlet;
            return $item;
        });
        return view('outlet.sumarireort.index', compact('module', 'data'));
    }

    public function history_summary($params)
    {
        $outlet = Outlet::where('uuid_user', Auth::user()->uuid)->first();

        $kasir = KasirOutlet::where('uuid_outlet', Auth::user()->uuid)->first();
        $namaKasir = User::where('uuid', $kasir->uuid_user)->first();

        $tanggal = Carbon::now()->format('d-m-Y');

        $closingDates = ClosingKasir::where('uuid_kasir_outlet', $kasir->uuid_user)
            ->where('uuid', $params)
            ->pluck('tanggal_closing')
            ->toArray();

        $penjualans = Penjualan::where('uuid_outlet', Auth::user()->uuid)
            ->where('created_by', $namaKasir->nama)
            ->whereIn('tanggal_transaksi', $closingDates)
            ->with('detailPenjualans')
            ->get();

        // Total penjualan termasuk jasa
        $totalPenjualan = $penjualans->sum(function ($p) {
            $totalDetail = $p->detailPenjualans->sum('total_harga');
            $totalJasa = 0;
            if ($p->uuid_jasa) {
                // Decode JSON menjadi array
                $uuidJasaArray = $p->uuid_jasa;

                if (!empty($uuidJasaArray)) {
                    // Ambil semua jasa yang UUID-nya ada di array
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();

                    // Jumlahkan harga semua jasa
                    $totalJasa = $jasa->sum('harga');
                }
            }
            return $totalDetail + $totalJasa;
        });

        // Total cash & transfer termasuk jasa
        $totalCash = $penjualans->where('pembayaran', 'Tunai')->sum(function ($p) {
            $totalDetail = $p->detailPenjualans->sum('total_harga');
            $totalJasa = 0;
            if ($p->uuid_jasa) {
                // Decode JSON menjadi array
                $uuidJasaArray = $p->uuid_jasa;

                if (!empty($uuidJasaArray)) {
                    // Ambil semua jasa yang UUID-nya ada di array
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();

                    // Jumlahkan harga semua jasa
                    $totalJasa = $jasa->sum('harga');
                }
            }
            return $totalDetail + $totalJasa;
        });

        $totalTransfer = $penjualans->where('pembayaran', 'Transfer Bank')->sum(function ($p) {
            $totalDetail = $p->detailPenjualans->sum('total_harga');
            $totalJasa = 0;
            if ($p->uuid_jasa) {
                // Decode JSON menjadi array
                $uuidJasaArray = $p->uuid_jasa;

                if (!empty($uuidJasaArray)) {
                    // Ambil semua jasa yang UUID-nya ada di array
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();

                    // Jumlahkan harga semua jasa
                    $totalJasa = $jasa->sum('harga');
                }
            }
            return $totalDetail + $totalJasa;
        });

        $totalNonTunai = $totalTransfer;

        // summary detail non-tunai termasuk jasa
        $detailNonTunai = $penjualans->where('pembayaran', '!=', 'Tunai')->map(function ($p) {
            $totalDetail = $p->detailPenjualans->sum('total_harga');
            $totalJasa = 0;
            if ($p->uuid_jasa) {
                // Decode JSON menjadi array
                $uuidJasaArray = $p->uuid_jasa;

                if (!empty($uuidJasaArray)) {
                    // Ambil semua jasa yang UUID-nya ada di array
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();

                    // Jumlahkan harga semua jasa
                    $totalJasa = $jasa->sum('harga');
                }
            }
            return [
                'jenis'      => $p->pembayaran,
                'no_invoice' => $p->no_bukti,
                'nominal'    => $totalDetail + $totalJasa,
            ];
        })->values();

        $saldoAwal = 0; // kalau ada tabel saldo awal, ambil dari situ

        $summaryReport = [
            'tanggal'             => $tanggal,
            'kasir'               => Auth::user()->nama,
            'saldo_awal'          => $saldoAwal,
            'penjualan_non_tunai' => $totalNonTunai,
            'penjualan_tunai'     => $totalCash,
            'total_penjualan'     => $totalPenjualan + $saldoAwal,
            'detail_non_tunai'    => $detailNonTunai,
            'total_non_tunai'     => $totalNonTunai,
            'setoran_tunai'       => $totalCash,
            'batal'               => 0,
        ];

        $pdf = Pdf::loadView('kasir.sumaryreport.index', [
            'report' => $summaryReport,
            'outlet' => $outlet->nama_outlet,
            'alamat' => $outlet->alamat
        ]);

        return $pdf->stream('summary-report.pdf');
    }
}

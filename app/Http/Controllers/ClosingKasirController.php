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
            ->with(['detailPenjualans', 'detailPenjualanPakets']) // ⬅️ tambahkan relasi paket
            ->get();

        // Fungsi bantu hitung total per penjualan (produk + paket + jasa)
        $hitungTotal = function ($p) {
            $totalProduk = $p->detailPenjualans->sum('total_harga');
            $totalPaket  = $p->detailPenjualanPakets->sum('total_harga');

            $totalJasa = 0;
            if ($p->uuid_jasa) {
                $uuidJasaArray = $p->uuid_jasa; // sudah cast ke array di model
                if (!empty($uuidJasaArray)) {
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();
                    $totalJasa = $jasa->sum('harga');
                }
            }

            return $totalProduk + $totalPaket + $totalJasa;
        };

        // Total penjualan
        $totalPenjualan = $penjualans->sum(fn($p) => $hitungTotal($p));

        // Total cash
        $totalCash = $penjualans->where('pembayaran', 'Tunai')->sum(fn($p) => $hitungTotal($p));

        // Total transfer
        $totalTransfer = $penjualans->where('pembayaran', 'Transfer Bank')->sum(fn($p) => $hitungTotal($p));

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
            ->whereIn('tanggal_transaksi', $closingDates) // ⬅️ ambil yang belum di-closing
            ->with(['detailPenjualans', 'detailPenjualanPakets'])
            ->get();

        // 🔹 helper untuk hitung total tiap penjualan
        $hitungTotal = function ($p) {
            $totalProduk = $p->detailPenjualans->sum('total_harga');
            $totalPaket  = $p->detailPenjualanPakets->sum('total_harga');

            $totalJasa = 0;
            if ($p->uuid_jasa) {
                $uuidJasaArray = $p->uuid_jasa; // sudah cast array di model
                if (!empty($uuidJasaArray)) {
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();
                    $totalJasa = $jasa->sum('harga');
                }
            }

            return $totalProduk + $totalPaket + $totalJasa;
        };

        // 🔹 Total penjualan
        $totalPenjualan = $penjualans->sum(fn($p) => $hitungTotal($p));

        // 🔹 Total tunai
        $totalCash = $penjualans
            ->where('pembayaran', 'Tunai')
            ->sum(fn($p) => $hitungTotal($p));

        // 🔹 Total transfer
        $totalTransfer = $penjualans
            ->where('pembayaran', 'Transfer Bank')
            ->sum(fn($p) => $hitungTotal($p));

        $totalNonTunai = $totalTransfer;

        // 🔹 Detail non-tunai
        $detailNonTunai = $penjualans
            ->where('pembayaran', '!=', 'Tunai')
            ->map(function ($p) use ($hitungTotal) {
                return [
                    'jenis'      => $p->pembayaran,
                    'no_invoice' => $p->no_bukti,
                    'nominal'    => $hitungTotal($p),
                ];
            })
            ->values();

        $summaryReport = [
            'tanggal'             => $tanggal,
            'kasir'               => Auth::user()->nama,
            'saldo_awal'          => 0,
            'penjualan_non_tunai' => $totalNonTunai,
            'penjualan_tunai'     => $totalCash,
            'total_penjualan'     => $totalPenjualan,
            'detail_non_tunai'    => $detailNonTunai,
            'total_non_tunai'     => $totalNonTunai,
            'setoran_tunai'       => $totalCash,
            'batal'               => 0
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
        $data = ClosingKasir::latest()->get();
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
        $closing = ClosingKasir::where('uuid', $params)->firstOrFail();

        // cari outlet kasir
        $kasir = KasirOutlet::where('uuid_user', $closing->uuid_kasir_outlet)->firstOrFail();

        // cari user kasir (opsional untuk tampilkan nama)
        $namaKasir = User::where('uuid', $closing->uuid_kasir_outlet)->first();

        // tentukan tanggal transaksi
        $tanggalTransaksi = \Carbon\Carbon::parse($closing->tanggal_closing)
            ->subDay()
            ->format('d-m-Y');

        // ambil penjualan (cek antara tanggal transaksi dan tanggal closing)
        $penjualans = Penjualan::where('uuid_outlet', $kasir->uuid_outlet)
            ->where('created_by', $namaKasir->nama)
            ->where('tanggal_transaksi', $tanggalTransaksi) // kalau closing H+1
            ->orWhere('tanggal_transaksi', $closing->tanggal_closing) // kalau closing di hari yang sama
            ->with(['detailPenjualans', 'detailPenjualanPakets'])
            ->get();

        // Helper hitung total penjualan (produk + paket + jasa)
        $hitungTotal = function ($p) {
            $totalProduk = $p->detailPenjualans->sum('total_harga');
            $totalPaket  = $p->detailPenjualanPakets->sum('total_harga');

            $totalJasa = 0;
            if ($p->uuid_jasa) {
                $uuidJasaArray = $p->uuid_jasa;
                if (!empty($uuidJasaArray)) {
                    $jasa = DB::table('jasas')->whereIn('uuid', $uuidJasaArray)->get();
                    $totalJasa = $jasa->sum('harga');
                }
            }

            return $totalProduk + $totalPaket + $totalJasa;
        };

        // Total penjualan
        $totalPenjualan = $penjualans->sum($hitungTotal);

        // Total per metode pembayaran
        $totalCash     = $penjualans->where('pembayaran', 'Tunai')->sum($hitungTotal);
        $totalTransfer = $penjualans->where('pembayaran', 'Transfer Bank')->sum($hitungTotal);

        // Detail non-tunai
        $detailNonTunai = $penjualans->where('pembayaran', '!=', 'Tunai')->map(function ($p) use ($hitungTotal) {
            return [
                'jenis'      => $p->pembayaran,
                'no_invoice' => $p->no_bukti,
                'nominal'    => $hitungTotal($p),
            ];
        })->values();

        $saldoAwal = 0; // kalau ada tabel saldo awal, bisa ambil dari situ

        // Summary report
        $summaryReport = [
            'tanggal'             => $closing->tanggal_closing,
            'kasir'               => $namaKasir->nama,
            'saldo_awal'          => $saldoAwal,
            'penjualan_non_tunai' => $totalTransfer,
            'penjualan_tunai'     => $totalCash,
            'total_penjualan'     => $totalPenjualan + $saldoAwal,
            'detail_non_tunai'    => $detailNonTunai,
            'total_non_tunai'     => $totalTransfer,
            'setoran_tunai'       => $totalCash,
            'batal'               => 0,
        ];

        $pdf = Pdf::loadView('kasir.sumaryreport.index', [
            'report' => $summaryReport,
            'outlet' => Outlet::where('uuid_user', Auth::user()->uuid)->first()->nama_outlet,
            'alamat' => Outlet::where('uuid_user', Auth::user()->uuid)->first()->alamat
        ]);

        return $pdf->stream('summary-report.pdf');
    }
}

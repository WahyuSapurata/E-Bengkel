<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Costumer;
use App\Models\Jurnal;
use App\Models\KasirOutlet;
use App\Models\Outlet;
use App\Models\Penjualan;
use App\Models\PoOutlet;
use App\Models\Produk;
use App\Models\StatusBarang;
use App\Models\TargetPenjualan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseController
{
    public function landing_page()
    {
        return view('welcome');
    }

    public function dashboard_superadmin()
    {
        $module = 'Dashboard';
        $outlet = Outlet::all();
        $produk = Produk::count();
        $costumer = Costumer::count();

        // $coas = Coa::whereIn('tipe', ['pendapatan', 'beban'])->get();

        // $saldoCoa = Jurnal::select(
        //     'uuid_coa',
        //     DB::raw('SUM(kredit - debit) as saldo_pendapatan'),
        //     DB::raw('SUM(debit - kredit) as saldo_beban')
        // )
        //     ->whereIn('uuid_coa', $coas->pluck('uuid'))
        //     ->groupBy('uuid_coa')
        //     ->get()
        //     ->keyBy('uuid_coa');

        // $total_pendapatan = 0;
        // $total_beban = 0;

        // foreach ($coas as $coa) {
        //     if ($coa->tipe === 'pendapatan') {
        //         $total_pendapatan += $saldoCoa[$coa->uuid]->saldo_pendapatan ?? 0;
        //     }

        //     if ($coa->tipe === 'beban') {
        //         $total_beban += $saldoCoa[$coa->uuid]->saldo_beban ?? 0;
        //     }
        // }

        // $laba_bersih = $total_pendapatan - $total_beban;

        // $coas = Coa::whereIn('tipe', ['pendapatan', 'beban'])->get();

        // // Hitung saldo jurnal
        // $saldoCoa = Jurnal::select(
        //     'uuid_coa',
        //     DB::raw('SUM(kredit - debit) as saldo_pendapatan'),
        //     DB::raw('SUM(debit - kredit) as saldo_beban')
        // )
        //     ->whereIn('uuid_coa', $coas->pluck('uuid'))
        //     ->groupBy('uuid_coa')
        //     ->get()
        //     ->keyBy('uuid_coa');

        // $total_pendapatan = 0;
        // $total_beban = 0;

        // // Loop COA pendapatan & beban
        // foreach ($coas as $coa) {
        //     if ($coa->tipe === 'pendapatan') {
        //         $total_pendapatan += $saldoCoa[$coa->uuid]->saldo_pendapatan ?? 0;
        //     }

        //     if ($coa->tipe === 'beban') {
        //         $total_beban += $saldoCoa[$coa->uuid]->saldo_beban ?? 0;
        //     }
        // }

        // // 🔥 Tambahkan pendapatan jasa service
        // $pendapatanJasa = Penjualan::whereNotNull('uuid_jasa')
        //     ->join('jasas', function ($join) {
        //         $join->whereRaw('JSON_CONTAINS(penjualans.uuid_jasa, JSON_QUOTE(jasas.uuid))');
        //     })
        //     ->sum('jasas.harga');

        // $total_pendapatan += $pendapatanJasa;

        // $laba_bersih = $total_pendapatan - $total_beban;

        // === Hitung total pendapatan ===
        // $produkTotals = DB::table('detail_penjualans')
        //     ->join('penjualans', 'detail_penjualans.uuid_penjualans', '=', 'penjualans.uuid')
        //     ->selectRaw('SUM(detail_penjualans.total_harga) as total_penjualan')
        //     ->first();

        // $paketTotals = DB::table('detail_penjualan_pakets')
        //     ->join('penjualans', 'detail_penjualan_pakets.uuid_penjualans', '=', 'penjualans.uuid')
        //     ->selectRaw('SUM(detail_penjualan_pakets.total_harga) as total_penjualan')
        //     ->first();

        // $jasaTotals = DB::table('penjualans')
        //     ->selectRaw('SUM(jasas.harga) as total_jasa')
        //     ->join(DB::raw(
        //         '(SELECT penjualans.id AS penjualan_id, jt.uuid AS jasa_uuid
        //   FROM penjualans,
        //   JSON_TABLE(penjualans.uuid_jasa, "$[*]" COLUMNS(uuid VARCHAR(255) PATH "$")) AS jt
        // ) AS pj'
        //     ), 'pj.penjualan_id', '=', 'penjualans.id')
        //     ->join('jasas', 'jasas.uuid', '=', 'pj.jasa_uuid')
        //     ->first();

        // $totalProdukPaket = ($produkTotals->total_penjualan ?? 0) + ($paketTotals->total_penjualan ?? 0);
        // $totalJasa        = $jasaTotals->total_jasa ?? 0;

        // $totalPendapatanHitung = $totalProdukPaket + $totalJasa;

        // $hppProduk = DB::table('detail_penjualans')
        //     ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
        //     ->join('penjualans', 'detail_penjualans.uuid_penjualans', '=', 'penjualans.uuid')
        //     ->selectRaw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_hpp')
        //     ->first();

        // $hppPaket = DB::table('detail_penjualan_pakets')
        //     ->join('harga_backup_penjualans', 'detail_penjualan_pakets.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
        //     ->join('penjualans', 'detail_penjualan_pakets.uuid_penjualans', '=', 'penjualans.uuid')
        //     ->selectRaw('SUM(harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty) as total_hpp')
        //     ->first();

        // $totalBebanHPP = ($hppProduk->total_hpp ?? 0) + ($hppPaket->total_hpp ?? 0);
        // $laba_bersih = $totalPendapatanHitung - $totalBebanHPP;
        $bulan = date('m');  // default: bulan ini
        $tahun = date('Y');  // default: tahun ini

        // === Total Penjualan Produk ===
        $produkTotals = DB::table('detail_penjualans')
            ->join('penjualans', 'detail_penjualans.uuid_penjualans', '=', 'penjualans.uuid')
            ->whereRaw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$bulan])
            ->whereRaw('YEAR(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$tahun])
            ->selectRaw('SUM(detail_penjualans.total_harga) as total_penjualan')
            ->first();

        // === Total Penjualan Paket ===
        $paketTotals = DB::table('detail_penjualan_pakets')
            ->join('penjualans', 'detail_penjualan_pakets.uuid_penjualans', '=', 'penjualans.uuid')
            ->whereRaw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$bulan])
            ->whereRaw('YEAR(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$tahun])
            ->selectRaw('SUM(detail_penjualan_pakets.total_harga) as total_penjualan')
            ->first();

        // === Total Jasa ===
        $jasaTotals = DB::table('penjualans')
            ->whereRaw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$bulan])
            ->whereRaw('YEAR(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$tahun])
            ->join(DB::raw(
                '(SELECT penjualans.id AS penjualan_id, jt.uuid AS jasa_uuid
              FROM penjualans,
              JSON_TABLE(penjualans.uuid_jasa, "$[*]" COLUMNS(uuid VARCHAR(255) PATH "$")) AS jt
            ) AS pj'
            ), 'pj.penjualan_id', '=', 'penjualans.id')
            ->join('jasas', 'jasas.uuid', '=', 'pj.jasa_uuid')
            ->selectRaw('SUM(jasas.harga) as total_jasa')
            ->first();

        // === Pendapatan Total ===
        $totalProdukPaket = ($produkTotals->total_penjualan ?? 0) + ($paketTotals->total_penjualan ?? 0);
        $totalJasa        = $jasaTotals->total_jasa ?? 0;
        $totalPendapatan  = $totalProdukPaket + $totalJasa;

        // === HPP Produk ===
        $hppProduk = DB::table('detail_penjualans')
            ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->join('penjualans', 'detail_penjualans.uuid_penjualans', '=', 'penjualans.uuid')
            ->whereRaw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$bulan])
            ->whereRaw('YEAR(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$tahun])
            ->selectRaw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_hpp')
            ->first();

        // === HPP Paket ===
        $hppPaket = DB::table('detail_penjualan_pakets')
            ->join('harga_backup_penjualans', 'detail_penjualan_pakets.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->join('penjualans', 'detail_penjualan_pakets.uuid_penjualans', '=', 'penjualans.uuid')
            ->whereRaw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$bulan])
            ->whereRaw('YEAR(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$tahun])
            ->selectRaw('SUM(harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty) as total_hpp')
            ->first();

        // === Total HPP ===
        $totalBebanHPP = ($hppProduk->total_hpp ?? 0) + ($hppPaket->total_hpp ?? 0);

        // === Laba Bersih ===
        $laba_bersih = $totalPendapatan - $totalBebanHPP;

        $columns = [
            'po_outlets.uuid' => 'uuid',
            'po_outlets.no_po' => 'no_po',
            'po_outlets.tanggal_transaksi' => 'tanggal_transaksi',
            'po_outlets.keterangan' => 'keterangan',
            'po_outlets.created_by' => 'created_by',
            'po_outlets.updated_by' => 'updated_by',
            'po_outlets.status' => 'status',
            'COALESCE(SUM(detail_po_outlets.qty * produks.hrg_modal),0)' => 'total_harga',
            'COALESCE(SUM(detail_po_outlets.qty),0)' => 'total_qty',

            // Array JSON berisi detail produk
            "JSON_ARRAYAGG(
            JSON_OBJECT(
                'uuid_produk', detail_po_outlets.uuid_produk,
                'nama_barang', produks.nama_barang,
                'qty', detail_po_outlets.qty
            )
        )" => 'detail_produk'
        ];

        $selects = [];
        foreach ($columns as $dbCol => $alias) {
            $selects[] = "$dbCol as $alias";
        }

        $query = PoOutlet::where('status', 'draft')->selectRaw(implode(", ", $selects))
            ->leftJoin('detail_po_outlets', 'detail_po_outlets.uuid_po_outlet', '=', 'po_outlets.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_po_outlets.uuid_produk')
            ->groupBy(
                'po_outlets.uuid',
                'po_outlets.no_po',
                'po_outlets.tanggal_transaksi',
                'po_outlets.keterangan',
                'po_outlets.created_by',
                'po_outlets.updated_by',
                'po_outlets.status'
            );

        $data = $query->get();

        $log = StatusBarang::latest()->get();

        return view('dashboard.superadmin', compact('module', 'outlet', 'produk', 'costumer', 'laba_bersih', 'data', 'log'));
    }

    public function dashboard_outlet()
    {
        $user = Auth::user();
        $nama_outlet = Outlet::where('uuid_user', $user->uuid)->first()->nama_outlet;
        $module = 'Dashboard Outlet ' . $nama_outlet;
        $produk = Produk::count();

        // $coas = Coa::whereIn('tipe', ['pendapatan', 'beban'])->get();

        // // Hitung saldo jurnal
        // $saldoCoa = Jurnal::select(
        //     'uuid_coa',
        //     DB::raw('SUM(kredit - debit) as saldo_pendapatan'),
        //     DB::raw('SUM(debit - kredit) as saldo_beban')
        // )
        //     ->whereIn('uuid_coa', $coas->pluck('uuid'))
        //     ->groupBy('uuid_coa')
        //     ->get()
        //     ->keyBy('uuid_coa');

        // $total_pendapatan = 0;
        // $total_beban = 0;

        // // Loop COA pendapatan & beban
        // foreach ($coas as $coa) {
        //     if ($coa->tipe === 'pendapatan') {
        //         $total_pendapatan += $saldoCoa[$coa->uuid]->saldo_pendapatan ?? 0;
        //     }

        //     if ($coa->tipe === 'beban') {
        //         $total_beban += $saldoCoa[$coa->uuid]->saldo_beban ?? 0;
        //     }
        // }

        // // 🔥 Tambahkan pendapatan jasa service
        // $pendapatanJasa = Penjualan::whereNotNull('uuid_jasa')
        //     ->join('jasas', function ($join) {
        //         $join->whereRaw('JSON_CONTAINS(penjualans.uuid_jasa, JSON_QUOTE(jasas.uuid))');
        //     })
        //     ->sum('jasas.harga');

        // $total_pendapatan += $pendapatanJasa;

        // $laba_bersih = $total_pendapatan - $total_beban;

        // === Hitung total pendapatan ===
        // $produkTotals = DB::table('detail_penjualans')
        //     ->join('penjualans', 'detail_penjualans.uuid_penjualans', '=', 'penjualans.uuid')
        //     ->selectRaw('SUM(detail_penjualans.total_harga) as total_penjualan')
        //     ->first();

        // $paketTotals = DB::table('detail_penjualan_pakets')
        //     ->join('penjualans', 'detail_penjualan_pakets.uuid_penjualans', '=', 'penjualans.uuid')
        //     ->selectRaw('SUM(detail_penjualan_pakets.total_harga) as total_penjualan')
        //     ->first();

        // $jasaTotals = DB::table('penjualans')
        //     ->selectRaw('SUM(jasas.harga) as total_jasa')
        //     ->join(DB::raw(
        //         '(SELECT penjualans.id AS penjualan_id, jt.uuid AS jasa_uuid
        //   FROM penjualans,
        //   JSON_TABLE(penjualans.uuid_jasa, "$[*]" COLUMNS(uuid VARCHAR(255) PATH "$")) AS jt
        // ) AS pj'
        //     ), 'pj.penjualan_id', '=', 'penjualans.id')
        //     ->join('jasas', 'jasas.uuid', '=', 'pj.jasa_uuid')
        //     ->first();

        // $totalProdukPaket = ($produkTotals->total_penjualan ?? 0) + ($paketTotals->total_penjualan ?? 0);
        // $totalJasa        = $jasaTotals->total_jasa ?? 0;

        // $totalPendapatanHitung = $totalProdukPaket + $totalJasa;

        // $hppProduk = DB::table('detail_penjualans')
        //     ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
        //     ->join('penjualans', 'detail_penjualans.uuid_penjualans', '=', 'penjualans.uuid')
        //     ->selectRaw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_hpp')
        //     ->first();

        // $hppPaket = DB::table('detail_penjualan_pakets')
        //     ->join('harga_backup_penjualans', 'detail_penjualan_pakets.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
        //     ->join('penjualans', 'detail_penjualan_pakets.uuid_penjualans', '=', 'penjualans.uuid')
        //     ->selectRaw('SUM(harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty) as total_hpp')
        //     ->first();

        // $totalBebanHPP = ($hppProduk->total_hpp ?? 0) + ($hppPaket->total_hpp ?? 0);
        // $laba_bersih = $totalPendapatanHitung - $totalBebanHPP;

        $bulan = date('m');  // default: bulan ini
        $tahun = date('Y');  // default: tahun ini

        // === Total Penjualan Produk ===
        $produkTotals = DB::table('detail_penjualans')
            ->join('penjualans', 'detail_penjualans.uuid_penjualans', '=', 'penjualans.uuid')
            ->whereRaw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$bulan])
            ->whereRaw('YEAR(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$tahun])
            ->selectRaw('SUM(detail_penjualans.total_harga) as total_penjualan')
            ->first();

        // === Total Penjualan Paket ===
        $paketTotals = DB::table('detail_penjualan_pakets')
            ->join('penjualans', 'detail_penjualan_pakets.uuid_penjualans', '=', 'penjualans.uuid')
            ->whereRaw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$bulan])
            ->whereRaw('YEAR(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$tahun])
            ->selectRaw('SUM(detail_penjualan_pakets.total_harga) as total_penjualan')
            ->first();

        // === Total Jasa ===
        $jasaTotals = DB::table('penjualans')
            ->whereRaw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$bulan])
            ->whereRaw('YEAR(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$tahun])
            ->join(DB::raw(
                '(SELECT penjualans.id AS penjualan_id, jt.uuid AS jasa_uuid
              FROM penjualans,
              JSON_TABLE(penjualans.uuid_jasa, "$[*]" COLUMNS(uuid VARCHAR(255) PATH "$")) AS jt
            ) AS pj'
            ), 'pj.penjualan_id', '=', 'penjualans.id')
            ->join('jasas', 'jasas.uuid', '=', 'pj.jasa_uuid')
            ->selectRaw('SUM(jasas.harga) as total_jasa')
            ->first();

        // === Pendapatan Total ===
        $totalProdukPaket = ($produkTotals->total_penjualan ?? 0) + ($paketTotals->total_penjualan ?? 0);
        $totalJasa        = $jasaTotals->total_jasa ?? 0;
        $totalPendapatan  = $totalProdukPaket + $totalJasa;

        // === HPP Produk ===
        $hppProduk = DB::table('detail_penjualans')
            ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->join('penjualans', 'detail_penjualans.uuid_penjualans', '=', 'penjualans.uuid')
            ->whereRaw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$bulan])
            ->whereRaw('YEAR(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$tahun])
            ->selectRaw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_hpp')
            ->first();

        // === HPP Paket ===
        $hppPaket = DB::table('detail_penjualan_pakets')
            ->join('harga_backup_penjualans', 'detail_penjualan_pakets.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->join('penjualans', 'detail_penjualan_pakets.uuid_penjualans', '=', 'penjualans.uuid')
            ->whereRaw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$bulan])
            ->whereRaw('YEAR(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) = ?', [$tahun])
            ->selectRaw('SUM(harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty) as total_hpp')
            ->first();

        // === Total HPP ===
        $totalBebanHPP = ($hppProduk->total_hpp ?? 0) + ($hppPaket->total_hpp ?? 0);

        // === Laba Bersih ===
        $laba_bersih = $totalPendapatan - $totalBebanHPP;

        return view('dashboard.outlet', compact('module', 'produk', 'laba_bersih'));
    }

    public function getProdukUnggul(Request $request)
    {
        $uuidOutlet = $request->uuid_user ?? null;

        // === Produk biasa ===
        $produkQuery = DB::table('detail_penjualans as dp')
            ->join('penjualans as p', 'dp.uuid_penjualans', '=', 'p.uuid')
            ->join('harga_backup_penjualans as hbp', 'dp.uuid', '=', 'hbp.uuid_detail_penjualan')
            ->join('produks as pr', 'dp.uuid_produk', '=', 'pr.uuid')
            ->select(
                'dp.uuid_produk as uuid_produk',
                'pr.nama_barang as nama_barang',
                DB::raw('SUM(dp.qty) as total_terjual'),
                DB::raw('SUM(dp.total_harga - hbp.harga_modal * dp.qty) as total_profit')
            )
            ->groupBy('dp.uuid_produk', 'pr.nama_barang');

        if ($uuidOutlet) {
            $produkQuery->where('p.uuid_outlet', $uuidOutlet);
        }

        // === Produk dari paket hemat ===
        $paketQuery = DB::table('detail_penjualan_pakets as dpp')
            ->join('penjualans as p', 'dpp.uuid_penjualans', '=', 'p.uuid')
            ->join('paket_hemats as ph', 'dpp.uuid_paket', '=', 'ph.uuid')
            ->join('produks as pr', function ($join) {
                // Join ke setiap produk yg ada di JSON uuid_produk paket
                $join->whereRaw("JSON_CONTAINS(ph.uuid_produk, JSON_QUOTE(pr.uuid))");
            })
            ->select(
                'pr.uuid as uuid_produk',
                'pr.nama_barang',
                // qty setiap produk dalam paket = qty paket
                DB::raw('SUM(dpp.qty) as total_terjual'),
                // Profit: (harga jual paket - total_modal paket) / jumlah produk × qty
                DB::raw('SUM((dpp.total_harga - ph.total_modal) / JSON_LENGTH(ph.uuid_produk) * dpp.qty) as total_profit')
            )
            ->groupBy('pr.uuid', 'pr.nama_barang');

        if ($uuidOutlet) {
            $paketQuery->where('p.uuid_outlet', $uuidOutlet);
        }

        // === UNION produk + paket ===
        $union = $produkQuery->unionAll($paketQuery);

        $query = DB::query()->fromSub($union, 'all_produk')
            ->select(
                'uuid_produk',
                'nama_barang',
                DB::raw('SUM(total_terjual) as total_terjual'),
                DB::raw('SUM(total_profit) as total_profit')
            )
            ->groupBy('uuid_produk', 'nama_barang');

        // Ambil Top 5
        $topLaku = (clone $query)->orderByDesc('total_terjual')->get();
        $topUntung = (clone $query)->orderByDesc('total_profit')->get();

        return response()->json([
            'top_laku'   => $topLaku,
            'top_untung' => $topUntung
        ]);
    }

    // public function getPenjualanBulanan(Request $request)
    // {
    //     $year = date('Y');
    //     $uuidOutlet = $request->uuid_user ?? null;

    //     $query = Penjualan::select(
    //         DB::raw('MONTH(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")) as bulan'),
    //         DB::raw('SUM(detail_penjualans.total_harga) as total')
    //     )
    //         ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
    //         ->where(DB::raw('YEAR(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"))'), $year);

    //     if ($uuidOutlet) {
    //         $query->where('penjualans.uuid_outlet', $uuidOutlet);
    //     }

    //     $query->groupBy(DB::raw('MONTH(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"))'))
    //         ->orderBy(DB::raw('MONTH(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"))'));

    //     $result = $query->get();

    //     $data = array_fill(1, 12, 0);
    //     foreach ($result as $row) {
    //         $data[$row->bulan] = (int) $row->total;
    //     }

    //     return response()->json([
    //         'year' => $year,
    //         'uuid_outlet' => $uuidOutlet,
    //         'series' => array_values($data),
    //     ]);
    // }

    public function getPenjualanHarian(Request $request)
    {
        $year  = $request->year ?? date('Y');
        $month = $request->month ?? date('m');
        $uuidOutlet = $request->uuid_user ?? null;

        // --- Penjualan Produk ---
        $produkQuery = Penjualan::select(
            DB::raw('DATE_FORMAT(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y"), "%d-%m-%Y") as tanggal'),
            DB::raw('SUM(detail_penjualans.total_harga) as total_penjualan'),
            DB::raw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal'),
            DB::raw('SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit')
        )
            ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->whereYear(DB::raw('STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")'), $year)
            ->whereMonth(DB::raw('STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")'), $month);

        if ($uuidOutlet) {
            $produkQuery->where('penjualans.uuid_outlet', $uuidOutlet);
        }
        $produkQuery->groupBy('tanggal');

        // --- Penjualan Paket Hemat ---
        $paketQuery = Penjualan::select(
            DB::raw('DATE_FORMAT(STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y"), "%d-%m-%Y") as tanggal'),
            DB::raw('SUM(dpp.total_harga) as total_penjualan'),
            DB::raw('SUM(hbp.harga_modal * dpp.qty) as total_modal'),
            DB::raw('SUM(dpp.total_harga - hbp.harga_modal * dpp.qty) as total_profit')
        )
            ->from('detail_penjualan_pakets as dpp')
            ->join('penjualans as p', 'dpp.uuid_penjualans', '=', 'p.uuid')
            ->join('paket_hemats as ph', 'dpp.uuid_paket', '=', 'ph.uuid')
            ->join('harga_backup_penjualans as hbp', 'dpp.uuid', '=', 'hbp.uuid_detail_penjualan')
            ->whereYear(DB::raw('STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y")'), $year)
            ->whereMonth(DB::raw('STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y")'), $month);

        if ($uuidOutlet) {
            $paketQuery->where('p.uuid_outlet', $uuidOutlet);
        }
        $paketQuery->groupBy('tanggal');

        // --- Gabungkan Produk + Paket ---
        $unionQuery = $produkQuery->unionAll($paketQuery);

        $result = DB::table(DB::raw("({$unionQuery->toSql()}) as combined"))
            ->mergeBindings($unionQuery->getQuery())
            ->select(
                'tanggal',
                DB::raw('SUM(total_penjualan) as total_penjualan'),
                DB::raw('SUM(total_modal) as total_modal'),
                DB::raw('SUM(total_profit) as total_profit'),
                DB::raw('ROUND((SUM(total_profit) / NULLIF(SUM(total_penjualan),0)) * 100, 2) as persen_profit')
            )
            ->groupBy('tanggal')
            ->orderBy(DB::raw('STR_TO_DATE(tanggal, "%d-%m-%Y")'))
            ->get();

        return response()->json([
            'year' => $year,
            'month' => $month,
            'uuid_outlet' => $uuidOutlet,
            'data' => $result
        ]);
    }

    public function getPenjualanBulanan(Request $request)
    {
        $year  = $request->year ?? date('Y');
        $uuidOutlet = $request->uuid_user ?? null;

        // === Penjualan Produk ===
        $produkQuery = Penjualan::select(
            DB::raw('DATE_FORMAT(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y"), "%M") as bulan'),
            DB::raw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) as bulan_angka'),
            DB::raw('SUM(detail_penjualans.total_harga) as total_penjualan'),
            DB::raw('SUM(hbp.harga_modal * detail_penjualans.qty) as total_modal'),
            DB::raw('SUM(detail_penjualans.total_harga) - SUM(hbp.harga_modal * detail_penjualans.qty) as total_profit')
        )
            ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->join('harga_backup_penjualans as hbp', 'detail_penjualans.uuid', '=', 'hbp.uuid_detail_penjualan')
            ->whereYear(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $year);

        if ($uuidOutlet) {
            $produkQuery->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $produkQuery->groupBy('bulan', 'bulan_angka');

        // === Penjualan Paket Hemat ===
        $paketQuery = DB::table('detail_penjualan_pakets as dpp')
            ->select(
                DB::raw('DATE_FORMAT(STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y"), "%M") as bulan'),
                DB::raw('MONTH(STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y")) as bulan_angka'),
                DB::raw('SUM(dpp.total_harga) as total_penjualan'),
                DB::raw('SUM(hbp.harga_modal * dpp.qty) as total_modal'),
                DB::raw('SUM(dpp.total_harga - hbp.harga_modal * dpp.qty) as total_profit')
            )
            ->join('penjualans as p', 'dpp.uuid_penjualans', '=', 'p.uuid')
            ->join('paket_hemats as ph', 'dpp.uuid_paket', '=', 'ph.uuid')
            ->join('harga_backup_penjualans as hbp', 'dpp.uuid', '=', 'hbp.uuid_detail_penjualan')
            ->whereYear(DB::raw('STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y")'), $year);

        if ($uuidOutlet) {
            $paketQuery->where('p.uuid_outlet', $uuidOutlet); // ✅ pakai alias p
        }

        $paketQuery->groupBy('bulan', 'bulan_angka');

        // === Gabungkan Produk + Paket ===
        $unionQuery = $produkQuery->unionAll($paketQuery);

        $result = DB::table(DB::raw("({$unionQuery->toSql()}) as combined"))
            ->mergeBindings($unionQuery->getQuery())
            ->select(
                'bulan',
                'bulan_angka',
                DB::raw('SUM(total_penjualan) as total_penjualan'),
                DB::raw('SUM(total_modal) as total_modal'),
                DB::raw('SUM(total_profit) as total_profit'),
                DB::raw('ROUND((SUM(total_profit) / NULLIF(SUM(total_penjualan),0)) * 100, 2) as persen_profit')
            )
            ->groupBy('bulan', 'bulan_angka')
            ->orderBy('bulan_angka')
            ->get();

        return response()->json([
            'year' => $year,
            'uuid_outlet' => $uuidOutlet,
            'data' => $result
        ]);
    }

    public function getPenjualanHarianJasa(Request $request)
    {
        $year  = $request->year ?? date('Y');
        $month = $request->month ?? date('m');
        $uuidOutlet = $request->uuid_user ?? null;

        // --- Penjualan Produk ---
        $produkQuery = Penjualan::select(
            DB::raw('DATE_FORMAT(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y"), "%d-%m-%Y") as tanggal'),
            DB::raw('SUM(detail_penjualans.total_harga) as total_penjualan'),
            DB::raw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal'),
            DB::raw('SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit'),
            DB::raw('0 as total_jasa')
        )
            ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->whereYear(DB::raw('STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")'), $year)
            ->whereMonth(DB::raw('STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")'), $month);

        if ($uuidOutlet) {
            $produkQuery->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $produkQuery->groupBy('tanggal');

        // --- Penjualan Jasa ---
        $jasaQuery = Penjualan::select(
            DB::raw('DATE_FORMAT(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y"), "%d-%m-%Y") as tanggal'),
            DB::raw('0 as total_penjualan'),
            DB::raw('0 as total_modal'),
            DB::raw('0 as total_profit'),
            DB::raw('SUM(jasas.harga) as total_jasa')
        )
            ->join('jasas', function ($join) {
                $join->whereRaw('JSON_CONTAINS(penjualans.uuid_jasa, JSON_QUOTE(jasas.uuid))');
            })
            ->whereNotNull('penjualans.uuid_jasa')
            ->whereYear(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $year)
            ->whereMonth(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $month);

        if ($uuidOutlet) {
            $jasaQuery->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $jasaQuery->groupBy('tanggal');

        // --- Gabungkan Produk + Jasa ---
        $unionQuery = $produkQuery->unionAll($jasaQuery);

        $result = DB::table(DB::raw("({$unionQuery->toSql()}) as combined"))
            ->mergeBindings($unionQuery->getQuery())
            ->select(
                'tanggal',
                DB::raw('SUM(total_penjualan) as total_penjualan'),
                DB::raw('SUM(total_modal) as total_modal'),
                DB::raw('SUM(total_profit) as total_profit'),
                DB::raw('SUM(total_jasa) as total_jasa'),
                DB::raw('ROUND((SUM(total_profit) / NULLIF(SUM(total_penjualan),0)) * 100, 2) as persen_profit')
            )
            ->groupBy('tanggal')
            ->orderBy(DB::raw('STR_TO_DATE(tanggal, "%d-%m-%Y")'))
            ->get();

        return response()->json([
            'year' => $year,
            'month' => $month,
            'uuid_outlet' => $uuidOutlet,
            'data' => $result
        ]);
    }

    public function getPenjualanBulananJasa(Request $request)
    {
        $year  = $request->year ?? date('Y');
        $uuidOutlet = $request->uuid_user ?? null;

        // --- Penjualan Produk ---
        $produkQuery = Penjualan::select(
            DB::raw('DATE_FORMAT(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"), "%M") as bulan'),
            DB::raw('MONTH(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")) as bulan_angka'),
            DB::raw('SUM(detail_penjualans.total_harga) as total_penjualan'),
            DB::raw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal'),
            DB::raw('SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit'),
            DB::raw('0 as total_jasa')
        )
            ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->whereYear(DB::raw('STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")'), $year);

        if ($uuidOutlet) {
            $produkQuery->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $produkQuery->groupBy('bulan', 'bulan_angka');

        // --- Penjualan Jasa ---
        $jasaQuery = Penjualan::select(
            DB::raw('DATE_FORMAT(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y"), "%M") as bulan'),
            DB::raw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) as bulan_angka'),
            DB::raw('0 as total_penjualan'),
            DB::raw('0 as total_modal'),
            DB::raw('0 as total_profit'),
            DB::raw('SUM(jasas.harga) as total_jasa')
        )
            ->join('jasas', function ($join) {
                $join->whereRaw('JSON_CONTAINS(penjualans.uuid_jasa, JSON_QUOTE(jasas.uuid))');
            })
            ->whereNotNull('penjualans.uuid_jasa')
            ->whereYear(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $year);

        if ($uuidOutlet) {
            $jasaQuery->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $jasaQuery->groupBy('bulan', 'bulan_angka');

        // --- Gabungkan Produk + Jasa ---
        $unionQuery = $produkQuery->unionAll($jasaQuery);

        $result = DB::table(DB::raw("({$unionQuery->toSql()}) as combined"))
            ->mergeBindings($unionQuery->getQuery())
            ->select(
                'bulan',
                'bulan_angka',
                DB::raw('SUM(total_penjualan) as total_penjualan'),
                DB::raw('SUM(total_modal) as total_modal'),
                DB::raw('SUM(total_profit) as total_profit'),
                DB::raw('SUM(total_jasa) as total_jasa'),
                DB::raw('ROUND((SUM(total_profit) / NULLIF(SUM(total_penjualan),0)) * 100, 2) as persen_profit')
            )
            ->groupBy('bulan', 'bulan_angka')
            ->orderBy('bulan_angka')
            ->get();

        return response()->json([
            'year' => $year,
            'uuid_outlet' => $uuidOutlet,
            'data' => $result
        ]);
    }

    public function getPenjualanPerKategori(Request $request)
    {
        $year       = $request->year ?? date('Y');
        $month      = $request->month ?? null;
        $uuidOutlet = $request->uuid_user ?? null;

        // === Produk langsung ===
        $produkQuery = DB::table('penjualans as p')
            ->join('detail_penjualans as dp', 'p.uuid', '=', 'dp.uuid_penjualans')
            ->join('produks as pr', 'dp.uuid_produk', '=', 'pr.uuid')
            ->join('kategoris as k', 'pr.uuid_kategori', '=', 'k.uuid')
            ->leftJoin('harga_backup_penjualans as hbp', 'dp.uuid', '=', 'hbp.uuid_detail_penjualan')
            ->select(
                'k.nama_kategori',
                DB::raw('SUM(dp.total_harga) as total_penjualan'),
                DB::raw('SUM(hbp.harga_modal * dp.qty) as total_modal'),
                DB::raw('SUM(dp.total_harga) - SUM(hbp.harga_modal * dp.qty) as total_profit')
            )
            ->whereYear(DB::raw('STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y")'), $year);

        if ($month) {
            $produkQuery->whereMonth(DB::raw('STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y")'), $month);
        }

        if ($uuidOutlet) {
            $produkQuery->where('p.uuid_outlet', $uuidOutlet);
        }

        $produkQuery->groupBy('k.nama_kategori');

        // === Produk dari Paket Hemat ===
        $paketQuery = DB::table('penjualans as p')
            ->join('detail_penjualan_pakets as dpp', 'p.uuid', '=', 'dpp.uuid_penjualans')
            ->join('paket_hemats as ph', 'dpp.uuid_paket', '=', 'ph.uuid')
            // join ke produk (pakai JSON_TABLE kalau MySQL 8, kalau 5.x harus decode manual di PHP)
            ->join('produks as pr', DB::raw('JSON_UNQUOTE(JSON_EXTRACT(ph.uuid_produk, "$[0]"))'), '=', 'pr.uuid')
            ->join('kategoris as k', 'pr.uuid_kategori', '=', 'k.uuid')
            ->join('harga_backup_penjualans as hbp', 'dpp.uuid', '=', 'hbp.uuid_detail_penjualan')
            ->select(
                'k.nama_kategori',
                DB::raw('SUM(dpp.total_harga) as total_penjualan'),
                DB::raw('SUM(ph.total_modal * dpp.qty) as total_modal'),
                DB::raw('SUM(dpp.total_harga - ph.total_modal * dpp.qty) as total_profit')
            )
            ->whereYear(DB::raw('STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y")'), $year);

        if ($month) {
            $paketQuery->whereMonth(DB::raw('STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y")'), $month);
        }

        if ($uuidOutlet) {
            $paketQuery->where('p.uuid_outlet', $uuidOutlet);
        }

        $paketQuery->groupBy('k.nama_kategori');

        // === Gabungkan Produk + Paket ===
        $unionQuery = $produkQuery->unionAll($paketQuery);

        $result = DB::table(DB::raw("({$unionQuery->toSql()}) as combined"))
            ->mergeBindings($unionQuery)
            ->select(
                'nama_kategori',
                DB::raw('SUM(total_penjualan) as total_penjualan'),
                DB::raw('SUM(total_modal) as total_modal'),
                DB::raw('SUM(total_profit) as total_profit'),
                DB::raw('ROUND(((SUM(total_profit)) / NULLIF(SUM(total_penjualan),0)) * 100, 2) as persen_profit')
            )
            ->groupBy('nama_kategori')
            ->orderBy('total_penjualan', 'desc')
            ->get();

        return response()->json([
            'year'        => $year,
            'month'       => $month,
            'uuid_outlet' => $uuidOutlet,
            'data'        => $result
        ]);
    }


    // public function getPenjualanPerKategoriDenganJasa(Request $request)
    // {
    //     $year  = $request->year ?? date('Y');
    //     $month = $request->month ?? null; // optional filter bulan
    //     $uuidOutlet = $request->uuid_user ?? null;

    //     // --- Penjualan Produk ---
    //     $produkQuery = DB::table('penjualans')
    //         ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
    //         ->join('produks', 'detail_penjualans.uuid_produk', '=', 'produks.uuid')
    //         ->join('kategoris', 'produks.uuid_kategori', '=', 'kategoris.uuid')
    //         ->leftJoin('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
    //         ->select(
    //             'kategoris.nama_kategori as kategori',
    //             DB::raw('SUM(detail_penjualans.total_harga) as total_penjualan'),
    //             DB::raw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal'),
    //             DB::raw('SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit')
    //         )
    //         ->whereYear(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $year);

    //     if ($month) {
    //         $produkQuery->whereMonth(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $month);
    //     }

    //     if ($uuidOutlet) {
    //         $produkQuery->where('penjualans.uuid_outlet', $uuidOutlet);
    //     }

    //     $produkQuery->groupBy('kategoris.nama_kategori');

    //     // --- Penjualan Jasa ---
    //     $jasaQuery = DB::table('penjualans')
    //         ->join('jasas', function ($join) {
    //             $join->whereRaw('JSON_CONTAINS(penjualans.uuid_jasa, JSON_QUOTE(jasas.uuid))');
    //         })
    //         ->select(
    //             DB::raw('"Jasa Service" as kategori'),
    //             DB::raw('SUM(jasas.harga) as total_penjualan'),
    //             DB::raw('0 as total_modal'),
    //             DB::raw('SUM(jasas.harga) as total_profit')
    //         )
    //         ->whereNotNull('penjualans.uuid_jasa')
    //         ->whereYear(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $year);

    //     if ($month) {
    //         $jasaQuery->whereMonth(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $month);
    //     }

    //     if ($uuidOutlet) {
    //         $jasaQuery->where('penjualans.uuid_outlet', $uuidOutlet);
    //     }

    //     $jasaQuery->groupBy(DB::raw('"Jasa Service"'));

    //     // --- Gabungkan Produk + Jasa ---
    //     $union = $produkQuery->unionAll($jasaQuery);

    //     $result = DB::table(DB::raw("({$union->toSql()}) as combined"))
    //         ->mergeBindings($union->getQuery())
    //         ->select(
    //             'kategori',
    //             DB::raw('SUM(total_penjualan) as total_penjualan'),
    //             DB::raw('SUM(total_modal) as total_modal'),
    //             DB::raw('SUM(total_profit) as total_profit')
    //         )
    //         ->groupBy('kategori')
    //         ->orderBy('total_penjualan', 'desc')
    //         ->get();

    //     return response()->json([
    //         'year' => $year,
    //         'month' => $month,
    //         'uuid_outlet' => $uuidOutlet,
    //         'data' => $result
    //     ]);
    // }

    public function getDashboardPenjualanKasir(Request $request)
    {
        $uuidOutlet = $request->uuid_outlet ?? null;

        $kasirsQuery = KasirOutlet::query();
        if ($uuidOutlet) {
            $kasirsQuery->where('uuid_outlet', $uuidOutlet);
        }
        $kasirs = $kasirsQuery->get();

        $dataDashboard = [];

        foreach ($kasirs as $kasir) {
            $namaKasir = User::where('uuid', $kasir->uuid_user)->value('nama') ?? '';

            // === Total Penjualan Produk ===
            $produkTotals = DB::table('penjualans')
                ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
                ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
                ->where('penjualans.created_by', $namaKasir)
                ->where('penjualans.uuid_outlet', $kasir->uuid_outlet)
                ->selectRaw('
                SUM(detail_penjualans.total_harga) as total_penjualan,
                SUM(detail_penjualans.qty) as total_item,
                SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit
            ')
                ->first();

            $totalItem    = $produkTotals->total_item ?? 0;
            $grandTotal   = $produkTotals->total_penjualan ?? 0;
            $totalPenjualan = $produkTotals->total_penjualan ?? 0;
            $profit       = $produkTotals->total_profit ?? 0;

            // === Total Penjualan Paket Hemat ===
            $paketTotals = DB::table('penjualans')
                ->join('detail_penjualan_pakets', 'penjualans.uuid', '=', 'detail_penjualan_pakets.uuid_penjualans')
                ->join('paket_hemats', 'detail_penjualan_pakets.uuid_paket', '=', 'paket_hemats.uuid')
                ->join('harga_backup_penjualans', 'detail_penjualan_pakets.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
                ->where('penjualans.created_by', $namaKasir)
                ->where('penjualans.uuid_outlet', $kasir->uuid_outlet)
                ->selectRaw('
                SUM(detail_penjualan_pakets.total_harga) as total_penjualan,
                SUM(harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty) as total_modal,
                SUM(detail_penjualan_pakets.total_harga - harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty) as total_profit,
                SUM(detail_penjualan_pakets.qty) as total_item
            ')
                ->first();

            $totalItem     += $paketTotals->total_item ?? 0;
            $grandTotal    += $paketTotals->total_penjualan ?? 0;
            $totalPenjualan += $paketTotals->total_penjualan ?? 0;
            $profit        += $paketTotals->total_profit ?? 0;

            // === Total Penjualan Jasa ===
            $totalJasa = DB::table('penjualans')
                ->join('jasas', function ($join) {
                    $join->whereRaw('JSON_CONTAINS(penjualans.uuid_jasa, JSON_QUOTE(jasas.uuid))');
                })
                ->where('penjualans.created_by', $namaKasir)
                ->where('penjualans.uuid_outlet', $kasir->uuid_outlet)
                ->whereNotNull('penjualans.uuid_jasa')
                ->sum('jasas.harga');

            $grandTotal += $totalJasa;

            // === Total transaksi (produk + paket + jasa) ===
            $totalTransaksi = DB::table('penjualans')
                ->where('created_by', $namaKasir)
                ->where('uuid_outlet', $kasir->uuid_outlet)
                ->count();

            $dataDashboard[] = [
                'kasir'          => $namaKasir,
                'uuid_kasir'     => $kasir->uuid,
                'totalTransaksi' => $totalTransaksi,
                'totalItem'      => $totalItem,
                'totalPenjualan' => $totalPenjualan,
                'profit'         => $profit,
                'totalJasa'      => $totalJasa,
                'grandTotal'     => $grandTotal,
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $dataDashboard
        ]);
    }

    public function getTargetPenjualanBulanan(Request $request)
    {
        $year = $request->year ?? date('Y');
        $uuidOutlet = $request->uuid_user ?? null;

        // === Penjualan Produk ===
        $produkQuery = Penjualan::select(
            DB::raw('DATE_FORMAT(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y"), "%M") as bulan'),
            DB::raw('MONTH(STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")) as bulan_angka'),
            DB::raw('SUM(detail_penjualans.total_harga) as total_penjualan'),
            DB::raw('SUM(hbp.harga_modal * detail_penjualans.qty) as total_modal'),
            DB::raw('SUM(detail_penjualans.total_harga) - SUM(hbp.harga_modal * detail_penjualans.qty) as total_profit')
        )
            ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->join('harga_backup_penjualans as hbp', 'detail_penjualans.uuid', '=', 'hbp.uuid_detail_penjualan')
            ->whereYear(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $year);

        if ($uuidOutlet) {
            $produkQuery->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $produkQuery->groupBy('bulan', 'bulan_angka');

        // === Penjualan Paket Hemat ===
        $paketQuery = DB::table('detail_penjualan_pakets as dpp')
            ->select(
                DB::raw('DATE_FORMAT(STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y"), "%M") as bulan'),
                DB::raw('MONTH(STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y")) as bulan_angka'),
                DB::raw('SUM(dpp.total_harga) as total_penjualan'),
                DB::raw('SUM(hbp.harga_modal * dpp.qty) as total_modal'),
                DB::raw('SUM(dpp.total_harga - hbp.harga_modal * dpp.qty) as total_profit')
            )
            ->join('penjualans as p', 'dpp.uuid_penjualans', '=', 'p.uuid')
            ->join('harga_backup_penjualans as hbp', 'dpp.uuid', '=', 'hbp.uuid_detail_penjualan')
            ->whereYear(DB::raw('STR_TO_DATE(p.tanggal_transaksi, "%d-%m-%Y")'), $year);

        if ($uuidOutlet) {
            $paketQuery->where('p.uuid_outlet', $uuidOutlet);
        }

        $paketQuery->groupBy('bulan', 'bulan_angka');

        // === Gabungkan Produk + Paket ===
        $unionQuery = $produkQuery->unionAll($paketQuery);

        $result = DB::table(DB::raw("({$unionQuery->toSql()}) as combined"))
            ->mergeBindings($unionQuery->getQuery())
            ->select(
                'bulan',
                'bulan_angka',
                DB::raw('SUM(total_penjualan) as total_penjualan'),
                DB::raw('SUM(total_modal) as total_modal'),
                DB::raw('SUM(total_profit) as total_profit'),
                DB::raw('ROUND((SUM(total_profit) / NULLIF(SUM(total_penjualan),0)) * 100, 2) as persen_profit')
            )
            ->groupBy('bulan', 'bulan_angka')
            ->orderBy('bulan_angka')
            ->get();

        // === Ambil target per bulan ===
        $targets = TargetPenjualan::where('tahun', $year)
            ->when($uuidOutlet, fn($q) => $q->where('uuid_outlet', $uuidOutlet))
            ->pluck('target', 'bulan'); // ['januari' => 5000000, ...]

        // === Gabungkan hasil dengan target ===
        $data = $result->map(function ($item) use ($targets) {
            $bulanLower = strtolower($item->bulan); // samakan key
            $target = $targets[$bulanLower] ?? 0;
            return [
                'bulan' => $item->bulan,
                'bulan_angka' => $item->bulan_angka,
                'target' => $target,
                'profit' => $item->total_profit,
                'selisih' => $item->total_profit - $target,
                'persen_profit' => $item->persen_profit
            ];
        });

        return response()->json([
            'year' => $year,
            'uuid_outlet' => $uuidOutlet,
            'data' => $data
        ]);
    }


    public function getDashboardPenjualanKasirHarian(Request $request)
    {
        $uuidOutlet = $request->uuid_outlet ?? null;

        $query = DB::table('penjualans')
            ->join('users', 'penjualans.created_by', '=', 'users.nama')
            ->select(
                'penjualans.uuid',
                DB::raw('penjualans.tanggal_transaksi as tanggal'),
                'users.nama as kasir',
                'penjualans.uuid_outlet',
                'penjualans.pembayaran',
                'penjualans.uuid_jasa'
            )
            ->orderBy('penjualans.tanggal_transaksi', 'desc');

        if ($uuidOutlet) {
            $query->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $transaksis = $query->get();

        $rekapTanggal = [];

        foreach ($transaksis as $trx) {
            $tanggalFormatted = $trx->tanggal;
            $keyKasir   = $tanggalFormatted . '_' . $trx->kasir;

            if (!isset($rekapTanggal[$tanggalFormatted])) {
                $rekapTanggal[$tanggalFormatted] = [
                    'tanggal' => $tanggalFormatted,
                    'kasir'   => []
                ];
            }

            if (!isset($rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir])) {
                $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir] = [
                    'nama'          => $trx->kasir,
                    'modal'         => 0,
                    'penjualan'     => 0,
                    'jasa'          => 0,
                    'profit'        => 0,
                    'tunai'         => 0,
                    'non_tunai'     => 0,
                    'sub_total'     => 0,
                    'total'         => 0,
                    'target_profit' => 0,
                    'persentase'    => 0,
                    'selisih'       => 0,
                ];
            }

            // === Produk
            $produkTotals = DB::table('detail_penjualans')
                ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
                ->where('uuid_penjualans', $trx->uuid)
                ->selectRaw('
                SUM(detail_penjualans.total_harga) as total_penjualan,
                SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit,
                SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal
            ')
                ->first();

            // === Paket
            $paketTotals = DB::table('detail_penjualan_pakets')
                ->join('harga_backup_penjualans', 'detail_penjualan_pakets.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
                ->where('uuid_penjualans', $trx->uuid)
                ->selectRaw('
                SUM(detail_penjualan_pakets.total_harga) as total_penjualan,
                SUM(detail_penjualan_pakets.total_harga - harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty) as total_profit,
                SUM(harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty) as total_modal
            ')
                ->first();

            // === Jasa
            $totalJasa = 0;

            if (!empty($trx->uuid_jasa)) {
                // Pastikan uuid_jasa berupa array
                $uuidJasa = is_array($trx->uuid_jasa)
                    ? $trx->uuid_jasa
                    : json_decode($trx->uuid_jasa, true);

                if (!empty($uuidJasa)) {
                    // Hitung frekuensi tiap UUID
                    $counts = array_count_values($uuidJasa);

                    // Ambil semua harga jasa
                    $hargaJasa = DB::table('jasas')
                        ->whereIn('uuid', array_keys($counts))
                        ->pluck('harga', 'uuid');

                    // Hitung total harga jasa, termasuk yang UUID-nya sama
                    foreach ($counts as $uuid => $qty) {
                        $totalJasa += ($hargaJasa[$uuid] ?? 0) * $qty;
                    }
                }
            }

            $modal      = ($produkTotals->total_modal ?? 0) + ($paketTotals->total_modal ?? 0);
            $penjualan  = ($produkTotals->total_penjualan ?? 0) + ($paketTotals->total_penjualan ?? 0);
            $profit     = ($produkTotals->total_profit ?? 0) + ($paketTotals->total_profit ?? 0);
            $sub_total      = $penjualan + $totalJasa;

            // === Update nilai kasir ===
            $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['modal']     += $modal;
            $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['penjualan'] += $penjualan;
            $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['jasa']      += $totalJasa;
            $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['profit']    += $profit + $totalJasa;
            $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['sub_total']     += $sub_total;

            if ($trx->pembayaran === 'Tunai') {
                $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['tunai'] += $sub_total;
            } else {
                $rekapTanggal[$tanggalFormatted]['kasir'][$keyKasir]['non_tunai'] += $sub_total;
            }
        }

        // Hitung target profit + persentase per kasir
        foreach ($rekapTanggal as $tanggal => &$group) {
            // Ambil target profit harian (pastikan format tanggal di DB cocok, misal 21-09-2025)
            $targetProfit = (int) DB::table('target_penjualans')
                ->where('tanggal', $tanggal)
                ->value('target') ?? 0;

            // Hitung total profit semua kasir hari ini
            $totalProfitTanggal = collect($group['kasir'])->sum('profit');
            $total = collect($group['kasir'])->sum('sub_total');

            foreach ($group['kasir'] as &$kasir) {
                $kasir['total'] = $total;
                $kasir['target_profit'] = $targetProfit;
                $kasir['persentase']    = $targetProfit > 0
                    ? round(($totalProfitTanggal / $targetProfit) * 100, 2)
                    : 0;
                // Selisih dihitung dari total profit semua kasir hari itu
                $kasir['selisih']       = $totalProfitTanggal - $targetProfit;
            }

            // Ubah kasir associative jadi array
            $group['kasir'] = array_values($group['kasir']);
        }

        // === Urutkan tanggal terbaru (desc) secara benar ===
        uksort($rekapTanggal, function ($a, $b) {
            return strtotime($b) <=> strtotime($a);
        });

        return response()->json([
            'status' => true,
            'data'   => array_values($rekapTanggal)
        ]);
    }

    public function getDashboardPenjualanKasirBulanan(Request $request)
    {
        $uuidOutlet = $request->uuid_outlet ?? null;

        // Ambil transaksi
        $query = DB::table('penjualans')
            ->join('users', 'penjualans.created_by', '=', 'users.nama')
            ->select(
                'penjualans.uuid',
                'penjualans.tanggal_transaksi as tanggal',
                'users.nama as kasir',
                'penjualans.uuid_outlet',
                'penjualans.pembayaran',
                'penjualans.uuid_jasa'
            )
            ->orderBy('tanggal', 'asc');

        if ($uuidOutlet) {
            $query->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $transaksis = $query->get();

        $rekapBulan = [];

        foreach ($transaksis as $trx) {
            // === Format tanggal (d-m-Y)
            $tanggalFormatted = \Carbon\Carbon::parse($trx->tanggal)->format('d-m-Y');

            // === Buat key bulanan
            $carbonDate = \Carbon\Carbon::createFromFormat('d-m-Y', $tanggalFormatted);
            $bulanKey   = $carbonDate->format('m-Y');
            $bulanLabel = $carbonDate->translatedFormat('F Y'); // Contoh: September 2025

            $keyKasir = $bulanKey . '_' . $trx->kasir;

            if (!isset($rekapBulan[$bulanKey])) {
                $rekapBulan[$bulanKey] = [
                    'bulan' => $bulanLabel,
                    'kasir' => []
                ];
            }

            if (!isset($rekapBulan[$bulanKey]['kasir'][$keyKasir])) {
                $rekapBulan[$bulanKey]['kasir'][$keyKasir] = [
                    'nama'          => $trx->kasir,
                    'modal'         => 0,
                    'penjualan'     => 0,
                    'jasa'          => 0,
                    'profit'        => 0,
                    'tunai'         => 0,
                    'non_tunai'     => 0,
                    'sub_total'     => 0,
                    'total'     => 0,
                    'target_profit' => 0,
                    'persentase'    => 0,
                    'selisih'       => 0,
                ];
            }

            // === Produk
            $produkTotals = DB::table('detail_penjualans')
                ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
                ->where('uuid_penjualans', $trx->uuid)
                ->selectRaw('
                SUM(detail_penjualans.total_harga) as total_penjualan,
                SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit,
                SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal
            ')
                ->first();

            // === Paket
            $paketTotals = DB::table('detail_penjualan_pakets')
                ->join('harga_backup_penjualans', 'detail_penjualan_pakets.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
                ->where('uuid_penjualans', $trx->uuid)
                ->selectRaw('
                SUM(detail_penjualan_pakets.total_harga) as total_penjualan,
                SUM(detail_penjualan_pakets.total_harga - harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty) as total_profit,
                SUM(harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty) as total_modal
            ')
                ->first();

            // === Jasa
            $totalJasa = 0;

            if (!empty($trx->uuid_jasa)) {
                // Pastikan uuid_jasa berupa array
                $uuidJasa = is_array($trx->uuid_jasa)
                    ? $trx->uuid_jasa
                    : json_decode($trx->uuid_jasa, true);

                if (!empty($uuidJasa)) {
                    // Hitung frekuensi tiap UUID
                    $counts = array_count_values($uuidJasa);

                    // Ambil semua harga jasa
                    $hargaJasa = DB::table('jasas')
                        ->whereIn('uuid', array_keys($counts))
                        ->pluck('harga', 'uuid');

                    // Hitung total harga jasa, termasuk yang UUID-nya sama
                    foreach ($counts as $uuid => $qty) {
                        $totalJasa += ($hargaJasa[$uuid] ?? 0) * $qty;
                    }
                }
            }

            $modal      = ($produkTotals->total_modal ?? 0) + ($paketTotals->total_modal ?? 0);
            $penjualan  = ($produkTotals->total_penjualan ?? 0) + ($paketTotals->total_penjualan ?? 0);
            $profit     = ($produkTotals->total_profit ?? 0) + ($paketTotals->total_profit ?? 0);
            $sub_total      = $penjualan + $totalJasa;

            // Update nilai kasir
            $rekapBulan[$bulanKey]['kasir'][$keyKasir]['modal']     += $modal;
            $rekapBulan[$bulanKey]['kasir'][$keyKasir]['penjualan'] += $penjualan;
            $rekapBulan[$bulanKey]['kasir'][$keyKasir]['jasa']      += $totalJasa;
            $rekapBulan[$bulanKey]['kasir'][$keyKasir]['profit']    += $profit + $totalJasa;
            $rekapBulan[$bulanKey]['kasir'][$keyKasir]['sub_total']     += $sub_total;

            if ($trx->pembayaran === 'Tunai') {
                $rekapBulan[$bulanKey]['kasir'][$keyKasir]['tunai'] += $sub_total;
            } else {
                $rekapBulan[$bulanKey]['kasir'][$keyKasir]['non_tunai'] += $sub_total;
            }
        }

        // === Hitung target profit bulanan
        foreach ($rekapBulan as $bulanKey => &$group) {
            $carbon = \Carbon\Carbon::createFromFormat('m-Y', $bulanKey);
            $awalBulan  = $carbon->copy()->startOfMonth()->format('Y-m-d');
            $akhirBulan = $carbon->copy()->endOfMonth()->format('Y-m-d');

            $targetProfit = (int) DB::table('target_penjualans')
                ->whereRaw("STR_TO_DATE(tanggal, '%d-%m-%Y') BETWEEN ? AND ?", [$awalBulan, $akhirBulan])
                ->sum('target');

            // Hitung total profit semua kasir dalam bulan ini
            $totalProfitBulan = collect($group['kasir'])->sum('profit');
            $total = collect($group['kasir'])->sum('sub_total');

            foreach ($group['kasir'] as &$kasir) {
                $kasir['total'] = $total;
                $kasir['target_profit'] = $targetProfit;
                $kasir['persentase']    = $targetProfit > 0 ? round(($totalProfitBulan / $targetProfit) * 100, 2) : 0;
                // Selisih dihitung dari total bulan, bukan per kasir
                $kasir['selisih']       = $totalProfitBulan - $targetProfit;
            }

            // Ubah kasir associative jadi array
            $group['kasir'] = array_values($group['kasir']);
        }


        return response()->json([
            'status' => true,
            'data'   => array_values($rekapBulan)
        ]);
    }
}

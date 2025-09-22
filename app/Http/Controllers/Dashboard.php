<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Costumer;
use App\Models\Jurnal;
use App\Models\Outlet;
use App\Models\Penjualan;
use App\Models\PoOutlet;
use App\Models\Produk;
use App\Models\StatusBarang;
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

        $coas = Coa::whereIn('tipe', ['pendapatan', 'beban'])->get();

        // Hitung saldo jurnal
        $saldoCoa = Jurnal::select(
            'uuid_coa',
            DB::raw('SUM(kredit - debit) as saldo_pendapatan'),
            DB::raw('SUM(debit - kredit) as saldo_beban')
        )
            ->whereIn('uuid_coa', $coas->pluck('uuid'))
            ->groupBy('uuid_coa')
            ->get()
            ->keyBy('uuid_coa');

        $total_pendapatan = 0;
        $total_beban = 0;

        // Loop COA pendapatan & beban
        foreach ($coas as $coa) {
            if ($coa->tipe === 'pendapatan') {
                $total_pendapatan += $saldoCoa[$coa->uuid]->saldo_pendapatan ?? 0;
            }

            if ($coa->tipe === 'beban') {
                $total_beban += $saldoCoa[$coa->uuid]->saldo_beban ?? 0;
            }
        }

        // 🔥 Tambahkan pendapatan jasa service
        $pendapatanJasa = Penjualan::join('jasas', 'penjualans.uuid_jasa', '=', 'jasas.uuid')
            ->whereNotNull('penjualans.uuid_jasa')
            ->sum('jasas.harga');

        $total_pendapatan += $pendapatanJasa;

        $laba_bersih = $total_pendapatan - $total_beban;

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

        $log = StatusBarang::all();

        return view('dashboard.superadmin', compact('module', 'outlet', 'produk', 'costumer', 'laba_bersih', 'data', 'log'));
    }

    public function dashboard_outlet()
    {
        $user = Auth::user();
        $nama_outlet = Outlet::where('uuid_user', $user->uuid)->first()->nama_outlet;
        $module = 'Dashboard Outlet ' . $nama_outlet;
        $produk = Produk::count();

        $coas = Coa::whereIn('tipe', ['pendapatan', 'beban'])->get();

        $total_pendapatan = 0;
        $total_beban = 0;

        foreach ($coas as $coa) {
            if ($coa->tipe === 'pendapatan') {
                $total_pendapatan += Jurnal::where('uuid_outlet', Auth::user()->uuid)->where('uuid_coa', $coa->uuid)
                    ->selectRaw("COALESCE(SUM(kredit - debit),0) as saldo")
                    ->value('saldo');
            }

            if ($coa->tipe === 'beban') {
                $total_beban += Jurnal::where('uuid_outlet', Auth::user()->uuid)->where('uuid_coa', $coa->uuid)
                    ->selectRaw("COALESCE(SUM(debit),0) as saldo")
                    ->value('saldo');
            }
        }

        $laba_bersih = $total_pendapatan - $total_beban;

        return view('dashboard.outlet', compact('module', 'produk', 'laba_bersih'));
    }

    public function getProdukUnggul(Request $request)
    {
        $uuidOutlet = $request->uuid_user ?? null;

        $query = DB::table('detail_penjualans as dp')
            ->join('penjualans as p', 'dp.uuid_penjualans', '=', 'p.uuid')
            ->join('harga_backup_penjualans as hbp', 'dp.uuid', '=', 'hbp.uuid_detail_penjualan')
            ->join('produks as pr', 'dp.uuid_produk', '=', 'pr.uuid')
            ->select(
                'dp.uuid_produk',
                'pr.nama_barang',
                DB::raw('SUM(dp.qty) as total_terjual'),
                DB::raw('SUM(dp.total_harga - hbp.harga_modal * dp.qty) as total_profit')
            )
            ->groupBy('dp.uuid_produk', 'pr.nama_barang');

        if ($uuidOutlet) {
            $query->where('p.uuid_outlet', $uuidOutlet);
        }

        $topLaku = (clone $query)->orderByDesc('total_terjual')->limit(5)->get();
        $topUntung = (clone $query)->orderByDesc('total_profit')->limit(5)->get();

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
        $month = $request->month ?? date('m'); // default bulan sekarang
        $uuidOutlet = $request->uuid_user ?? null;

        $query = Penjualan::select(
            DB::raw('DATE_FORMAT(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"), "%d-%m-%Y") as tanggal'),
            DB::raw('SUM(detail_penjualans.total_harga) as total_penjualan'),
            DB::raw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal'),
            DB::raw('SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit'),
            DB::raw('ROUND(((SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty)) / SUM(detail_penjualans.total_harga)) * 100, 2) as persen_profit')
        )
            ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->where(DB::raw('YEAR(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"))'), $year)
            ->where(DB::raw('MONTH(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"))'), $month);

        if ($uuidOutlet) {
            $query->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $query->groupBy('tanggal')
            ->orderBy(DB::raw('STR_TO_DATE(tanggal, "%d-%m-%Y")'));

        $result = $query->get();

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

        $query = Penjualan::select(
            DB::raw('DATE_FORMAT(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"), "%M") as bulan'), // nama bulan
            DB::raw('MONTH(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")) as bulan_angka'),       // angka bulan (1-12) utk sorting
            DB::raw('SUM(detail_penjualans.total_harga) as total_penjualan'),
            DB::raw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal'),
            DB::raw('SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit'),
            DB::raw('ROUND(((SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty)) / NULLIF(SUM(detail_penjualans.total_harga),0)) * 100, 2) as persen_profit')
        )
            ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->where(DB::raw('YEAR(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"))'), $year);

        if ($uuidOutlet) {
            $query->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $query->groupBy('bulan', 'bulan_angka')
            ->orderBy('bulan_angka');

        $result = $query->get();

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
            ->join('jasas', 'penjualans.uuid_jasa', '=', 'jasas.uuid')
            ->whereNotNull('penjualans.uuid_jasa')
            ->whereYear(DB::raw('STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")'), $year)
            ->whereMonth(DB::raw('STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")'), $month);

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
            DB::raw('DATE_FORMAT(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"), "%M") as bulan'),
            DB::raw('MONTH(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")) as bulan_angka'),
            DB::raw('0 as total_penjualan'),
            DB::raw('0 as total_modal'),
            DB::raw('0 as total_profit'),
            DB::raw('SUM(jasas.harga) as total_jasa')
        )
            ->join('jasas', 'penjualans.uuid_jasa', '=', 'jasas.uuid')
            ->whereNotNull('penjualans.uuid_jasa')
            ->whereYear(DB::raw('STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")'), $year);

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
        $year  = $request->year ?? date('Y');
        $month = $request->month ?? null; // optional filter bulan
        $uuidOutlet = $request->uuid_user ?? null;

        $query = DB::table('penjualans')
            ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->join('produks', 'detail_penjualans.uuid_produk', '=', 'produks.uuid')
            ->join('kategoris', 'produks.uuid_kategori', '=', 'kategoris.uuid')
            ->leftJoin('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->select(
                'kategoris.nama_kategori',
                DB::raw('SUM(detail_penjualans.total_harga) as total_penjualan'),
                DB::raw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal'),
                DB::raw('SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit'),
                DB::raw('ROUND(((SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty)) / SUM(detail_penjualans.total_harga)) * 100, 2) as persen_profit')
            )
            ->whereYear(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $year);

        if ($month) {
            $query->whereMonth(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $month);
        }

        if ($uuidOutlet) {
            $query->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $query->groupBy('kategoris.nama_kategori')
            ->orderBy('total_penjualan', 'desc');

        $result = $query->get();

        return response()->json([
            'year' => $year,
            'month' => $month,
            'uuid_outlet' => $uuidOutlet,
            'data' => $result
        ]);
    }

    public function getPenjualanPerKategoriDenganJasa(Request $request)
    {
        $year  = $request->year ?? date('Y');
        $month = $request->month ?? null; // optional filter bulan
        $uuidOutlet = $request->uuid_user ?? null;

        // --- Penjualan Produk ---
        $produkQuery = DB::table('penjualans')
            ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->join('produks', 'detail_penjualans.uuid_produk', '=', 'produks.uuid')
            ->join('kategoris', 'produks.uuid_kategori', '=', 'kategoris.uuid')
            ->leftJoin('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->select(
                'kategoris.nama_kategori as kategori',
                DB::raw('SUM(detail_penjualans.total_harga) as total_penjualan'),
                DB::raw('SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_modal'),
                DB::raw('SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit')
            )
            ->whereYear(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $year);

        if ($month) {
            $produkQuery->whereMonth(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $month);
        }

        if ($uuidOutlet) {
            $produkQuery->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $produkQuery->groupBy('kategoris.nama_kategori');

        // --- Penjualan Jasa ---
        $jasaQuery = DB::table('penjualans')
            ->join('jasas', 'penjualans.uuid_jasa', '=', 'jasas.uuid')
            ->select(
                DB::raw('"Jasa Service" as kategori'),
                DB::raw('SUM(jasas.harga) as total_penjualan'),
                DB::raw('0 as total_modal'),
                DB::raw('SUM(jasas.harga) as total_profit')
            )
            ->whereNotNull('penjualans.uuid_jasa')
            ->whereYear(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $year);

        if ($month) {
            $jasaQuery->whereMonth(DB::raw('STR_TO_DATE(penjualans.tanggal_transaksi, "%d-%m-%Y")'), $month);
        }

        if ($uuidOutlet) {
            $jasaQuery->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $jasaQuery->groupBy(DB::raw('"Jasa Service"'));

        // --- Gabungkan Produk + Jasa ---
        $union = $produkQuery->unionAll($jasaQuery);

        $result = DB::table(DB::raw("({$union->toSql()}) as combined"))
            ->mergeBindings($union->getQuery())
            ->select(
                'kategori',
                DB::raw('SUM(total_penjualan) as total_penjualan'),
                DB::raw('SUM(total_modal) as total_modal'),
                DB::raw('SUM(total_profit) as total_profit')
            )
            ->groupBy('kategori')
            ->orderBy('total_penjualan', 'desc')
            ->get();

        return response()->json([
            'year' => $year,
            'month' => $month,
            'uuid_outlet' => $uuidOutlet,
            'data' => $result
        ]);
    }
}

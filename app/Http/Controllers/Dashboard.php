<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Costumer;
use App\Models\Jurnal;
use App\Models\Outlet;
use App\Models\Penjualan;
use App\Models\PoOutlet;
use App\Models\Produk;
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

        $coas = Coa::whereIn('tipe', ['pendapatan', 'beban'])->get();

        $total_pendapatan = 0;
        $total_beban = 0;

        foreach ($coas as $coa) {
            if ($coa->tipe === 'pendapatan') {
                $total_pendapatan += Jurnal::where('uuid_coa', $coa->uuid)
                    ->selectRaw("COALESCE(SUM(kredit - debit),0) as saldo")
                    ->value('saldo');
            }

            if ($coa->tipe === 'beban') {
                $total_beban += Jurnal::where('uuid_coa', $coa->uuid)
                    ->selectRaw("COALESCE(SUM(debit),0) as saldo")
                    ->value('saldo');
            }
        }

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

        return view('dashboard.superadmin', compact('module', 'outlet', 'produk', 'costumer', 'laba_bersih', 'data'));
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

    public function getPenjualanBulanan(Request $request)
    {
        $year = date('Y');
        $uuidOutlet = $request->uuid_user ?? null;

        $query = Penjualan::select(
            DB::raw('MONTH(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y")) as bulan'),
            DB::raw('SUM(detail_penjualans.total_harga) as total')
        )
            ->join('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->where(DB::raw('YEAR(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"))'), $year);

        if ($uuidOutlet) {
            $query->where('penjualans.uuid_outlet', $uuidOutlet);
        }

        $query->groupBy(DB::raw('MONTH(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"))'))
            ->orderBy(DB::raw('MONTH(STR_TO_DATE(tanggal_transaksi, "%d-%m-%Y"))'));

        $result = $query->get();

        $data = array_fill(1, 12, 0);
        foreach ($result as $row) {
            $data[$row->bulan] = (int) $row->total;
        }

        return response()->json([
            'year' => $year,
            'uuid_outlet' => $uuidOutlet,
            'series' => array_values($data),
        ]);
    }
}

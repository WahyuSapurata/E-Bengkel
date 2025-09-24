<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LapTransakasi extends Controller
{
    public function index()
    {
        $module = 'Laporan Transaksi';
        $outlet = Outlet::all();
        return view('pages.laptransaksi.index', compact('module', 'outlet'));
    }

    public function get(Request $request)
    {
        $columns = [
            'penjualans.no_bukti',
            'penjualans.tanggal_transaksi',
            'penjualans.pembayaran',
            'penjualans.created_by'
        ];

        $totalData = Penjualan::count();

        $query = Penjualan::query()
            ->select(
                'penjualans.*',
                DB::raw('COALESCE(SUM(detail_penjualans.total_harga),0) as total_penjualan'),
                DB::raw('COALESCE(SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty),0) as total_modal'),
                DB::raw('COALESCE(SUM(jasas.harga),0) as total_jasa') // ✅ total jasa
            )
            ->leftJoin('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->leftJoin('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->leftJoin('jasas', 'penjualans.uuid_jasa', '=', 'jasas.uuid') // join ke tabel jasa
            ->groupBy('penjualans.id')
            ->latest('penjualans.created_at');

        if ($request->filled('uuid_user')) {
            $query->where('penjualans.uuid_outlet', $request->uuid_user);
        }

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $query->count();

        // Pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        // dd($data);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }
}

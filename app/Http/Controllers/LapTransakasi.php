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

    public function index_outlet()
    {
        $module = 'Laporan Transaksi';
        return view('outlet.laptransaksi.index', compact('module'));
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

        $jasaSub = DB::table('penjualans')
            ->select('penjualans.id', DB::raw('SUM(jasas.harga) as total_jasa'))
            ->join('jasas', function ($join) {
                $join->whereRaw('JSON_CONTAINS(penjualans.uuid_jasa, JSON_QUOTE(jasas.uuid))');
            })
            ->groupBy('penjualans.id');

        $query = Penjualan::query()
            ->select(
                'penjualans.*',
                DB::raw('COALESCE(SUM(detail_penjualans.total_harga),0) as total_penjualan'),
                DB::raw('COALESCE(SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty),0) as total_modal'),
                DB::raw('COALESCE(jasa.total_jasa,0) as total_jasa')
            )
            ->leftJoin('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            ->leftJoin('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
            ->leftJoinSub($jasaSub, 'jasa', function ($join) {
                $join->on('penjualans.id', '=', 'jasa.id');
            })
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

        // Hitung total filtered **dengan query yang sama tapi tanpa groupBy** agar count benar
        $totalFiltered = Penjualan::when($request->filled('uuid_user'), function ($q) use ($request) {
            $q->where('uuid_outlet', $request->uuid_user);
        })
            ->when(!empty($request->search['value']), function ($q) use ($request, $columns) {
                $search = $request->search['value'];
                $q->where(function ($q2) use ($search, $columns) {
                    foreach ($columns as $column) {
                        $q2->orWhere($column, 'like', "%{$search}%");
                    }
                });
            })
            ->count();

        // Pagination
        $data = $query->skip($request->start)->take($request->length)->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }
}

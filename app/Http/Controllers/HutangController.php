<?php

namespace App\Http\Controllers;

use App\Helpers\JurnalHelper;
use App\Http\Requests\StoreHutangRequest;
use App\Http\Requests\UpdateHutangRequest;
use App\Models\Coa;
use App\Models\Hutang;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HutangController extends Controller
{
    public function index()
    {
        $module = 'Hutang';
        return view('pages.hutang.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'hutangs.uuid',
            'hutangs.uuid_pembelian',
            'hutangs.jatuh_tempo',
            'hutangs.jumlah_terbayarkan',
            'hutangs.status',
            'pembelians.no_invoice',
            'total_harga', // alias dari SUM
        ];

        $totalData = Hutang::where('status', 'Belum Lunas')->count();

        $query = Hutang::where('status', 'Belum Lunas')->select(
            'hutangs.uuid',
            'hutangs.uuid_pembelian',
            'hutangs.jatuh_tempo',
            'hutangs.jumlah_terbayarkan',
            'hutangs.status',
            'pembelians.no_invoice',
            DB::raw('COALESCE(SUM(detail_pembelians.qty * produks.hrg_modal),0) as total_harga')
        )
            ->leftJoin('pembelians', 'pembelians.uuid', '=', 'hutangs.uuid_pembelian')
            ->leftJoin('detail_pembelians', 'detail_pembelians.uuid_pembelian', '=', 'pembelians.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_pembelians.uuid_produk')
            ->groupBy(
                'hutangs.uuid',
                'hutangs.uuid_pembelian',
                'hutangs.jatuh_tempo',
                'hutangs.jumlah_terbayarkan',
                'hutangs.status',
                'pembelians.no_invoice'
            );

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->orWhere('hutangs.uuid', 'like', "%{$search}%")
                    ->orWhere('hutangs.uuid_pembelian', 'like', "%{$search}%")
                    ->orWhere('hutangs.jatuh_tempo', 'like', "%{$search}%")
                    ->orWhere('hutangs.jumlah_terbayarkan', 'like', "%{$search}%")
                    ->orWhere('hutangs.status', 'like', "%{$search}%")
                    ->orWhere('pembelians.no_invoice', 'like', "%{$search}%");
                // kalau mau search total_harga harus pakai havingRaw
            });
        }

        $totalFiltered = $query->get()->count();

        // Sorting
        if (!empty($request->order)) {
            $orderCol = $columns[$request->order[0]['column']];
            $orderDir = $request->order[0]['dir'];

            if ($orderCol === 'total_harga') {
                $query->orderBy(DB::raw('total_harga'), $orderDir);
            } else {
                $query->orderBy($orderCol, $orderDir);
            }
        } else {
            $query->orderBy('hutangs.created_at', 'desc');
        }

        // Pagination
        if ($request->length != -1) {
            $query->skip($request->start)->take($request->length);
        }

        $data = $query->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function edit($params)
    {
        return response()->json(Hutang::where('uuid', $params)->first());
    }

    public function update(Request $update, $params)
    {
        $kategori = Hutang::where('uuid', $params)->first();
        $pembelian = Pembelian::where('uuid', $kategori->uuid_pembelian)->first();
        $kategori->update([
            'status' => $update->status,
            'jumlah_terbayarkan' => preg_replace('/\D/', '', $update->jumlah_terbayarkan),
        ]);

        $persediaan = Coa::where('nama', 'Persediaan Sparepart')->first();
        $hutang     = Coa::where('nama', 'Hutang Usaha')->first();

        JurnalHelper::create(
            now()->format('d-m-Y'),
            $pembelian->no_invoice,
            'Pembayaran Hutang ' . $pembelian->no_invoice,
            [
                ['uuid_coa' => $hutang->uuid, 'debit' => $kategori->jumlah_terbayarkan],
                ['uuid_coa' => $persediaan->uuid,     'kredit' => $kategori->jumlah_terbayarkan],
            ]
        );

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Hutang::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

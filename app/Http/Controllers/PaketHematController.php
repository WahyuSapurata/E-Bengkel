<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaketHematRequest;
use App\Models\PaketHemat;
use App\Models\Produk;
use App\Models\Suplayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaketHematController extends Controller
{
    public function index()
    {
        $module = 'Paket Hemat';
        $suplayers = Suplayer::select('uuid', 'nama')->get();
        return view('pages.pakethemat.index', compact('module', 'suplayers'));
    }

    public function getProdukBySuplayer($params)
    {
        $produks = Produk::where('uuid_suplayer', $params)
            ->select('uuid', 'nama_barang', 'hrg_modal')
            ->get();

        return response()->json($produks);
    }

    public function getProdukByPaket($uuid)
    {
        $paket = PaketHemat::findOrFail($uuid);

        $produks = Produk::whereIn('uuid', $paket->uuid_produk)->get();

        return response()->json([
            'paket' => $paket,
            'produks' => $produks
        ]);
    }


    public function get(Request $request)
    {
        $columns = [
            'paket_hemats.uuid_produk',
            'paket_hemats.nama_paket',
            'paket_hemats.total_modal',
            'paket_hemats.profit',
            'paket_hemats.keterangan'
        ];

        $totalData = PaketHemat::count();

        $query = PaketHemat::query()
            ->select(
                'paket_hemats.*',
                DB::raw('
                ROUND(
                    (
                        CAST(total_modal AS DECIMAL(15,2))
                        + (CAST(total_modal AS DECIMAL(15,2)) * CAST(profit AS DECIMAL(15,2)) / 100)
                    ) / 1000
                ) * 1000 as harga_jual
            ')
            )
            ->latest('paket_hemats.created_at');

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

        // optional: kalau tetap mau tampilkan detail produk
        $data->transform(function ($item) {
            $uuids = $item->uuid_produk;
            $item->produk_list = Produk::whereIn('uuid', $uuids)
                ->get(['uuid', 'nama_barang']);
            return $item;
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function store(PaketHematRequest $request)
    {
        PaketHemat::create([
            'uuid_produk' => $request->uuid_produk,
            'nama_paket' => $request->nama_paket,
            'total_modal' => preg_replace('/\D/', '', $request->total_modal),
            'profit' => $request->profit,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        $paket = PaketHemat::where('uuid', $params)->firstOrFail();

        // Ambil produk dari tabel produks berdasarkan array uuid_produk
        $produks = Produk::whereIn('uuid', $paket->uuid_produk)->get(['uuid', 'nama_barang', 'hrg_modal']);

        return response()->json([
            'uuid'        => $paket->uuid,
            'nama_paket'  => $paket->nama_paket,
            'total_modal' => $paket->total_modal,
            'profit'      => $paket->profit,
            'keterangan'  => $paket->keterangan,
            'uuid_produk' => $produks, // langsung kirim objek produk (uuid + nama_barang)
        ]);
    }

    public function update(PaketHematRequest $update, $params)
    {
        $paket = PaketHemat::where('uuid', $params)->first();
        $paket->update([
            'uuid_produk' => $update->uuid_produk,
            'nama_paket' => $update->nama_paket,
            'total_modal' => preg_replace('/\D/', '', $update->total_modal),
            'profit' => $update->profit,
            'keterangan' => $update->keterangan,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        PaketHemat::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

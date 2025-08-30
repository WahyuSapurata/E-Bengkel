<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends BaseController
{
    public function index()
    {
        $module = 'Kategori';
        return view('pages.kategori.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = ['uuid', 'kode', 'nama_kategori'];

        $totalData = Kategori::count();

        $query = Kategori::select('uuid', 'kode', 'nama_kategori');

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

        // Sorting
        if ($request->order) {
            $orderCol = $columns[$request->order[0]['column']];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest();
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        // Format response DataTables
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function store(StoreKategoriRequest $request)
    {
        // Format tanggal -> DDMMYY
        $today = now()->format('dmy');
        $prefix = "K-" . $today;

        // Cari PO terakhir di hari ini
        $lastKategori = Kategori::whereDate('created_at', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastKategori) {
            // Ambil angka urut terakhir (setelah prefix)
            $lastNumber = intval(substr($lastKategori->kode, strrpos($lastKategori->kode, '-') + 1));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $kode = $prefix . "-" . $nextNumber;

        Kategori::create([
            'kode' => $kode,
            'nama_kategori' => $request->nama_kategori
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Kategori::where('uuid', $params)->first());
    }

    public function update(UpdateKategoriRequest $update, $params)
    {
        $kategori = Kategori::where('uuid', $params)->first();
        $kategori->update([
            'nama_kategori' => $update->nama_kategori
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Kategori::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

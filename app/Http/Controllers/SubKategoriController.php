<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubKategoriRequest;
use App\Http\Requests\UpdateSubKategoriRequest;
use App\Models\SubKategori;
use Illuminate\Http\Request;

class SubKategoriController extends Controller
{
    public function index()
    {
        $module = 'Sub Kategori';
        return view('pages.subkategori.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'kode',
            'nama_sub_kategori',
        ];

        $totalData = SubKategori::count();

        $query = SubKategori::select($columns);

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

    public function store(StoreSubKategoriRequest $request)
    {
        SubKategori::create([
            'kode' => $request->kode,
            'nama_sub_kategori' => $request->nama_sub_kategori
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(SubKategori::where('uuid', $params)->first());
    }

    public function update(UpdateSubKategoriRequest $update, $params)
    {
        $kategori = SubKategori::where('uuid', $params)->first();
        $kategori->update([
            'kode' => $update->kode,
            'nama_sub_kategori' => $update->nama_sub_kategori
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        SubKategori::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

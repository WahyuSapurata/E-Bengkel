<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJasaRequest;
use App\Http\Requests\UpdateJasaRequest;
use App\Models\Jasa;
use Illuminate\Http\Request;

class JasaController extends Controller
{
    public function index()
    {
        $module = 'Jasa';
        return view('pages.jasa.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'kode',
            'nama',
            'deskripsi',
            'harga',
        ];

        $totalData = Jasa::count();

        $query = Jasa::select(
            'uuid',
            'kode',
            'nama',
            'deskripsi',
            'harga',
        );

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

    public function store(StoreJasaRequest $request)
    {
        Jasa::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'harga' => preg_replace('/\D/', '', $request->harga),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Jasa::where('uuid', $params)->first());
    }

    public function update(UpdateJasaRequest $update, $params)
    {
        $kategori = Jasa::where('uuid', $params)->first();
        $kategori->update([
            'kode' => $update->kode,
            'nama' => $update->nama,
            'deskripsi' => $update->deskripsi,
            'harga' => preg_replace('/\D/', '', $update->harga),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Jasa::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

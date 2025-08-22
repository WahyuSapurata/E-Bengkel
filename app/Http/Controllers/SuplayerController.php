<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuplayerRequest;
use App\Http\Requests\UpdateSuplayerRequest;
use App\Models\Suplayer;
use Illuminate\Http\Request;

class SuplayerController extends Controller
{
    public function index()
    {
        $module = 'Suplayer';
        return view('pages.suplayer.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'kode',
            'nama',
            'alamat',
            'telepon',
            'kota',
        ];

        $totalData = Suplayer::count();

        $query = Suplayer::select(
            'uuid',
            'kode',
            'nama',
            'alamat',
            'telepon',
            'kota',
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

    public function store(StoreSuplayerRequest $request)
    {
        Suplayer::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
            'kota' => $request->kota,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Suplayer::where('uuid', $params)->first());
    }

    public function update(UpdateSuplayerRequest $update, $params)
    {
        $kategori = Suplayer::where('uuid', $params)->first();
        $kategori->update([
            'kode' => $update->kode,
            'nama' => $update->nama,
            'alamat' => $update->alamat,
            'telepon' => $update->telepon,
            'kota' => $update->kota,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Suplayer::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

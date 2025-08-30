<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKaryawanRequest;
use App\Http\Requests\UpdateKaryawanRequest;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        $module = 'Karyawan';
        return view('pages.karyawan.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'nama',
            'alamat',
            'nomor',
            'jabatan',
        ];

        $totalData = Karyawan::count();

        $query = Karyawan::select(
            'uuid',
            'nama',
            'alamat',
            'nomor',
            'jabatan',
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

    public function store(StoreKaryawanRequest $request)
    {
        Karyawan::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'nomor' => $request->nomor,
            'jabatan' => $request->jabatan,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Karyawan::where('uuid', $params)->first());
    }

    public function update(StoreKaryawanRequest $update, $params)
    {
        $kategori = Karyawan::where('uuid', $params)->first();
        $kategori->update([
            'nama' => $update->nama,
            'alamat' => $update->alamat,
            'nomor' => $update->nomor,
            'jabatan' => $update->jabatan,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Karyawan::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

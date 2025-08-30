<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGajiRequest;
use App\Http\Requests\UpdateGajiRequest;
use App\Models\Gaji;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class GajiController extends Controller
{
    public function index()
    {
        $module = 'Gaji';
        $karyawan = Karyawan::select('uuid', 'nama')->get();
        return view('pages.gaji.index', compact('module', 'karyawan'));
    }

    public function get(Request $request)
    {
        $columns = [
            'gajis.uuid',
            'gajis.uuid_karyawan',
            'karyawans.nama',
            'gajis.tanggal',
            'gajis.jumlah',
        ];

        $totalData = Gaji::count();

        $query = Gaji::select(
            'gajis.uuid',
            'gajis.uuid_karyawan',
            'karyawans.nama as nama_karyawan',
            'gajis.tanggal',
            'gajis.jumlah'
        )
            ->leftJoin('karyawans', 'karyawans.uuid', '=', 'gajis.uuid_karyawan');

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
            $query->latest('gajis.tanggal');
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

    public function store(StoreGajiRequest $request)
    {
        Gaji::create([
            'uuid_karyawan' => $request->uuid_karyawan,
            'tanggal' => $request->tanggal,
            'jumlah' => preg_replace('/\D/', '', $request->jumlah),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Gaji::where('uuid', $params)->first());
    }

    public function update(StoreGajiRequest $update, $params)
    {
        $kategori = Gaji::where('uuid', $params)->first();
        $kategori->update([
            'uuid_karyawan' => $update->uuid_karyawan,
            'tanggal' => $update->tanggal,
            'jumlah' => preg_replace('/\D/', '', $update->jumlah),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Gaji::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

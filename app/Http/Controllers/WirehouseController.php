<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWirehouseRequest;
use App\Http\Requests\UpdateWirehouseRequest;
use App\Models\Outlet;
use App\Models\Wirehouse;
use Illuminate\Http\Request;

class WirehouseController extends BaseController
{
    public function index()
    {
        $module = 'Wirehouse';
        $outlet = Outlet::all();
        return view('admin.wirehouse.index', compact('module', 'outlet'));
    }

    public function get(Request $request)
    {
        $columns = [
            'wirehouses.uuid',
            'wirehouses.uuid_user',
            'outlets.nama_outlet', // tambahkan kolom ini
            'wirehouses.tipe',
            'wirehouses.lokasi',
            'wirehouses.keterangan',
        ];

        $totalData = Wirehouse::count();

        // 🔗 JOIN ke tabel outlets berdasarkan uuid_user
        $query = Wirehouse::select(
            'wirehouses.uuid',
            'wirehouses.uuid_user',
            'outlets.nama_outlet', // ambil nama outlet
            'wirehouses.tipe',
            'wirehouses.lokasi',
            'wirehouses.keterangan',
        )
            ->leftJoin('outlets', 'outlets.uuid_user', '=', 'wirehouses.uuid_user');

        // 🔍 Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $query->count();

        // 🔽 Sorting
        if ($request->order) {
            $orderCol = $columns[$request->order[0]['column']];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest('wirehouses.created_at');
        }

        // 📄 Pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        // 🔧 Format response DataTables
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    public function store(StoreWirehouseRequest $request)
    {
        Wirehouse::create([
            'uuid_user' => $request->uuid_user,
            'tipe' => $request->tipe,
            'lokasi' => $request->lokasi,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Wirehouse::where('uuid', $params)->first());
    }

    public function update(StoreWirehouseRequest $update, $params)
    {
        $kategori = Wirehouse::where('uuid', $params)->first();
        $kategori->update([
            'uuid_user' => $update->uuid_user,
            'tipe' => $update->tipe,
            'lokasi' => $update->lokasi,
            'keterangan' => $update->keterangan,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Wirehouse::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

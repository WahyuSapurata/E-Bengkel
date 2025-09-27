<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTargetPenjualanRequest;
use App\Models\TargetPenjualan;
use Illuminate\Http\Request;

class TargetPenjualanController extends Controller
{
    public function index()
    {
        $module = 'Target Penjualan Bulanan';
        return view('pages.targetpenjualan.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'tanggal',
            'target',
            'keterangan',
        ];

        $totalData = TargetPenjualan::count();

        $query = TargetPenjualan::select(
            'uuid',
            'tanggal',
            'target',
            'keterangan',
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

    public function store(StoreTargetPenjualanRequest $request)
    {
        TargetPenjualan::create([
            'tanggal' => $request->tanggal,
            'target' =>  preg_replace('/\D/', '', $request->target),
            'keterangan' => $request->keterangan,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(TargetPenjualan::where('uuid', $params)->first());
    }

    public function update(StoreTargetPenjualanRequest $update, $params)
    {
        $kategori = TargetPenjualan::where('uuid', $params)->first();
        $kategori->update([
            'tanggal' => $update->tanggal,
            'target' =>  preg_replace('/\D/', '', $update->target),
            'keterangan' => $update->keterangan,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        TargetPenjualan::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

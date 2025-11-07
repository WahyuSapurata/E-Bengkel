<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCostumerRequest;
use App\Http\Requests\UpdateCostumerRequest;
use App\Models\Costumer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CostumerController extends Controller
{
    public function index()
    {
        $module = 'Costumer';
        return view('pages.costumer.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'nama',
            'alamat',
            'nomor',
            'plat',
            'uuid_penjualan',
        ];

        $totalData = Costumer::where('uuid_outlet', Auth::user()->uuid)->count();

        // Ambil data beserta relasi outlet dan penjualan
        $query = Costumer::where('uuid_outlet', Auth::user()->uuid)->with(['penjualan', 'outlet'])->select($columns);

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
            $query->latest();
        }

        // 📄 Pagination
        $query->skip($request->start)->take($request->length);

        // 🔹 Ambil data dan ubah formatnya
        $data = $query->get()->map(function ($item) {
            return [
                'uuid' => $item->uuid,
                'nama' => $item->nama,
                'alamat' => $item->alamat,
                'nomor' => $item->nomor,
                'plat' => $item->plat,
                'bukti' => $item->penjualan->no_bukti,
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }


    public function store(StoreCostumerRequest $request)
    {
        Costumer::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'nomor' => $request->nomor,
            'plat' => $request->plat,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Costumer::where('uuid', $params)->first());
    }

    public function update(StoreCostumerRequest $update, $params)
    {
        $kategori = Costumer::where('uuid', $params)->first();
        $kategori->update([
            'nama' => $update->nama,
            'alamat' => $update->alamat,
            'nomor' => $update->nomor,
            'plat' => $update->plat,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Costumer::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }

    public function getCostumerByPlat(Request $request)
    {
        $plat = $request->input('plat');
        $costumer = Costumer::where('plat', $plat)->first();

        if ($costumer) {
            return response()->json([
                'status' => 'success',
                'data' => $costumer
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Costumer not found'
        ], 404);
    }
}

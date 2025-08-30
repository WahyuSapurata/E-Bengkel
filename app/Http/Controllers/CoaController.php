<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCoaRequest;
use App\Http\Requests\UpdateCoaRequest;
use App\Models\Coa;
use Illuminate\Http\Request;

class CoaController extends Controller
{
    public function index()
    {
        $module = 'Daftar Akun';
        return view('pages.akun.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'kode',
            'nama',
            'tipe',
        ];

        $totalData = Coa::count();

        $query = Coa::select(
            'uuid',
            'kode',
            'nama',
            'tipe',
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

    public function store(StoreCoaRequest $request)
    {
        // Format tanggal -> DDMMYY
        // $today = now()->format('dmy');
        // $prefix = "S-" . $today;

        // // Cari PO terakhir di hari ini
        // $lastCoa = Coa::whereDate('created_at', now()->toDateString())
        //     ->orderBy('created_at', 'desc')
        //     ->first();

        // if ($lastCoa) {
        //     // Ambil angka urut terakhir (setelah prefix)
        //     $lastNumber = intval(substr($lastCoa->kode, strrpos($lastCoa->kode, '-') + 1));
        //     $nextNumber = $lastNumber + 1;
        // } else {
        //     $nextNumber = 1;
        // }

        // $kode = $prefix . "-" . $nextNumber;

        Coa::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'tipe' => $request->tipe,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Coa::where('uuid', $params)->first());
    }

    public function update(UpdateCoaRequest $update, $params)
    {
        $kategori = Coa::where('uuid', $params)->first();
        $kategori->update([
            'kode' => $update->kode,
            'nama' => $update->nama,
            'tipe' => $update->tipe,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Coa::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

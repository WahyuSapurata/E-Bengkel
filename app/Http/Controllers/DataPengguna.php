<?php

namespace App\Http\Controllers;

use App\Http\Requests\DataPenggunaRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DataPengguna extends Controller
{
    public function index()
    {
        $module = 'Data Pengguna';
        return view('pages.datapengguna.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'nama',
            'username',
            'password_hash',
        ];

        $totalData = User::where('role', 'superadmin')->count();

        $query = User::where('role', 'superadmin')->select($columns);

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

    public function store(DataPenggunaRequest $request)
    {
        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password_hash' => $request->password_hash,
            'password' => Hash::make($request->password_hash),
            'role' => 'superadmin',
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(User::where('uuid', $params)->first());
    }

    public function update(DataPenggunaRequest $update, $params)
    {
        $kategori = User::where('uuid', $params)->first();
        $kategori->update([
            'nama' => $update->nama,
            'username' => $update->username,
            'password_hash' => $update->password_hash,
            'password' => Hash::make($update->password_hash),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        User::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOutletRequest;
use App\Http\Requests\UpdateOutletRequest;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OutletController extends Controller
{
    public function index()
    {
        $module = 'Outlet';
        return view('pages.outlet.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'outlets.uuid',
            'outlets.uuid_user',
            'outlets.nama_outlet',
            'outlets.alamat',
            'outlets.telepon',
            'users.nama',
            'users.username',
            'users.password_hash',
        ];

        $totalData = Outlet::count();

        $query = Outlet::select($columns)
            ->leftJoin('users', 'users.uuid', '=', 'outlets.uuid_user');

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

    public function store(StoreOutletRequest $request)
    {
        $user = User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password_hash' => $request->password_hash,
            'password' => Hash::make($request->password_hash),
            'role' => 'outlet',
        ]);

        Outlet::create([
            'uuid_user' => $user->uuid,
            'nama_outlet' => $request->nama_outlet,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        $data = Outlet::where('uuid', $params)->firstOrFail();
        $user = User::where('uuid', $data->uuid_user)->first();

        // Tambahkan data user ke outlet
        if ($user) {
            $data->nama = $user->nama;
            $data->username = $user->username;
            $data->password_hash = $user->password_hash;
        }

        return response()->json($data);
    }

    public function update(StoreOutletRequest $request, $params)
    {
        $outlet = Outlet::where('uuid', $params)->firstOrFail();
        $outlet->update([
            'nama_outlet' => $request->nama_outlet,
            'alamat'      => $request->alamat,
            'telepon'     => $request->telepon,
        ]);

        // Kalau mau update user juga (opsional)
        if ($outlet->uuid_user) {
            User::where('uuid', $outlet->uuid_user)->update([
                'nama'     => $request->nama,
                'username' => $request->username,
                'password_hash' => $request->password_hash,
                // password update kalau ada
                'password' => $request->password_hash ? Hash::make($request->password_hash) : DB::raw('password'),
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $outlet = Outlet::where('uuid', $params)->firstOrFail();

        DB::transaction(function () use ($outlet) {
            if ($outlet->uuid_user) {
                User::where('uuid', $outlet->uuid_user)->delete();
            }
            $outlet->delete();
        });

        return response()->json(['status' => 'success']);
    }
}

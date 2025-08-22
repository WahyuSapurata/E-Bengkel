<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKasirOutletRequest;
use App\Http\Requests\UpdateKasirOutletRequest;
use App\Models\KasirOutlet;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KasirOutletController extends Controller
{
    public function index()
    {
        $module = 'Kasir Outlet';
        return view('outlet.kasiroutlet.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'kasir_outlets.uuid',
            'kasir_outlets.uuid_user',
            'kasir_outlets.alamat',
            'kasir_outlets.telepon',
            'users.nama',
            'users.username',
            'users.password_hash',
        ];

        $totalData = KasirOutlet::count();

        $query = KasirOutlet::select($columns)
            ->leftJoin('users', 'users.uuid', '=', 'kasir_outlets.uuid_user');

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

    public function store(StoreKasirOutletRequest $request)
    {
        $user = User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password_hash' => $request->password_hash,
            'password' => Hash::make($request->password_hash),
            'role' => 'kasir',
        ]);

        KasirOutlet::create([
            'uuid_user' => $user->uuid,
            'uuid_outlet' => Auth::user()->uuid,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        $data = KasirOutlet::where('uuid', $params)->firstOrFail();
        $user = User::where('uuid', $data->uuid_user)->first();

        // Tambahkan data user ke outlet
        if ($user) {
            $data->nama = $user->nama;
            $data->username = $user->username;
            $data->password_hash = $user->password_hash;
        }

        return response()->json($data);
    }

    public function update(StoreKasirOutletRequest $request, $params)
    {
        $kasiroutlet = KasirOutlet::where('uuid', $params)->firstOrFail();
        $kasiroutlet->update([
            'alamat'      => $request->alamat,
            'telepon'     => $request->telepon,
        ]);

        // Kalau mau update user juga (opsional)
        if ($kasiroutlet->uuid_user) {
            User::where('uuid', $kasiroutlet->uuid_user)->update([
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
        $kasiroutlet = KasirOutlet::where('uuid', $params)->firstOrFail();

        DB::transaction(function () use ($kasiroutlet) {
            if ($kasiroutlet->uuid_user) {
                User::where('uuid', $kasiroutlet->uuid_user)->delete();
            }
            $kasiroutlet->delete();
        });

        return response()->json(['status' => 'success']);
    }
}

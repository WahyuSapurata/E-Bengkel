<?php

namespace App\Http\Controllers;

use App\Helpers\JurnalHelper;
use App\Http\Requests\StoreBiayaRequest;
use App\Http\Requests\UpdateBiayaRequest;
use App\Models\Biaya;
use App\Models\Coa;
use Illuminate\Http\Request;

class BiayaController extends Controller
{
    public function index()
    {
        $module = 'Biaya Lain-lain';
        $coa = Coa::select('uuid', 'nama')->get();
        return view('pages.biaya.index', compact('module', 'coa'));
    }

    public function get(Request $request)
    {
        $columns = [
            'biayas.uuid',
            'biayas.uuid_coa',
            'coas.nama',
            'biayas.tanggal',
            'biayas.deskripsi',
            'biayas.jumlah',
        ];

        $totalData = Biaya::count();

        $query = Biaya::select(
            'biayas.uuid',
            'biayas.uuid_coa',
            'coas.nama as nama_coa',
            'biayas.tanggal',
            'biayas.deskripsi',
            'biayas.jumlah'
        )
            ->leftJoin('coas', 'coas.uuid', '=', 'biayas.uuid_coa');

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
            $query->latest('biayas.tanggal');
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

    public function store(StoreBiayaRequest $request)
    {
        // Simpan biaya
        $biaya = Biaya::create([
            'uuid_coa'   => $request->uuid_coa,
            'tanggal'    => $request->tanggal,
            'deskripsi'  => $request->deskripsi,
            'jumlah'     => preg_replace('/\D/', '', $request->jumlah),
        ]);

        // Ambil akun COA
        $akunBiaya = Coa::where('uuid', $request->uuid_coa)->firstOrFail();
        $kas       = Coa::where('nama', 'Kas')->firstOrFail();

        // Hitung biaya ke berapa (buat nomor bukti)
        $biayaKe = Biaya::where('uuid_coa', $request->uuid_coa)->count();

        // Nomor bukti → BBIAYA-001-NAMA-AKUN
        $namaAkun   = strtoupper(str_replace(' ', '', $akunBiaya->nama));
        $nomorBukti = 'BIAYA-' . str_pad($biayaKe, 3, '0', STR_PAD_LEFT) . '-' . $namaAkun;

        // Catat ke jurnal
        JurnalHelper::create(
            $request->tanggal,
            $nomorBukti,
            'Pembayaran Biaya: ' . $request->deskripsi,
            [
                ['uuid_coa' => $akunBiaya->uuid, 'debit'  => $biaya->jumlah],
                ['uuid_coa' => $kas->uuid,       'kredit' => $biaya->jumlah],
            ]
        );

        return response()->json([
            'status'      => 'success',
            'nomor_bukti' => $nomorBukti,
            'akun_biaya'  => $akunBiaya->nama,
            'jumlah'      => $biaya->jumlah,
        ]);
    }

    public function edit($params)
    {
        return response()->json(Biaya::where('uuid', $params)->first());
    }

    public function update(StoreBiayaRequest $update, $params)
    {
        $kategori = Biaya::where('uuid', $params)->first();
        $kategori->update([
            'uuid_coa' => $update->uuid_coa,
            'tanggal' => $update->tanggal,
            'deskripsi' => $update->deskripsi,
            'jumlah' => preg_replace('/\D/', '', $update->jumlah),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Biaya::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

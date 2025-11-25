<?php

namespace App\Http\Controllers;

use App\Helpers\JurnalHelper;
use App\Http\Requests\StorePemindahanDanaRequest;
use App\Http\Requests\UpdatePemindahanDanaRequest;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\PemindahanDana;
use Illuminate\Http\Request;

class PemindahanDanaController extends BaseController
{
    public function index()
    {
        $module = 'Pemindahan Dana';
        $coa = Coa::where('tipe', 'aset')
            ->where(function ($q) {
                $q->where('nama', 'like', '%Kas%')
                    ->orWhere('nama', 'like', 'Bank%');
            })
            ->where('nama', '!=', 'Kas Outlet') // pengecualian
            ->get(['kode', 'nama']);
        return view('admin.pemindahan.index', compact('module', 'coa'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'no_bukti',
            'deskripsi',
            'sumber_dana',
            'tujuan_dana',
            'nominal',
        ];

        $totalData = PemindahanDana::count();

        $query = PemindahanDana::select($columns);

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

    public function store(StorePemindahanDanaRequest $request)
    {

        // ============================
        // GENERATE NO_BUKTI UNIK
        // ============================
        $today = now()->format('dmY'); // YYYYMMDD
        $prefix = "PMD-" . $today . "-";

        // Ambil nomor terakhir hari ini
        $last = PemindahanDana::where('no_bukti', 'like', $prefix . '%')
            ->orderBy('no_bukti', 'desc')
            ->first();

        if ($last) {
            // Ambil 4 digit terakhir, increment
            $lastNumber = (int) substr($last->no_bukti, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = "0001";
        }

        $no_bukti = $prefix . $newNumber;

        $nominal = preg_replace('/\D/', '', $request->nominal);

        $pemindahan = PemindahanDana::create([
            'no_bukti'     => $no_bukti,
            'deskripsi'   => $request->deskripsi,
            'sumber_dana' => $request->sumber_dana,
            'tujuan_dana' => $request->tujuan_dana,
            'nominal'     => $nominal,
        ]);

        // COA sumber dana (yang berkurang)
        $coaSumber = Coa::where('nama', $request->sumber_dana)->firstOrFail();

        // COA tujuan dana (yang bertambah)
        $coaTujuan = Coa::where('nama', $request->tujuan_dana)->firstOrFail();

        JurnalHelper::create(
            now()->format('d-m-Y'),
            $no_bukti,  // ini no_ref, harus PAKAI no_bukti!
            'Pemindahan ' . $request->sumber_dana . ' ke ' . $request->tujuan_dana . ': ' . $request->deskripsi,
            [
                // Tujuan dana bertambah → DEBIT
                [
                    'uuid_coa' => $coaTujuan->uuid,
                    'debit'    => $nominal,     // <--- perbaikan
                ],

                // Sumber dana berkurang → KREDIT
                [
                    'uuid_coa' => $coaSumber->uuid,
                    'kredit'   => $nominal,     // <--- perbaikan
                ]
            ]
        );

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        $data = PemindahanDana::where('uuid', $params)->firstOrFail();
        return response()->json($data);
    }

    public function update(StorePemindahanDanaRequest $request, $params)
    {
        $pemindahan = PemindahanDana::where('uuid', $params)->firstOrFail();

        // simpan no_bukti lama → dipakai sebagai no_ref jurnal
        $no_bukti = $pemindahan->no_bukti;

        $nominal = preg_replace('/\D/', '', $request->nominal);

        // ============================
        // UPDATE DATA
        // ============================
        $pemindahan->update([
            'deskripsi'   => $request->deskripsi,
            'sumber_dana' => $request->sumber_dana,
            'tujuan_dana' => $request->tujuan_dana,
            'nominal'     => $nominal,
        ]);

        // ============================
        // HAPUS JURNAL LAMA (berdasarkan no_ref)
        // ============================
        Jurnal::where('no_ref', $no_bukti)->delete();

        // ============================
        // AMBIL COA SUMBER & TUJUAN BARU
        // ============================
        $coaSumber = Coa::where('nama', $request->sumber_dana)->firstOrFail();
        $coaTujuan = Coa::where('nama', $request->tujuan_dana)->firstOrFail();

        // ============================
        // BUAT JURNAL BARU (format sama seperti STORE)
        // ============================
        JurnalHelper::create(
            now()->format('d-m-Y'),
            $no_bukti,  // WAJIB pakai no_bukti, agar delete/update presisi
            'Pemindahan ' . $request->sumber_dana . ' ke ' . $request->tujuan_dana . ': ' . $request->deskripsi,
            [
                // Tujuan dana bertambah → DEBIT
                [
                    'uuid_coa' => $coaTujuan->uuid,
                    'debit'    => $nominal,
                ],

                // Sumber dana berkurang → KREDIT
                [
                    'uuid_coa' => $coaSumber->uuid,
                    'kredit'   => $nominal,
                ],
            ]
        );

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $pemidahan = PemindahanDana::where('uuid', $params)->firstOrFail();

        // ============================
        // HAPUS JURNAL BERDASARKAN NO_BUKTI
        // ============================
        Jurnal::where('no_ref', $pemidahan->no_bukti)->delete();

        // ============================
        // HAPUS DATA PENGELUARAN
        // ============================
        $pemidahan->delete();

        return response()->json(['status' => 'success']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\JurnalHelper;
use App\Http\Requests\StorePengeluaranRequest;
use App\Http\Requests\UpdatePengeluaranRequest;
use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;

use function Symfony\Component\Clock\now;

class PengeluaranController extends BaseController
{
    public function index()
    {
        $module = 'Pengeluaran';
        $coa = Coa::where('tipe', 'aset')
            ->where(function ($q) {
                $q->where('nama', 'like', '%Kas%')
                    ->orWhere('nama', 'like', 'Bank%');
            })
            ->where('nama', '!=', 'Kas Outlet') // pengecualian
            ->get(['kode', 'nama']);
        return view('admin.pengeluaran.index', compact('module', 'coa'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'no_bukti',
            'deskripsi',
            'sumber_dana',
            'nominal',
        ];

        $totalData = Pengeluaran::count();

        $query = Pengeluaran::select($columns);

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

    public function store(StorePengeluaranRequest $request)
    {
        // ============================
        // GENERATE NO_BUKTI UNIK
        // ============================
        $today = now()->format('dmY'); // YYYYMMDD
        $prefix = "PGL-" . $today . "-";

        // Ambil nomor terakhir hari ini
        $last = Pengeluaran::where('no_bukti', 'like', $prefix . '%')
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

        // ============================
        // SIMPAN PENGELUARAN
        // ============================
        $pengeluaran = Pengeluaran::create([
            'no_bukti'     => $no_bukti,
            'deskripsi'    => $request->deskripsi,
            'sumber_dana'  => $request->sumber_dana,
            'nominal'      => preg_replace('/\D/', '', $request->nominal),
        ]);

        // ============================
        // GET COA SUMBER DANA
        // ============================
        $coa = Coa::where('nama', $request->sumber_dana)->firstOrFail();

        // ============================
        // JURNAL (DEBIT & KREDIT)
        // ============================
        JurnalHelper::create(
            now()->format('d-m-Y'),
            $no_bukti, // invoice atau nomor referensi jurnal
            'Pengeluaran ' . $request->sumber_dana . ': ' . $request->deskripsi,
            [
                // Sumber dana (Kas / Bank) = KREDIT
                ['uuid_coa' => $coa->uuid, 'kredit' => $pengeluaran->nominal],
            ]
        );

        return response()->json(['status' => 'success', 'no_bukti' => $no_bukti]);
    }

    public function edit($params)
    {
        $data = Pengeluaran::where('uuid', $params)->firstOrFail();
        return response()->json($data);
    }

    public function update(StorePengeluaranRequest $request, $params)
    {
        $pengeluaran = Pengeluaran::where('uuid', $params)->firstOrFail();

        // ============================
        // SIMPAN DATA LAMA
        // ============================
        $oldNoBukti   = $pengeluaran->no_bukti;

        // ============================
        // UPDATE DATA PENGELUARAN
        // ============================
        $pengeluaran->update([
            'deskripsi'   => $request->deskripsi,
            'sumber_dana' => $request->sumber_dana,
            'nominal'     => preg_replace('/\D/', '', $request->nominal),
        ]);

        $nominal = preg_replace('/\D/', '', $request->nominal);

        // ============================
        // HAPUS JURNAL LAMA BERDASARKAN NO_BUKTI
        // ============================
        Jurnal::where('no_ref', $oldNoBukti)->delete();

        // ============================
        // GET COA SUMBER DANA
        // ============================
        $coa = Coa::where('nama', $request->sumber_dana)->firstOrFail();

        // ============================
        // BUAT JURNAL BARU (SAMA FORMAT DENGAN STORE)
        // ============================
        JurnalHelper::create(
            now()->format('d-m-Y'),        // tanggal jurnal
            $oldNoBukti,                   // no referensi
            'Pengeluaran ' . $request->sumber_dana . ': ' . $request->deskripsi,
            [
                // Sumber dana (Kas / Bank) = KREDIT
                ['uuid_coa' => $coa->uuid, 'kredit' => $nominal],
            ]
        );

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $pengeluaran = Pengeluaran::where('uuid', $params)->firstOrFail();

        // ============================
        // HAPUS JURNAL BERDASARKAN NO_BUKTI
        // ============================
        Jurnal::where('no_ref', $pengeluaran->no_bukti)->delete();

        // ============================
        // HAPUS DATA PENGELUARAN
        // ============================
        $pengeluaran->delete();

        return response()->json(['status' => 'success']);
    }
}

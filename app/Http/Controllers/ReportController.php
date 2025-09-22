<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\Penjualan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function vw_jurnal_umum()
    {
        $module = 'Jurnal Umum';
        return view('pages.jurnalumum.index', compact('module'));
    }

    public function get_jurnal_umum(Request $request)
    {
        $columns = [
            'jurnals.tanggal',
            'jurnals.ref',
            'jurnals.deskripsi',
            'coas.nama',
            'jurnals.debit',
            'jurnals.kredit',
        ];

        // Filter tanggal default bulan berjalan
        $tanggal_awal  = $request->get('tanggal_awal', Carbon::now()->startOfMonth()->format('d-m-Y'));
        $tanggal_akhir = $request->get('tanggal_akhir', Carbon::now()->endOfMonth()->format('d-m-Y'));

        // Total data tanpa filter pencarian
        $totalData = Jurnal::join('coas', 'coas.uuid', '=', 'jurnals.uuid_coa')
            ->whereRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [
                $tanggal_awal,
                $tanggal_akhir
            ])
            ->count();

        $query = Jurnal::join('coas', 'coas.uuid', '=', 'jurnals.uuid_coa')
            ->select(
                'jurnals.uuid',
                'jurnals.tanggal',
                'jurnals.ref',
                'jurnals.deskripsi',
                'coas.nama as nama_akun',
                'jurnals.debit',
                'jurnals.kredit'
            )
            ->whereRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [
                $tanggal_awal,
                $tanggal_akhir
            ]);

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
            if ($orderCol === 'jurnals.tanggal') {
                $query->orderByRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') $orderDir");
            } else {
                $query->orderBy($orderCol, $orderDir);
            }
        } else {
            $query->orderByRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') ASC");
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

    public function vw_buku_besar()
    {
        $module = 'Buku Besar';
        $coas = Coa::all();
        return view('pages.bukubesar.index', compact('module', 'coas'));
    }

    public function get_buku_besar(Request $request)
    {
        $columns = [
            'jurnals.tanggal',
            'jurnals.ref',
            'jurnals.deskripsi',
            'coas.nama',
            'jurnals.debit',
            'jurnals.kredit',
        ];

        // Filter tanggal default bulan berjalan
        // ambil dari request (format m-d-Y)
        $tanggal_awal  = $request->get('tanggal_awal', Carbon::now()->startOfMonth()->format('d-m-Y'));
        $tanggal_akhir = $request->get('tanggal_akhir', Carbon::now()->endOfMonth()->format('d-m-Y'));

        $uuid_coa = $request->get('uuid_coa'); // akun yang dipilih

        $totalData = Jurnal::join('coas', 'coas.uuid', '=', 'jurnals.uuid_coa')
            ->where('jurnals.uuid_coa', $uuid_coa)
            ->whereRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [
                $tanggal_awal,
                $tanggal_akhir
            ])
            ->count();

        $query = Jurnal::join('coas', 'coas.uuid', '=', 'jurnals.uuid_coa')
            ->select(
                'jurnals.uuid',
                'jurnals.tanggal',
                'jurnals.ref',
                'jurnals.deskripsi',
                'coas.nama as nama_akun',
                'jurnals.debit',
                'jurnals.kredit'
            )
            ->where('jurnals.uuid_coa', $uuid_coa)
            ->whereRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [
                $tanggal_awal,
                $tanggal_akhir
            ]);

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
            if ($orderCol === 'jurnals.tanggal') {
                $query->orderByRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') $orderDir");
            } else {
                $query->orderBy($orderCol, $orderDir);
            }
        } else {
            $query->orderByRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') ASC");
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        // Tambah saldo berjalan
        $saldo = 0;
        $result = [];
        foreach ($data as $row) {
            $saldo += ($row->debit - $row->kredit);
            $result[] = [
                'tanggal'   => $row->tanggal,
                'ref'       => $row->ref,
                'deskripsi' => $row->deskripsi,
                'nama_akun' => $row->nama_akun,
                'debit'     => $row->debit,
                'kredit'    => $row->kredit,
                'saldo'     => $saldo,
            ];
        }

        // Format response DataTables
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $result
        ]);
    }

    public function vw_neraca()
    {
        $module = 'Neraca';
        return view('pages.neraca.index', compact('module'));
    }

    public function get_neraca(Request $request)
    {
        $tanggal_akhir = $request->get('tanggal_akhir', Carbon::now()->endOfMonth()->format('d-m-Y'));

        $coas = Coa::all();

        $saldos = Jurnal::selectRaw("uuid_coa, COALESCE(SUM(debit - kredit),0) as saldo")
            ->whereRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') <= STR_TO_DATE(?, '%d-%m-%Y')", [$tanggal_akhir])
            ->groupBy('uuid_coa')
            ->pluck('saldo', 'uuid_coa');

        $result = [
            'aset' => [],
            'kewajiban' => [],
            'modal' => [],
        ];

        $laba_berjalan = 0;

        foreach ($coas as $coa) {
            $saldo = $saldos[$coa->uuid] ?? 0;
            if ($saldo == 0) continue;

            switch ($coa->tipe) {
                case 'aset':
                    $result['aset'][] = [
                        'kode' => $coa->kode,
                        'nama' => $coa->nama,
                        'saldo' => $saldo,
                    ];
                    break;
                case 'kewajiban':
                    $result['kewajiban'][] = [
                        'kode' => $coa->kode,
                        'nama' => $coa->nama,
                        'saldo' => $saldo,
                    ];
                    break;
                case 'modal':
                    $result['modal'][] = [
                        'kode' => $coa->kode,
                        'nama' => $coa->nama,
                        'saldo' => $saldo,
                    ];
                    break;
                case 'pendapatan':
                    $laba_berjalan += $saldo;
                    break;
                case 'beban':
                    $laba_berjalan -= $saldo;
                    break;
            }
        }

        if ($laba_berjalan != 0) {
            $result['modal'][] = [
                'kode' => '309',
                'nama' => 'Laba Ditahan',
                'saldo' => $laba_berjalan,
            ];
        }

        return response()->json([
            'tanggal' => $tanggal_akhir,
            'data' => $result,
            'total_aset' => collect($result['aset'])->sum('saldo'),
            'total_kewajiban' => collect($result['kewajiban'])->sum('saldo'),
            'total_modal' => collect($result['modal'])->sum('saldo'),
            'total_passiva' => collect($result['kewajiban'])->sum('saldo') + collect($result['modal'])->sum('saldo'),
        ]);
    }

    public function vw_laba_rugi()
    {
        $module = 'Laba Rugi';
        return view('pages.labarugi.index', compact('module'));
    }

    // public function get_laba_rugi(Request $request)
    // {
    //     $tanggal_awal = $request->get('tanggal_awal', Carbon::now()->startOfMonth()->format('d-m-Y'));
    //     $tanggal_akhir = $request->get('tanggal_akhir', Carbon::now()->endOfMonth()->format('d-m-Y'));

    //     $coas = Coa::whereIn('tipe', ['pendapatan', 'beban'])->get();

    //     $pendapatan = [];
    //     $beban = [];
    //     $total_pendapatan = 0;
    //     $total_beban = 0;

    //     foreach ($coas as $coa) {
    //         $saldo = Jurnal::where('uuid_coa', $coa->uuid)
    //             ->whereRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [$tanggal_awal, $tanggal_akhir])
    //             ->selectRaw("COALESCE(SUM(kredit - debit),0) as saldo")
    //             ->value('saldo');

    //         if ($saldo == 0) continue;

    //         if ($coa->tipe === 'pendapatan') {
    //             $pendapatan[] = [
    //                 'kode' => $coa->kode,
    //                 'nama' => $coa->nama,
    //                 'total' => $saldo
    //             ];
    //             $total_pendapatan += $saldo;
    //         }

    //         if ($coa->tipe === 'beban') {
    //             $saldo_beban = Jurnal::where('uuid_coa', $coa->uuid)
    //                 ->whereRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [$tanggal_awal, $tanggal_akhir])
    //                 ->selectRaw("COALESCE(SUM(debit),0) as saldo")
    //                 ->value('saldo');

    //             $beban[] = [
    //                 'kode' => $coa->kode,
    //                 'nama' => $coa->nama,
    //                 'total' => $saldo_beban
    //             ];
    //             $total_beban += $saldo_beban;
    //         }
    //     }

    //     $laba_bersih = $total_pendapatan - $total_beban;

    //     return response()->json([
    //         'tanggal_awal' => $tanggal_awal,
    //         'tanggal_akhir' => $tanggal_akhir,
    //         'pendapatan' => $pendapatan,
    //         'beban' => $beban,
    //         'total_pendapatan' => $total_pendapatan,
    //         'total_beban' => $total_beban,
    //         'laba_bersih' => $laba_bersih
    //     ]);
    // }

    public function get_laba_rugi(Request $request)
    {
        $tanggal_awal = $request->get('tanggal_awal', Carbon::now()->startOfMonth()->format('d-m-Y'));
        $tanggal_akhir = $request->get('tanggal_akhir', Carbon::now()->endOfMonth()->format('d-m-Y'));

        $coas = Coa::whereIn('tipe', ['pendapatan', 'beban'])->get();

        $pendapatan = [];
        $beban = [];
        $total_pendapatan = 0;
        $total_beban = 0;

        foreach ($coas as $coa) {
            $saldo = Jurnal::where('uuid_coa', $coa->uuid)
                ->whereRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [$tanggal_awal, $tanggal_akhir])
                ->selectRaw("COALESCE(SUM(kredit - debit),0) as saldo")
                ->value('saldo');

            if ($saldo == 0) continue;

            if ($coa->tipe === 'pendapatan') {
                $pendapatan[] = [
                    'kode' => $coa->kode,
                    'nama' => $coa->nama,
                    'total' => $saldo
                ];
                $total_pendapatan += $saldo;
            }

            if ($coa->tipe === 'beban') {
                $saldo_beban = Jurnal::where('uuid_coa', $coa->uuid)
                    ->whereRaw("STR_TO_DATE(jurnals.tanggal, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [$tanggal_awal, $tanggal_akhir])
                    ->selectRaw("COALESCE(SUM(debit),0) as saldo")
                    ->value('saldo');

                $beban[] = [
                    'kode' => $coa->kode,
                    'nama' => $coa->nama,
                    'total' => $saldo_beban
                ];
                $total_beban += $saldo_beban;
            }
        }

        // 🔥 Tambahkan Pendapatan Jasa Service dari penjualan
        $pendapatan_jasa = Penjualan::join('jasas', 'penjualans.uuid_jasa', '=', 'jasas.uuid')
            ->whereNotNull('penjualans.uuid_jasa')
            ->whereRaw("STR_TO_DATE(penjualans.tanggal_transaksi, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [$tanggal_awal, $tanggal_akhir])
            ->sum('jasas.harga');

        if ($pendapatan_jasa > 0) {
            $pendapatan[] = [
                'kode' => '-',
                'nama' => 'Pendapatan Jasa Service',
                'total' => $pendapatan_jasa
            ];
            $total_pendapatan += $pendapatan_jasa;
        }

        $laba_bersih = $total_pendapatan - $total_beban;

        return response()->json([
            'tanggal_awal' => $tanggal_awal,
            'tanggal_akhir' => $tanggal_akhir,
            'pendapatan' => $pendapatan,
            'beban' => $beban,
            'total_pendapatan' => $total_pendapatan,
            'total_beban' => $total_beban,
            'laba_bersih' => $laba_bersih
        ]);
    }
}

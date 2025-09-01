<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function vw_jurnal_umum()
    {
        $module = 'Jurnal Umum';
        return view('pages.jurnalumum.index', compact('module'));
    }

    public function get_jurnal_umum(Request $request)
    {
        $tanggal_awal  = $request->get('tanggal_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggal_akhir = $request->get('tanggal_akhir', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $jurnalUmum = Jurnal::join('coas', 'coas.uuid', '=', 'jurnals.uuid_coa')
            ->select(
                'jurnals.*',
                'coas.nama as nama_akun'
            )
            ->whereBetween('jurnals.tanggal', [$tanggal_awal, $tanggal_akhir])
            ->orderBy('jurnals.tanggal')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $jurnalUmum,
        ]);
    }
}

<?php

namespace App\Helpers;

use App\Models\Jurnal;
use App\Models\Coa;
use Illuminate\Support\Facades\DB;

class ReportHelper
{
    /**
     * Buku Besar per akun
     */
    public static function bukuBesar($uuid_coa, $tanggal_awal = null, $tanggal_akhir = null)
    {
        $query = Jurnal::where('uuid_coa', $uuid_coa);

        if ($tanggal_awal && $tanggal_akhir) {
            $query->whereBetween('tanggal', [$tanggal_awal, $tanggal_akhir]);
        }

        return $query->orderBy('tanggal')->get();
    }

    /**
     * Neraca (Balance Sheet)
     */
    public static function neraca($tanggal_awal = null, $tanggal_akhir = null)
    {
        $query = Jurnal::join('coas', 'coas.uuid', '=', 'jurnals.uuid_coa')
            ->select(
                'coas.nama',
                'coas.tipe',
                DB::raw('SUM(jurnals.debit - jurnals.kredit) as saldo_aset'),
                DB::raw('SUM(jurnals.kredit - jurnals.debit) as saldo_pasiva')
            )
            ->groupBy('coas.uuid', 'coas.nama', 'coas.tipe');

        if ($tanggal_awal && $tanggal_akhir) {
            $query->whereBetween('jurnals.tanggal', [$tanggal_awal, $tanggal_akhir]);
        }

        $data = $query->get();

        return [
            'aset'      => $data->where('tipe', 'aset')->map(fn($d) => [
                'nama' => $d->nama,
                'saldo' => $d->saldo_aset,
            ]),
            'kewajiban' => $data->where('tipe', 'kewajiban')->map(fn($d) => [
                'nama' => $d->nama,
                'saldo' => $d->saldo_pasiva,
            ]),
            'modal'     => $data->where('tipe', 'modal')->map(fn($d) => [
                'nama' => $d->nama,
                'saldo' => $d->saldo_pasiva,
            ]),
        ];
    }

    /**
     * Laba Rugi (Income Statement)
     */
    public static function labaRugi($tanggal_awal = null, $tanggal_akhir = null)
    {
        $query = Jurnal::join('coas', 'coas.uuid', '=', 'jurnals.uuid_coa')
            ->select(
                'coas.nama',
                'coas.tipe',
                DB::raw('SUM(jurnals.debit) as total_debit'),
                DB::raw('SUM(jurnals.kredit) as total_kredit')
            )
            ->groupBy('coas.uuid', 'coas.nama', 'coas.tipe');

        if ($tanggal_awal && $tanggal_akhir) {
            $query->whereBetween('jurnals.tanggal', [$tanggal_awal, $tanggal_akhir]);
        }

        $data = $query->get();

        $pendapatan = $data->where('tipe', 'pendapatan')->sum('total_kredit');
        $beban      = $data->where('tipe', 'beban')->sum('total_debit');

        return [
            'pendapatan' => $data->where('tipe', 'pendapatan')->map(fn($d) => [
                'nama' => $d->nama,
                'total' => $d->total_kredit,
            ]),
            'beban' => $data->where('tipe', 'beban')->map(fn($d) => [
                'nama' => $d->nama,
                'total' => $d->total_debit,
            ]),
            'laba_bersih' => $pendapatan - $beban,
        ];
    }
}

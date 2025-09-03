<?php

namespace App\Helpers;

use App\Models\Jurnal;
use Carbon\Carbon;

class JurnalHelper
{
    public static function create($tanggal, $uuid_outlet, $ref, $deskripsi, $entries = [])
    {
        foreach ($entries as $entry) {
            Jurnal::create([
                'tanggal' => $tanggal ?? Carbon::now(),
                'uuid_outlet' => $uuid_outlet,
                'ref' => $ref,
                'deskripsi' => $deskripsi,
                'uuid_coa' => $entry['uuid_coa'],
                'debit' => $entry['debit'] ?? 0,
                'kredit' => $entry['kredit'] ?? 0,
            ]);
        }
    }
}

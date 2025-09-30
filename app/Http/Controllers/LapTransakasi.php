<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LapTransakasi extends Controller
{
    public function index()
    {
        $module = 'Laporan Transaksi';
        $outlet = Outlet::all();
        return view('pages.laptransaksi.index', compact('module', 'outlet'));
    }

    public function index_outlet()
    {
        $module = 'Laporan Transaksi';
        return view('outlet.laptransaksi.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'penjualans.no_bukti',
            'penjualans.tanggal_transaksi',
            'penjualans.pembayaran',
            'penjualans.created_by',
            'penjualans.created_at'
        ];

        $totalData = Penjualan::count();

        // Subquery jasa
        $jasaSub = DB::table('penjualans')
            ->select('penjualans.id', DB::raw('SUM(jasas.harga) as total_jasa'))
            ->join(DB::raw(
                // Menggunakan JSON_TABLE untuk split uuid_jasa menjadi baris
                '(SELECT penjualans.id AS penjualan_id, jt.uuid AS jasa_uuid
          FROM penjualans,
          JSON_TABLE(penjualans.uuid_jasa, "$[*]" COLUMNS(uuid VARCHAR(255) PATH "$")) AS jt
        ) AS pj'
            ), 'pj.penjualan_id', '=', 'penjualans.id')
            ->join('jasas', 'jasas.uuid', '=', 'pj.jasa_uuid')
            ->groupBy('penjualans.id');

        $query = Penjualan::query()
            ->select(
                'penjualans.*',
                DB::raw('
                COALESCE(SUM(detail_penjualans.total_harga),0)
                + COALESCE(SUM(detail_penjualan_pakets.total_harga),0)
                as total_penjualan
            '),
                DB::raw('
                COALESCE(SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty),0)
                + COALESCE(SUM(harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty),0)
                as total_modal
            '),
                DB::raw('COALESCE(jasa.total_jasa,0) as total_jasa')
            )
            // join detail produk
            ->leftJoin('detail_penjualans', 'penjualans.uuid', '=', 'detail_penjualans.uuid_penjualans')
            // join detail paket
            ->leftJoin('detail_penjualan_pakets', 'penjualans.uuid', '=', 'detail_penjualan_pakets.uuid_penjualans')
            // join harga backup (satu tabel untuk semua detail)
            ->leftJoin('harga_backup_penjualans', function ($join) {
                $join->on('harga_backup_penjualans.uuid_detail_penjualan', '=', 'detail_penjualans.uuid')
                    ->orOn('harga_backup_penjualans.uuid_detail_penjualan', '=', 'detail_penjualan_pakets.uuid');
            })
            // join jasa
            ->leftJoinSub($jasaSub, 'jasa', function ($join) {
                $join->on('penjualans.id', '=', 'jasa.id');
            })
            ->groupBy('penjualans.id')
            ->latest('penjualans.created_at');

        // filter outlet
        if ($request->filled('uuid_user')) {
            $query->where('penjualans.uuid_outlet', $request->uuid_user);
        }

        // searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        // total filtered (tanpa groupBy)
        $totalFiltered = Penjualan::when($request->filled('uuid_user'), function ($q) use ($request) {
            $q->where('uuid_outlet', $request->uuid_user);
        })
            ->when(!empty($request->search['value']), function ($q) use ($request, $columns) {
                $search = $request->search['value'];
                $q->where(function ($q2) use ($search, $columns) {
                    foreach ($columns as $column) {
                        $q2->orWhere($column, 'like', "%{$search}%");
                    }
                });
            })
            ->count();

        // pagination
        $data = $query->skip($request->start)->take($request->length)->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function export_excel($params = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ===== Header =====
        $headers = [
            'A1' => 'Tanggal',
            'B1' => 'Nama Barang',
            'C1' => 'Merek',
            'D1' => 'Kategori',
            'E1' => 'Sub Kategori',
            'F1' => 'Suplier',
            'G1' => 'Qty',
            'H1' => 'Total Harga',
        ];
        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col, $text);
        }

        // ===== Detail produk =====
        $produkDetails = DB::table('detail_penjualans as dp')
            ->join('penjualans as p', 'dp.uuid_penjualans', '=', 'p.uuid')
            ->join('produks as pr', 'dp.uuid_produk', '=', 'pr.uuid')
            ->leftJoin('suplayers as s', 'pr.uuid_suplayer', '=', 's.uuid')
            ->leftJoin('kategoris as k', 'pr.uuid_kategori', '=', 'k.uuid')
            ->select(
                'p.tanggal_transaksi',
                'pr.nama_barang',
                'pr.merek',
                'k.nama_kategori',
                'pr.sub_kategori',
                's.nama as nama_suplier',
                'dp.qty',
                'dp.total_harga'
            );

        if ($params) {
            $produkDetails->where('p.uuid_outlet', $params);
        }

        // ===== Detail paket hemat =====
        $paketDetails = DB::table('detail_penjualan_pakets as dpp')
            ->join('penjualans as p', 'dpp.uuid_penjualans', '=', 'p.uuid')
            ->join('paket_hemats as ph', 'dpp.uuid_paket', '=', 'ph.uuid')
            ->select(
                'p.tanggal_transaksi',
                'ph.nama_paket as nama_barang',
                DB::raw('"" as merek'),
                DB::raw('"" as nama_kategori'),
                DB::raw('"" as sub_kategori'),
                DB::raw('"" as nama_suplier'),
                'dpp.qty',
                'dpp.total_harga'
            );

        if ($params) {
            $paketDetails->where('p.uuid_outlet', $params);
        }

        // Gabungkan produk + paket hemat
        $allDetails = $produkDetails->unionAll($paketDetails)->get();

        // ===== Isi data ke Excel =====
        $row = 2;
        foreach ($allDetails as $d) {
            $sheet->setCellValue('A' . $row, \Carbon\Carbon::parse($d->tanggal_transaksi)->format('d-m-Y'));
            $sheet->setCellValue('B' . $row, $d->nama_barang);
            $sheet->setCellValue('C' . $row, $d->merek);
            $sheet->setCellValue('D' . $row, $d->nama_kategori);
            $sheet->setCellValue('E' . $row, $d->sub_kategori);
            $sheet->setCellValue('F' . $row, $d->nama_suplier);
            $sheet->setCellValue('G' . $row, $d->qty);
            $sheet->setCellValue('H' . $row, $d->total_harga);

            // Format rupiah (Rp #.##0,00)
            $sheet->getStyle('H' . $row)
                ->getNumberFormat()
                ->setFormatCode('"Rp" #,##0');
            $row++;
        }

        // Auto width kolom
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ==== Styling Header ====
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9E1F2'], // biru muda
            ],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // ==== Border untuk semua data (A1:H terakhir) ====
        $sheet->getStyle('A1:H' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        // ==== Total di paling bawah ====
        $sheet->mergeCells('A' . $row . ':G' . $row); // merge kolom A-G
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('H' . $row, '=SUM(H2:H' . ($row - 1) . ')');

        // Styling total
        $totalStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'FCE4D6'], // oranye muda
            ],
        ];
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($totalStyle);

        // Format H (total) jadi Rupiah
        $sheet->getStyle('H' . $row)
            ->getNumberFormat()
            ->setFormatCode('"Rp" #,##0');

        // ===== Download =====
        $fileName = 'penjualan-export.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}

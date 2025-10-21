<?php

namespace App\Http\Controllers;

use App\Helpers\JurnalHelper;
use App\Http\Requests\StorePembelianRequest;
use App\Http\Requests\UpdatePembelianRequest;
use App\Models\Coa;
use App\Models\DetailPembelian;
use App\Models\DetailPoPusat;
use App\Models\Hutang;
use App\Models\Jurnal;
use App\Models\Pembelian;
use App\Models\PoPusat;
use App\Models\PriceHistory;
use App\Models\Produk;
use App\Models\StatusBarang;
use App\Models\Suplayer;
use App\Models\Wirehouse;
use App\Models\WirehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PembelianController extends Controller
{
    public function index()
    {
        $module = 'Pembelian';
        $suplayers = Suplayer::select('uuid', 'nama')->get();
        $po_pusat = PoPusat::select('uuid', 'no_po')->get();
        $aset = Coa::where('tipe', 'aset')->whereNotIn('nama', ['Kas Outlet', 'Persediaan Sparepart'])->select('uuid', 'nama')->get();
        return view('pages.pembelian.index', compact('module', 'suplayers', 'po_pusat', 'aset'));
    }

    public function getProdukBySuplayer($params)
    {
        $produks = Produk::where('uuid_suplayer', $params)
            ->select('uuid', 'nama_barang')
            ->get();

        return response()->json($produks);
    }

    public function get(Request $request)
    {
        $columns = [
            'pembelians.uuid' => 'uuid',
            'pembelians.uuid_suplayer' => 'uuid_suplayer',
            'pembelians.no_invoice' => 'no_invoice',
            'pembelians.pembayaran' => 'pembayaran',
            'pembelians.tanggal_transaksi' => 'tanggal_transaksi',
            'pembelians.created_by' => 'created_by',
            'pembelians.updated_by' => 'updated_by',
            'suplayers.nama' => 'nama_suplayer',
            'COALESCE(SUM(detail_pembelians.qty * produks.hrg_modal),0)' => 'total_harga',
        ];

        // Hitung total tanpa filter
        $totalData = Pembelian::count();

        // SELECT dengan alias
        $selects = [];
        foreach ($columns as $dbCol => $alias) {
            $selects[] = "$dbCol as $alias";
        }

        $baseQuery = Pembelian::selectRaw(implode(", ", $selects))
            ->leftJoin('suplayers', 'suplayers.uuid', '=', 'pembelians.uuid_suplayer')
            ->leftJoin('detail_pembelians', 'detail_pembelians.uuid_pembelian', '=', 'pembelians.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_pembelians.uuid_produk')
            ->groupBy(
                'pembelians.uuid',
                'pembelians.uuid_suplayer',
                'pembelians.no_invoice',
                'pembelians.pembayaran',
                'pembelians.tanggal_transaksi',
                'pembelians.created_by',
                'pembelians.updated_by',
                'suplayers.nama'
            );

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $baseQuery->where(function ($q) use ($search, $columns) {
                foreach ($columns as $dbCol => $alias) {
                    if (str_contains($dbCol, 'SUM')) continue;
                    $q->orWhere($dbCol, 'like', "%{$search}%");
                }
            });
        }

        // Clone query untuk menghitung totalFiltered
        $filteredQuery = clone $baseQuery;
        $totalFiltered = $filteredQuery->get()->count(); // pakai get()->count() agar sesuai dengan hasil group

        // Sorting
        if ($request->order) {
            $orderColIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];
            $dbCol = array_keys($columns)[$orderColIndex];
            $baseQuery->orderByRaw("$dbCol $orderDir");
        } else {
            $baseQuery->orderBy('pembelians.created_at', 'desc');
        }

        // Pagination
        $data = $baseQuery
            ->skip($request->start)
            ->take($request->length)
            ->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function store(StorePembelianRequest $request)
    {
        // Ambil produk berdasarkan UUID
        $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();

        if ($produk->count() !== count($request->uuid_produk)) {
            return response()->json(['status' => 'error', 'message' => 'Ada produk yang tidak ditemukan.'], 404);
        }

        if (count($request->uuid_produk) !== count($request->qty)) {
            return response()->json(['status' => 'error', 'message' => 'Jumlah produk dan qty tidak sesuai.'], 400);
        }

        // Simpan pembelian
        $pembelian = Pembelian::create([
            'uuid_suplayer'      => $request->uuid_suplayer,
            'no_invoice'         => $request->no_invoice,
            'no_internal'        => $request->no_internal,
            'pembayaran'         => $request->pembayaran,
            'tanggal_transaksi'  => $request->tanggal_transaksi,
            'keterangan'         => $request->keterangan,
            'created_by'         => Auth::user()->nama,
        ]);

        $totalPembelian = 0;

        // Simpan detail pembelian + hitung total
        foreach ($request->uuid_produk as $index => $uuid_produk) {
            $produk = $produk->where('uuid', $uuid_produk)->first();
            $qty = $request->qty[$index];

            $hargaBaru = (int) preg_replace('/\D/', '', $request->harga[$index]);

            DetailPembelian::create([
                'uuid_pembelian' => $pembelian->uuid,
                'uuid_produk'    => $uuid_produk,
                'qty'            => $qty,
                'harga'         => $hargaBaru,
            ]);

            // Simpan harga lama sebelum update
            $hargaLama = (int) $produk->hrg_modal;

            // Update harga modal produk
            $produk->update(['hrg_modal' => $hargaBaru]);

            // Tambahkan price history hanya jika modal berubah
            if ($hargaLama !== $hargaBaru) {
                PriceHistory::create([
                    'uuid_produk' => $produk->uuid,
                    'harga'       => $hargaBaru,
                ]);
            }

            $totalPembelian += $qty * $produk->hrg_modal;
        }

        $suplyer = Suplayer::where('uuid', $request->uuid_suplayer)->first();

        StatusBarang::create([
            'uuid_log_barang' => $pembelian->uuid,
            'ref' => $request->no_invoice,
            'ketarangan' => 'Pembelian dari suplayer ' . $suplyer->nama,
        ]);

        // Ambil gudang pusat (jangan bikin baru setiap kali)
        $warehouse = Wirehouse::where('tipe', 'gudang')->where('lokasi', 'pusat')->first();

        if (!$warehouse) {
            $warehouse = Wirehouse::create([
                'uuid_user'  => Auth::user()->uuid,
                'tipe'       => 'gudang',
                'lokasi'     => 'pusat',
                'keterangan' => 'Gudang pusat',
            ]);
        }

        // Tambahkan stok per produk
        foreach ($request->uuid_produk as $index => $uuid_produk) {
            WirehouseStock::create([
                'uuid_warehouse' => $warehouse->uuid,
                'uuid_produk'    => $uuid_produk,
                'qty'            => $request->qty[$index],
                'jenis'          => 'masuk',
                'sumber'         => 'pembelian',
                'keterangan'     => 'Pembelian dari supplier: ' . $suplyer->nama,
            ]);
        }

        // Jika pembayaran kredit → simpan hutang
        if ($request->pembayaran === 'Kredit') {
            Hutang::create([
                'uuid_pembelian' => $pembelian->uuid,
                'jatuh_tempo'    => now()->addDays(7)->format('Y-m-d'),
            ]);
        }

        // ======== Jurnal Otomatis =========
        $persediaan = Coa::where('nama', 'Persediaan Sparepart')->first();
        $kas        = Coa::where('uuid', $request->aset)->first();
        $hutang     = Coa::where('nama', 'Hutang Usaha')->first();

        if ($request->pembayaran === 'Cash') {
            JurnalHelper::create(
                $request->tanggal_transaksi,
                $request->no_invoice,
                'Pembelian Cash ' . $kas->nama,
                [
                    ['uuid_coa' => $persediaan->uuid, 'debit' => $totalPembelian],
                    ['uuid_coa' => $kas->uuid,        'kredit' => $totalPembelian],
                ]
            );
        } elseif ($request->pembayaran === 'Kredit') {
            JurnalHelper::create(
                $request->tanggal_transaksi,
                $request->no_invoice,
                'Pembelian Kredit',
                [
                    ['uuid_coa' => $persediaan->uuid, 'debit' => $totalPembelian],
                    ['uuid_coa' => $hutang->uuid,     'kredit' => $totalPembelian],
                ]
            );
        }

        return response()->json(['status' => 'success']);
    }

    public function edit($uuid)
    {
        $pembelian = Pembelian::with(['details.produk'])->where('uuid', $uuid)->first();
        $junal = Jurnal::where('ref', $pembelian->no_invoice)->get();

        if ($junal) {
            $pembelian->aset = $junal[1]->uuid_coa;
        }

        return response()->json($pembelian);
    }

    public function update(StorePembelianRequest $request, $uuid)
    {
        // Cari data pembelian
        $pembelian = Pembelian::where('uuid', $uuid)->firstOrFail();

        // Ambil produk berdasarkan UUID
        $produkList = Produk::whereIn('uuid', $request->uuid_produk)->get();

        // Pastikan semua produk ditemukan
        if ($produkList->count() !== count($request->uuid_produk)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ada produk yang tidak ditemukan.'
            ], 404);
        }

        // Update data pembelian
        $pembelian->update([
            'uuid_suplayer'     => $request->uuid_suplayer,
            'no_invoice'        => $request->no_invoice,
            'no_internal'       => $request->no_internal,
            'pembayaran'        => $request->pembayaran,
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'keterangan'        => $request->keterangan,
            'updated_by'        => Auth::user()->nama,
        ]);

        $suplyer = Suplayer::where('uuid', $request->uuid_suplayer)->first();

        $statusbarang = StatusBarang::where('uuid_log_barang', $pembelian->uuid)->first();
        $statusbarang->update([
            'uuid_log_barang' => $pembelian->uuid,
            'ref' => $request->no_invoice,
            'ketarangan' => 'Pembelian dari suplayer ' . $suplyer->nama,
        ]);

        // Hapus detail lama
        DetailPembelian::where('uuid_pembelian', $pembelian->uuid)->delete();

        $totalPembelian = 0;

        // Simpan detail baru
        foreach ($request->uuid_produk as $index => $uuid_produk) {
            $produk = $produkList->firstWhere('uuid', $uuid_produk);
            $qty = (int) $request->qty[$index];
            $hargaBaru = (int) preg_replace('/\D/', '', $request->harga[$index]);

            DetailPembelian::create([
                'uuid_pembelian' => $pembelian->uuid,
                'uuid_produk'    => $uuid_produk,
                'qty'            => $qty,
                'harga'          => $hargaBaru,
            ]);

            // Hitung total
            $totalPembelian += $qty * $hargaBaru;

            // Simpan harga lama sebelum update
            $hargaLama = (int) $produk->hrg_modal;

            // Update harga modal produk
            $produk->update(['hrg_modal' => $hargaBaru]);

            // Tambahkan price history hanya jika modal berubah
            if ($hargaLama !== $hargaBaru) {
                PriceHistory::create([
                    'uuid_produk' => $produk->uuid,
                    'harga'       => $hargaBaru,
                ]);
            }
        }

        // Ambil gudang pusat (jangan bikin baru setiap kali)
        $warehouse = Wirehouse::where('tipe', 'gudang')
            ->where('lokasi', 'pusat')
            ->firstOrCreate([
                'uuid_user'  => Auth::user()->uuid,
                'tipe'       => 'gudang',
                'lokasi'     => 'pusat',
            ], [
                'keterangan' => 'Gudang pusat',
            ]);

        // Reset stok lama lalu simpan stok baru (hindari double stock)
        WirehouseStock::where('sumber', 'pembelian')
            ->where('uuid_warehouse', $warehouse->uuid)
            ->where('keterangan', 'Pembelian dari supplier: ' . $suplyer->nama)
            ->delete();

        foreach ($request->uuid_produk as $index => $uuid_produk) {
            WirehouseStock::create([
                'uuid_warehouse' => $warehouse->uuid,
                'uuid_produk'    => $uuid_produk,
                'qty'            => $request->qty[$index],
                'jenis'          => 'masuk',
                'sumber'         => 'pembelian',
                'keterangan'     => 'Pembelian dari supplier: ' . $suplyer->nama,
            ]);
        }

        // Jika pembayaran kredit → update / buat hutang
        if ($request->pembayaran === 'Kredit') {
            Hutang::updateOrCreate(
                ['uuid_pembelian' => $pembelian->uuid],
                ['jatuh_tempo'    => now()->addDays(7)->format('Y-m-d')]
            );
        } else {
            // Jika sudah lunas → hapus hutang lama
            Hutang::where('uuid_pembelian', $pembelian->uuid)->delete();
        }

        // ======== Jurnal Otomatis =========
        $persediaan = Coa::where('nama', 'Persediaan Sparepart')->first();
        $kas        = Coa::where('uuid', $request->aset)->first();
        $hutang     = Coa::where('nama', 'Hutang Usaha')->first();

        // Hapus jurnal lama dulu agar tidak dobel
        Jurnal::where('ref', $request->no_invoice)->delete();

        if ($request->pembayaran === 'Cash') {
            JurnalHelper::create(
                $request->tanggal_transaksi,
                $request->no_invoice,
                'Pembelian Cash ' . $kas->nama,
                [
                    ['uuid_coa' => $persediaan->uuid, 'debit' => $totalPembelian],
                    ['uuid_coa' => $kas->uuid,        'kredit' => $totalPembelian],
                ]
            );
        } elseif ($request->pembayaran === 'Kredit') {
            JurnalHelper::create(
                $request->tanggal_transaksi,
                $request->no_invoice,
                'Pembelian Kredit',
                [
                    ['uuid_coa' => $persediaan->uuid, 'debit' => $totalPembelian],
                    ['uuid_coa' => $hutang->uuid,     'kredit' => $totalPembelian],
                ]
            );
        }

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        // Cari pembelian yang mau dihapus
        $pembelian = Pembelian::where('uuid', $params)->firstOrFail();

        // Ambil semua produk terkait pembelian
        $uuidProduks = DetailPembelian::where('uuid_pembelian', $pembelian->uuid)
            ->pluck('uuid_produk');

        // Hapus stok yang terkait pembelian ini
        WirehouseStock::where('sumber', 'pembelian')
            ->whereIn('uuid_produk', $uuidProduks)
            ->delete();

        // Hapus detail pembelian
        DetailPembelian::where('uuid_pembelian', $pembelian->uuid)->delete();

        // Hapus status barang
        StatusBarang::where('uuid_log_barang', $pembelian->uuid)->delete();

        // Hapus hutang (jika ada)
        Hutang::where('uuid_pembelian', $pembelian->uuid)->delete();

        // Hapus jurnal yang terkait
        Jurnal::where('ref', $pembelian->no_invoice)->delete();

        // Hapus pembelian utama
        $pembelian->delete();

        return response()->json(['status' => 'success']);
    }

    public function form_po($uuid)
    {
        $po = PoPusat::where('uuid', $uuid)->first();

        if (!$po) {
            return response()->json([
                'status' => 'error',
                'message' => 'PO tidak ditemukan'
            ]);
        }

        // ambil supplier
        $supplier = Suplayer::where('uuid', $po->uuid_suplayer)->first();

        // ambil detail PO
        $details = DetailPoPusat::where('uuid_po_pusat', $po->uuid)->get();

        $detailsFormatted = $details->map(function ($d) {
            $produk = Produk::where('uuid', $d->uuid_produk)->first();
            return [
                'uuid_produk' => $d->uuid_produk,
                'nama_barang' => $produk ? $produk->nama_barang : null,
                'qty' => $d->qty,
                'harga' => $d->harga,
            ];
        });

        return response()->json([
            'status' => 'success',
            'po' => [
                'uuid_suplayer' => $po->uuid_suplayer,
                'nama_suplayer' => $supplier ? $supplier->nama : null,
                'no_po' => $po->no_po,
                'tanggal_transaksi' => $po->tanggal_transaksi,
                'keterangan' => $po->keterangan
            ],
            'details' => $detailsFormatted
        ]);
    }
}

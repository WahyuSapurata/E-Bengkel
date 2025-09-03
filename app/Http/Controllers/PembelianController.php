<?php

namespace App\Http\Controllers;

use App\Helpers\JurnalHelper;
use App\Http\Requests\StorePembelianRequest;
use App\Http\Requests\UpdatePembelianRequest;
use App\Models\Coa;
use App\Models\DetailPembelian;
use App\Models\DetailPoPusat;
use App\Models\Hutang;
use App\Models\Pembelian;
use App\Models\PoPusat;
use App\Models\Produk;
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
        // Kolom: database => alias
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

        $totalData = Pembelian::count();

        // SELECT dengan alias
        $selects = [];
        foreach ($columns as $dbCol => $alias) {
            $selects[] = "$dbCol as $alias";
        }

        $query = Pembelian::selectRaw(implode(", ", $selects))
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
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $dbCol => $alias) {
                    // kalau kolom pakai SUM() skip pencarian
                    if (str_contains($dbCol, 'SUM')) continue;
                    $q->orWhere($dbCol, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $query->count();

        // Sorting
        if ($request->order) {
            $orderColIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];

            $dbCol = array_keys($columns)[$orderColIndex];
            $query->orderByRaw("$dbCol $orderDir");
        } else {
            $query->orderBy('pembelians.created_at', 'desc');
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

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

            DetailPembelian::create([
                'uuid_pembelian' => $pembelian->uuid,
                'uuid_produk'    => $uuid_produk,
                'qty'            => $qty,
            ]);

            $totalPembelian += $qty * $produk->hrg_modal;
        }

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

        $suplyer = Suplayer::where('uuid', $request->uuid_suplayer)->first();

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

        return response()->json($pembelian);
    }

    public function update(StorePembelianRequest $request, $uuid)
    {
        // Cari data pembelian
        $pembelian = Pembelian::where('uuid', $uuid)->firstOrFail();

        // Ambil produk berdasarkan UUID
        $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();

        // Pastikan semua produk ditemukan
        if ($produk->count() !== count($request->uuid_produk)) {
            return response()->json(['status' => 'error', 'message' => 'Ada produk yang tidak ditemukan.'], 404);
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

        // Hapus detail lama
        DetailPembelian::where('uuid_pembelian', $pembelian->uuid)->delete();

        // Simpan detail baru
        foreach ($request->uuid_produk as $index => $uuid_produk) {
            $item = $produk->firstWhere('uuid', $uuid_produk);
            DetailPembelian::create([
                'uuid_pembelian' => $pembelian->uuid,
                'uuid_produk'    => $uuid_produk,
                'qty'            => $request->qty[$index],
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        // Cari pembelian yang mau dihapus
        $pembelian = Pembelian::where('uuid', $params)->firstOrFail();

        // Hapus detail pembelian
        DetailPembelian::where('uuid_pembelian', $pembelian->uuid)->delete();

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

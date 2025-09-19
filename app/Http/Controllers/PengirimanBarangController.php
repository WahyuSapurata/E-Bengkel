<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengirimanBarangRequest;
use App\Http\Requests\UpdatePengirimanBarangRequest;
use App\Models\DetailPengirimanBarang;
use App\Models\Outlet;
use App\Models\PengirimanBarang;
use App\Models\PoOutlet;
use App\Models\Produk;
use App\Models\Wirehouse;
use App\Models\WirehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengirimanBarangController extends Controller
{
    public function index()
    {
        $module = 'Po';
        $produk = Produk::select('uuid', 'nama_barang')->get();
        $po_outlet = PoOutlet::select('uuid', 'no_po')
            ->where('status', 'aprove')
            ->get();
        return view('pages.pengiriman.index', compact('module', 'produk', 'po_outlet'));
    }

    public function get(Request $request)
    {
        // Kolom: database => alias
        $columns = [
            'pengiriman_barangs.uuid' => 'uuid',
            'pengiriman_barangs.uuid_outlet' => 'uuid_outlet',
            'pengiriman_barangs.no_do' => 'no_do',
            'pengiriman_barangs.tanggal_kirim' => 'tanggal_kirim',
            'pengiriman_barangs.status' => 'status',
            'pengiriman_barangs.created_by' => 'created_by',
            'outlets.uuid_user' => 'uuid_user_outlet',
            'outlets.nama_outlet' => 'nama_outlet',
            'COALESCE(SUM(detail_pengiriman_barangs.qty * produks.hrg_modal),0)' => 'total_harga',
        ];

        $totalData = PengirimanBarang::count();

        // SELECT dengan alias
        $selects = [];
        foreach ($columns as $dbCol => $alias) {
            $selects[] = "$dbCol as $alias";
        }

        $query = PengirimanBarang::selectRaw(implode(", ", $selects))
            ->leftJoin('detail_pengiriman_barangs', 'detail_pengiriman_barangs.uuid_pengiriman_barang', '=', 'pengiriman_barangs.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_pengiriman_barangs.uuid_produk')
            ->leftJoin('outlets', 'outlets.uuid_user', '=', 'pengiriman_barangs.uuid_outlet')
            ->groupBy(
                'pengiriman_barangs.uuid',
                'pengiriman_barangs.uuid_outlet',
                'pengiriman_barangs.no_do',
                'pengiriman_barangs.tanggal_kirim',
                'pengiriman_barangs.status',
                'pengiriman_barangs.created_by',
                'outlets.uuid_user',
                'outlets.nama_outlet'
            );

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $dbCol => $alias) {
                    if (str_contains($dbCol, 'SUM')) continue; // skip agregat
                    $q->orWhere($dbCol, 'like', "%{$search}%");
                }
            });
        }

        // Hitung total filtered (clone query tanpa limit & order)
        $totalFilteredQuery = PengirimanBarang::leftJoin('detail_pengiriman_barangs', 'detail_pengiriman_barangs.uuid_pengiriman_barang', '=', 'pengiriman_barangs.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_pengiriman_barangs.uuid_produk')
            ->leftJoin('outlets', 'outlets.uuid', '=', 'pengiriman_barangs.uuid_outlet')
            ->leftJoin('users', 'users.uuid', '=', 'outlets.uuid_user');

        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $totalFilteredQuery->where(function ($q) use ($search, $columns) {
                foreach ($columns as $dbCol => $alias) {
                    if (str_contains($dbCol, 'SUM')) continue;
                    $q->orWhere($dbCol, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $totalFilteredQuery->distinct('pengiriman_barangs.uuid')->count('pengiriman_barangs.uuid');

        // Sorting
        if ($request->order) {
            $orderColIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];

            $dbCol = array_keys($columns)[$orderColIndex];
            $query->orderByRaw("$dbCol $orderDir");
        } else {
            $query->orderBy('pengiriman_barangs.created_at', 'desc');
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

    public function store(StorePengirimanBarangRequest $request)
    {
        // Ambil produk berdasarkan UUID
        $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();

        if ($produk->count() !== count($request->uuid_produk)) {
            return response()->json(['status' => 'error', 'message' => 'Ada produk yang tidak ditemukan.'], 404);
        }

        // Cek PO Outlet
        $po = PoOutlet::where('uuid', $request->uuid_po_outlet)->first();
        if (!$po) {
            return response()->json(['status' => 'error', 'message' => 'PO Outlet tidak ditemukan'], 404);
        }

        // Generate nomor DO
        $today = now()->format('dmy');
        $prefix = "DO-" . $today;

        $lastDo = PengirimanBarang::whereDate('created_at', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->first();

        $nextNumber = $lastDo
            ? intval(substr($lastDo->no_do, strrpos($lastDo->no_do, '-') + 1)) + 1
            : 1;

        $no_do = $prefix . "-" . $nextNumber;

        // Simpan header pengiriman
        $pengiriman = PengirimanBarang::create([
            'uuid_po_outlet' => $po->uuid,
            'uuid_outlet'    => $po->uuid_user,
            'no_do'          => $no_do,
            'tanggal_kirim'  => $request->tanggal_kirim,
            'created_by'     => Auth::user()->nama,
        ]);

        // Ambil gudang pusat
        $warehousePusat = Wirehouse::where('tipe', 'gudang')->where('lokasi', 'pusat')->first();

        // Simpan detail + stok keluar
        foreach ($request->uuid_produk as $index => $uuid_produk) {
            $item = $produk->firstWhere('uuid', $uuid_produk);

            // Simpan detail DO
            DetailPengirimanBarang::create([
                'uuid_pengiriman_barang' => $pengiriman->uuid,
                'uuid_produk'            => $uuid_produk,
                'qty'                    => $request->qty[$index],
            ]);

            // Cek stok pusat
            $stokPusat = WirehouseStock::where('uuid_warehouse', $warehousePusat->uuid)
                ->where('uuid_produk', $uuid_produk)
                ->sum('qty');

            if ($stokPusat < $request->qty[$index]) {
                return response()->json(['status' => 'error', 'message' => 'Stok pusat tidak cukup untuk produk ' . $item->nama], 400);
            }

            // Catat keluar dari gudang pusat
            WirehouseStock::create([
                'uuid_warehouse' => $warehousePusat->uuid,
                'uuid_produk'    => $uuid_produk,
                'qty'            => -$request->qty[$index],
                'jenis'          => 'keluar',
                'sumber'         => 'delivery order',
                'keterangan'     => 'Pengiriman ke outlet: ' . Outlet::where('uuid_user', $po->uuid_user)->first()->nama_outlet,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function edit($uuid)
    {
        $pengiriman_barang = PengirimanBarang::where('uuid', $uuid)->first();
        if (!$pengiriman_barang) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }

        // Ambil detail produk
        $detailProduk = DetailPengirimanBarang::where('uuid_pengiriman_barang', $pengiriman_barang->uuid)
            ->select('uuid_produk', 'qty')
            ->get();
        $pengiriman_barang->detail_produk = $detailProduk;

        return response()->json($pengiriman_barang);
    }

    public function update(StorePengirimanBarangRequest $request, $uuid)
    {
        // Cari data pengiriman_barang
        $pengiriman_barang = PengirimanBarang::where('uuid', $uuid)->firstOrFail();

        // Ambil produk berdasarkan UUID
        $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();

        if ($produk->count() !== count($request->uuid_produk)) {
            return response()->json(['status' => 'error', 'message' => 'Ada produk yang tidak ditemukan.'], 404);
        }

        // Cari PO Outlet
        $po = PoOutlet::where('uuid', $request->uuid_po_outlet)->first();
        if (!$po) {
            return response()->json(['status' => 'error', 'message' => 'PO Outlet tidak ditemukan'], 404);
        }

        // Update data pengiriman_barang
        $pengiriman_barang->update([
            'uuid_po_outlet' => $po->uuid,
            'uuid_outlet'    => $po->uuid_user,
            'tanggal_kirim'  => $request->tanggal_kirim,
            'updated_by'     => Auth::user()->nama,
        ]);

        // Hapus detail lama
        DetailPengirimanBarang::where('uuid_pengiriman_barang', $pengiriman_barang->uuid)->delete();

        // Simpan detail baru
        foreach ($request->uuid_produk as $index => $uuid_produk) {
            DetailPengirimanBarang::create([
                'uuid_pengiriman_barang' => $pengiriman_barang->uuid,
                'uuid_produk'    => $uuid_produk,
                'qty'            => $request->qty[$index],
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        // Cari pengiriman_barang yang mau dihapus
        $pengiriman_barang = PengirimanBarang::where('uuid', $params)->firstOrFail();

        // Hapus detail pengiriman_barang
        DetailPengirimanBarang::where('uuid_pengiriman_barang', $pengiriman_barang->uuid)->delete();

        // Hapus pengiriman_barang utama
        $pengiriman_barang->delete();

        return response()->json(['status' => 'success']);
    }

    public function vw_outlet()
    {
        $module = 'DO';
        return view('outlet.do.index', compact('module'));
    }

    public function get_vw_outlet(Request $request)
    {
        // Kolom: database => alias
        $columns = [
            'pengiriman_barangs.uuid' => 'uuid',
            'pengiriman_barangs.uuid_outlet' => 'uuid_outlet',
            'pengiriman_barangs.no_do' => 'no_do',
            'pengiriman_barangs.tanggal_kirim' => 'tanggal_kirim',
            'pengiriman_barangs.status' => 'status',
            'pengiriman_barangs.created_by' => 'created_by',
            'COALESCE(SUM(detail_pengiriman_barangs.qty * produks.hrg_modal),0)' => 'total_harga',
            'COALESCE(SUM(detail_pengiriman_barangs.qty),0)' => 'total_qty',
            // Array JSON detail produk
            "CAST(JSON_ARRAYAGG(
                JSON_OBJECT(
                    'uuid_produk', detail_pengiriman_barangs.uuid_produk,
                    'nama_barang', produks.nama_barang,
                    'qty', detail_pengiriman_barangs.qty
                )
            ) AS JSON)" => 'detail_produk'
        ];

        $totalData = PengirimanBarang::where('uuid_outlet', Auth::user()->uuid)->count();

        // SELECT dengan alias
        $selects = [];
        foreach ($columns as $dbCol => $alias) {
            $selects[] = "$dbCol as $alias";
        }

        $query = PengirimanBarang::where('uuid_outlet', Auth::user()->uuid)->selectRaw(implode(", ", $selects))
            ->leftJoin('detail_pengiriman_barangs', 'detail_pengiriman_barangs.uuid_pengiriman_barang', '=', 'pengiriman_barangs.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_pengiriman_barangs.uuid_produk')
            ->groupBy(
                'pengiriman_barangs.uuid',
                'pengiriman_barangs.uuid_outlet',
                'pengiriman_barangs.no_do',
                'pengiriman_barangs.tanggal_kirim',
                'pengiriman_barangs.status',
                'pengiriman_barangs.created_by'
            );

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $dbCol => $alias) {
                    if (str_contains($dbCol, 'SUM') || str_contains($dbCol, 'JSON_ARRAYAGG')) continue;
                    $q->orWhere($dbCol, 'like', "%{$search}%");
                }
            });
        }

        // Clone untuk hitung totalFiltered
        $totalFilteredQuery = PengirimanBarang::where('uuid_outlet', Auth::user()->uuid)->leftJoin('detail_pengiriman_barangs', 'detail_pengiriman_barangs.uuid_pengiriman_barang', '=', 'pengiriman_barangs.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_pengiriman_barangs.uuid_produk')
            ->leftJoin('outlets', 'outlets.uuid', '=', 'pengiriman_barangs.uuid_outlet');

        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $totalFilteredQuery->where(function ($q) use ($search, $columns) {
                foreach ($columns as $dbCol => $alias) {
                    if (str_contains($dbCol, 'SUM') || str_contains($dbCol, 'JSON_ARRAYAGG')) continue;
                    $q->orWhere($dbCol, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $totalFilteredQuery->distinct('pengiriman_barangs.uuid')->count('pengiriman_barangs.uuid');

        // Sorting
        if ($request->order) {
            $orderColIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];

            $dbCol = array_keys($columns)[$orderColIndex];
            $query->orderByRaw("$dbCol $orderDir");
        } else {
            $query->orderBy('pengiriman_barangs.created_at', 'desc');
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        // Ubah kolom detail_produk (string JSON → array PHP)
        $data->transform(function ($row) {
            $row->detail_produk = json_decode($row->detail_produk, true) ?? [];
            return $row;
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function aprove_do_outlet(Request $request, $uuidPengiriman)
    {
        DB::beginTransaction();

        try {
            // Ambil PO/DO yang mau diapprove
            $po_outlet = PengirimanBarang::where('uuid', $uuidPengiriman)->firstOrFail();

            // Update status
            $po_outlet->status = $request->status;
            $po_outlet->save();

            // Ambil outlet
            $outlet = Outlet::where('uuid_user', $po_outlet->uuid_outlet)->firstOrFail();

            // Ambil detail pengiriman
            $detailBarang = DetailPengirimanBarang::where('uuid_pengiriman_barang', $po_outlet->uuid)->get();

            $warehouseOutlet = Wirehouse::where('tipe', 'gudang')->where('lokasi', 'outlet')->where('uuid_user', $outlet->uuid_user)->first();

            foreach ($detailBarang as $detail) {
                // Catat stok masuk di warehouse outlet
                WirehouseStock::create([
                    'uuid_warehouse' => $warehouseOutlet->uuid,
                    'uuid_produk'    => $detail->uuid_produk,
                    'qty'            => $detail->qty,
                    'jenis'          => 'masuk',
                    'sumber'         => 'delivery order',
                    'keterangan'     => 'Penerimaan dari gudang pusat',
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'PO Outlet berhasil diapprove dan stok outlet diperbarui.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}

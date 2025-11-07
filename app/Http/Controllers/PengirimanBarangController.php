<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengirimanBarangRequest;
use App\Http\Requests\UpdatePengirimanBarangRequest;
use App\Models\DetailPengirimanBarang;
use App\Models\DetailPoOutlet;
use App\Models\Outlet;
use App\Models\PengirimanBarang;
use App\Models\PoOutlet;
use App\Models\Produk;
use App\Models\StatusBarang;
use App\Models\Wirehouse;
use App\Models\WirehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengirimanBarangController extends Controller
{
    public function index()
    {
        $module = 'Pengiriman Barang';
        $produk = Produk::select('uuid', 'nama_barang')->get();

        $transfer_barang = PengirimanBarang::pluck('uuid_po_outlet'); // langsung ambil list uuid_po_outlet
        $po_outlet = PoOutlet::select('uuid', 'no_po')
            ->where('status', 'aprove')
            ->whereNotIn('uuid', $transfer_barang)
            ->get();

        return view('pages.pengiriman.index', compact('module', 'produk', 'po_outlet'));
    }

    public function form_po_pusat($uuid)
    {
        $po = PoOutlet::where('uuid', $uuid)->first();

        if (!$po) {
            return response()->json([
                'status' => 'error',
                'message' => 'PO Outlet tidak ditemukan'
            ]);
        }

        // ambil detail PO
        $details = DetailPoOutlet::where('uuid_po_outlet', $po->uuid)->get();

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
            'details' => $detailsFormatted
        ]);
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
        return DB::transaction(function () use ($request) {
            // Ambil produk berdasarkan UUID
            $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();

            if ($produk->count() !== count($request->uuid_produk)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Ada produk yang tidak ditemukan.'
                ], 404);
            }

            // Ambil gudang pusat
            $warehousePusat = Wirehouse::where('tipe', 'gudang')
                ->where('lokasi', 'pusat')
                ->firstOrFail();

            // Cek PO Outlet
            $po = PoOutlet::where('uuid', $request->uuid_po_outlet)->first();
            if (!$po) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'PO Outlet tidak ditemukan'
                ], 404);
            }

            // ✅ Validasi stok semua produk sebelum insert
            foreach ($request->uuid_produk as $index => $uuid_produk) {
                $item = $produk->firstWhere('uuid', $uuid_produk);
                $qtyKirim = $request->qty[$index];

                $stokPusat = WirehouseStock::where('uuid_warehouse', $warehousePusat->uuid)
                    ->where('uuid_produk', $uuid_produk)
                    ->sum('qty');

                if ($stokPusat < $qtyKirim) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => "Stok pusat tidak cukup untuk produk {$item->nama_barang}"
                    ], 400);
                }
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
                'uuid_outlet' => $po->uuid_user,
                'no_do'          => $no_do,
                'tanggal_kirim'  => $request->tanggal_kirim,
                'created_by'     => Auth::user()->nama,
            ]);

            StatusBarang::create([
                'uuid_log_barang' => $pengiriman->uuid,
                'ref' => $no_do,
                'ketarangan' => 'Pengiriman barang ke ' . Outlet::where('uuid_user', $po->uuid_user)->first()->nama_outlet,
            ]);

            // Simpan detail + stok keluar
            foreach ($request->uuid_produk as $index => $uuid_produk) {
                $qtyKirim = $request->qty[$index];

                DetailPengirimanBarang::create([
                    'uuid_pengiriman_barang' => $pengiriman->uuid,
                    'uuid_produk'            => $uuid_produk,
                    'qty'                    => $qtyKirim,
                ]);

                WirehouseStock::create([
                    'uuid_warehouse' => $warehousePusat->uuid,
                    'uuid_produk'    => $uuid_produk,
                    'qty'            => -$qtyKirim,
                    'jenis'          => 'keluar',
                    'sumber'         => 'delivery order',
                    'keterangan'     => 'Pengiriman ke outlet: ' . Outlet::where('uuid_user', $po->uuid_user)->first()->nama_outlet,
                ]);
            }

            return response()->json(['status' => 'success']);
        });
    }

    public function edit($uuid)
    {
        $pengiriman_barang = PengirimanBarang::where('uuid', $uuid)->first();
        if (!$pengiriman_barang) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        // detail produk
        $detailProduk = DetailPengirimanBarang::where('uuid_pengiriman_barang', $pengiriman_barang->uuid)
            ->select('uuid_produk', 'qty')
            ->get();

        // ambil info PO Outlet yg dipakai
        $po = PoOutlet::select('uuid', 'no_po')
            ->where('uuid', $pengiriman_barang->uuid_po_outlet)
            ->first();

        return response()->json([
            'uuid'             => $pengiriman_barang->uuid,
            'uuid_po_outlet'   => $pengiriman_barang->uuid_po_outlet,
            'tanggal_kirim'    => $pengiriman_barang->tanggal_kirim,
            'no_po'            => $po ? $po->no_po : '-',
            'detail_produk'    => $detailProduk,
        ]);
    }


    public function update(StorePengirimanBarangRequest $request, $uuid)
    {
        return DB::transaction(function () use ($request, $uuid) {
            $pengiriman = PengirimanBarang::where('uuid', $uuid)->firstOrFail();

            // Ambil produk
            $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();
            if ($produk->count() !== count($request->uuid_produk)) {
                return response()->json(['status' => 'error', 'message' => 'Ada produk yang tidak ditemukan.'], 404);
            }

            // Gudang pusat
            $warehousePusat = Wirehouse::where('tipe', 'gudang')
                ->where('lokasi', 'pusat')
                ->firstOrFail();

            // PO Outlet
            $po = PoOutlet::where('uuid', $request->uuid_po_outlet)->first();
            if (!$po) {
                return response()->json(['status' => 'error', 'message' => 'PO Outlet tidak ditemukan'], 404);
            }

            // 🔹 Hapus detail lama + stok lama
            $oldDetails = DetailPengirimanBarang::where('uuid_pengiriman_barang', $pengiriman->uuid)->get();
            foreach ($oldDetails as $detail) {
                WirehouseStock::where('uuid_warehouse', $warehousePusat->uuid)
                    ->where('uuid_produk', $detail->uuid_produk)
                    ->where('qty', -$detail->qty) // pastikan catatan stok keluar yg sesuai
                    ->where('sumber', 'delivery order')
                    ->delete();
            }
            DetailPengirimanBarang::where('uuid_pengiriman_barang', $pengiriman->uuid)->delete();

            // 🔹 Validasi stok baru
            foreach ($request->uuid_produk as $i => $uuid_produk) {
                $qtyBaru = $request->qty[$i];
                $item = $produk->firstWhere('uuid', $uuid_produk);

                $stokPusat = WirehouseStock::where('uuid_warehouse', $warehousePusat->uuid)
                    ->where('uuid_produk', $uuid_produk)
                    ->sum('qty');

                if ($stokPusat < $qtyBaru) {
                    throw new \Exception("Stok pusat tidak cukup untuk produk {$item->nama_barang}.
                    Stok tersedia: {$stokPusat}, diminta: {$qtyBaru}");
                }
            }

            // 🔹 Update header
            $pengiriman->update([
                'uuid_po_outlet' => $po->uuid,
                'uuid_outlet'    => $po->uuid_user,
                'tanggal_kirim'  => $request->tanggal_kirim,
                'updated_by'     => Auth::user()->nama,
            ]);

            // 🔹 Buat ulang detail + stok keluar
            $outlet = Outlet::where('uuid_user', $po->uuid_user)->first();
            foreach ($request->uuid_produk as $i => $uuid_produk) {
                $qtyBaru = $request->qty[$i];

                DetailPengirimanBarang::create([
                    'uuid_pengiriman_barang' => $pengiriman->uuid,
                    'uuid_produk'            => $uuid_produk,
                    'qty'                    => $qtyBaru,
                ]);

                WirehouseStock::create([
                    'uuid_warehouse' => $warehousePusat->uuid,
                    'uuid_produk'    => $uuid_produk,
                    'qty'            => -$qtyBaru,
                    'jenis'          => 'keluar',
                    'sumber'         => 'delivery order',
                    'keterangan'     => 'Update DO ' . $pengiriman->no_do . ' ke outlet: ' . $outlet->nama_outlet,
                ]);
            }

            return response()->json(['status' => 'success', 'message' => 'Pengiriman berhasil diperbarui']);
        });
    }

    public function delete($uuid)
    {
        return DB::transaction(function () use ($uuid) {
            $pengiriman = PengirimanBarang::where('uuid', $uuid)->firstOrFail();

            $warehousePusat = Wirehouse::where('tipe', 'gudang')
                ->where('lokasi', 'pusat')
                ->firstOrFail();

            $details = DetailPengirimanBarang::where('uuid_pengiriman_barang', $pengiriman->uuid)->get();

            // 🔹 Hapus stok keluar terkait
            foreach ($details as $detail) {
                WirehouseStock::where('uuid_warehouse', $warehousePusat->uuid)
                    ->where('uuid_produk', $detail->uuid_produk)
                    ->where('qty', -$detail->qty)
                    ->where('sumber', 'delivery order')
                    ->delete();
            }

            // 🔹 Hapus detail
            DetailPengirimanBarang::where('uuid_pengiriman_barang', $pengiriman->uuid)->delete();

            StatusBarang::where('uuid_log_barang', $pengiriman->uuid)->delete();

            // 🔹 Hapus header
            $pengiriman->delete();

            return response()->json(['status' => 'success', 'message' => 'Pengiriman berhasil dihapus']);
        });
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

    public function getDetailPengiriman($uuid)
    {
        try {
            // Cari data pengiriman
            $pengiriman = PengirimanBarang::where('uuid', $uuid)->firstOrFail();

            // Ambil semua detail produk terkait DO
            $detail = DetailPengirimanBarang::where('uuid_pengiriman_barang', $pengiriman->uuid)
                ->join('produks', 'produks.uuid', '=', 'detail_pengiriman_barangs.uuid_produk')
                ->select(
                    'detail_pengiriman_barangs.uuid_produk',
                    'produks.nama_barang',
                    'detail_pengiriman_barangs.qty'
                )
                ->get();

            return response()->json($detail);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function aprove_do_outlet(Request $request, $uuidPengiriman)
    {
        DB::beginTransaction();

        try {
            // Ambil DO & detail barang
            $po_outlet = PengirimanBarang::where('uuid', $uuidPengiriman)->firstOrFail();
            $detailBarang = DetailPengirimanBarang::where('uuid_pengiriman_barang', $po_outlet->uuid)->get()->keyBy('uuid_produk');

            // Validasi input
            $request->validate([
                'status' => 'required',
                'alokasi' => 'required|array',
                'alokasi.*.uuid_produk' => 'required|uuid',
                'alokasi.*.qty_gudang' => 'nullable|integer|min:0',
                'alokasi.*.qty_toko' => 'nullable|integer|min:0',
            ]);

            // Validasi tambahan: total qty tidak boleh lebih dari pengiriman
            foreach ($request->alokasi as $alokasi) {
                $uuid_produk = $alokasi['uuid_produk'];
                $qty_gudang = $alokasi['qty_gudang'] ?? 0;
                $qty_toko = $alokasi['qty_toko'] ?? 0;
                $total_input = $qty_gudang + $qty_toko;

                if (!isset($detailBarang[$uuid_produk])) {
                    throw new \Exception("Produk dengan UUID {$uuid_produk} tidak ditemukan di DO ini.");
                }

                $qty_dikirim = $detailBarang[$uuid_produk]->qty;

                if ($total_input > $qty_dikirim) {
                    throw new \Exception("Total alokasi ($total_input) untuk produk {$detailBarang[$uuid_produk]->produk->nama_produk} melebihi jumlah pengiriman ($qty_dikirim).");
                }
            }

            // Update status DO
            $po_outlet->status = $request->status;
            $po_outlet->save();

            // Ambil outlet & lokasi warehouse
            $outlet = Outlet::where('uuid_user', $po_outlet->uuid_outlet)->first();

            // Ambil warehouse tujuan
            $warehouseGudang = Wirehouse::where('tipe', 'gudang')
                ->where('uuid_user', $outlet->uuid_user)
                ->first();
            $warehouseToko = Wirehouse::where('tipe', 'toko')
                ->where('uuid_user', $outlet->uuid_user)
                ->first();

            foreach ($request->alokasi as $alokasi) {
                // Ke Gudang
                if (!empty($alokasi['qty_gudang']) && $alokasi['qty_gudang'] > 0) {
                    WirehouseStock::create([
                        'uuid_warehouse' => $warehouseGudang->uuid,
                        'uuid_produk'    => $alokasi['uuid_produk'],
                        'qty'            => $alokasi['qty_gudang'],
                        'jenis'          => 'masuk',
                        'sumber'         => 'delivery order',
                        'keterangan'     => 'Alokasi ke gudang dari DO ' . $po_outlet->no_do,
                    ]);
                }

                // Ke Toko
                if (!empty($alokasi['qty_toko']) && $alokasi['qty_toko'] > 0) {
                    WirehouseStock::create([
                        'uuid_warehouse' => $warehouseGudang->uuid,
                        'uuid_produk'    => $alokasi['uuid_produk'],
                        'qty'            => -$alokasi['qty_gudang'],
                        'jenis'          => 'keluar',
                        'sumber'         => 'delivery order',
                        'keterangan'     => 'Alokasi ke gudang dari DO ' . $po_outlet->no_do,
                    ]);

                    WirehouseStock::create([
                        'uuid_warehouse' => $warehouseToko->uuid,
                        'uuid_produk'    => $alokasi['uuid_produk'],
                        'qty'            => $alokasi['qty_toko'],
                        'jenis'          => 'masuk',
                        'sumber'         => 'delivery order',
                        'keterangan'     => 'Alokasi ke toko dari DO ' . $po_outlet->no_do,
                    ]);
                }
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

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransferBarangRequest;
use App\Http\Requests\UpdateTransferBarangRequest;
use App\Models\DetailPengirimanBarang;
use App\Models\DetailTransferBarang;
use App\Models\Outlet;
use App\Models\PengirimanBarang;
use App\Models\Produk;
use App\Models\StatusBarang;
use App\Models\TransferBarang;
use App\Models\Wirehouse;
use App\Models\WirehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferBarangController extends Controller
{
    public function index()
    {
        $module = 'Transfer Barang';
        $produk = Produk::select('uuid', 'nama_barang')->get();
        $do = PengirimanBarang::select('uuid', 'no_do')->where('uuid_outlet', Auth::user()->uuid)->where('status', 'diterima')->get();
        // dd($do);
        return view('outlet.transfer.index', compact('module', 'produk', 'do'));
    }

    public function form_do($uuid)
    {
        $do = PengirimanBarang::where('uuid', $uuid)->first();

        if (!$do) {
            return response()->json([
                'status' => 'error',
                'message' => 'Do tidak ditemukan'
            ]);
        }

        // ambil detail PO
        $details = DetailPengirimanBarang::where('uuid_pengiriman_barang', $do->uuid)->get();

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
            'transfer_barangs.uuid' => 'uuid',
            'transfer_barangs.uuid_outlet' => 'uuid_outlet',
            'transfer_barangs.no_bukti' => 'no_bukti',
            'transfer_barangs.tanggal_transfer' => 'tanggal_transfer',
            'transfer_barangs.created_by' => 'created_by',
            'COALESCE(SUM(detail_transfer_barangs.qty * produks.hrg_modal),0)' => 'total_harga',
        ];

        $totalData = TransferBarang::where('uuid_outlet', Auth::user()->uuid)->count();

        // SELECT dengan alias
        $selects = [];
        foreach ($columns as $dbCol => $alias) {
            $selects[] = "$dbCol as $alias";
        }

        $query = TransferBarang::where('uuid_outlet', Auth::user()->uuid)->selectRaw(implode(", ", $selects))
            ->leftJoin('detail_transfer_barangs', 'detail_transfer_barangs.uuid_transfer_barangs', '=', 'transfer_barangs.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_transfer_barangs.uuid_produk')
            ->groupBy(
                'transfer_barangs.uuid',
                'transfer_barangs.uuid_outlet',
                'transfer_barangs.no_bukti',
                'transfer_barangs.tanggal_transfer',
                'transfer_barangs.created_by',
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
        $totalFilteredQuery = TransferBarang::leftJoin('detail_transfer_barangs', 'detail_transfer_barangs.uuid_transfer_barangs', '=', 'transfer_barangs.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_transfer_barangs.uuid_produk');

        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $totalFilteredQuery->where(function ($q) use ($search, $columns) {
                foreach ($columns as $dbCol => $alias) {
                    if (str_contains($dbCol, 'SUM')) continue;
                    $q->orWhere($dbCol, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $totalFilteredQuery->distinct('transfer_barangs.uuid')->count('transfer_barangs.uuid');

        // Sorting
        if ($request->order) {
            $orderColIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];

            $dbCol = array_keys($columns)[$orderColIndex];
            $query->orderByRaw("$dbCol $orderDir");
        } else {
            $query->orderBy('transfer_barangs.created_at', 'desc');
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

    public function store(StoreTransferBarangRequest $request)
    {
        DB::transaction(function () use ($request) {
            // Validasi produk
            $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();
            if ($produk->count() !== count($request->uuid_produk)) {
                throw new \Exception('Ada produk yang tidak ditemukan.');
            }

            $outlet = Outlet::where('uuid_user', Auth::user()->uuid)->first();

            // Gudang outlet
            $warehouseGudang = Wirehouse::where('uuid_user', Auth::user()->uuid)
                ->where('tipe', 'gudang')
                ->first();

            if (!$warehouseGudang) {
                $warehouseGudang = Wirehouse::create([
                    'uuid_user'  => Auth::user()->uuid,
                    'tipe'       => 'gudang',
                    'lokasi'     => 'outlet',
                    'keterangan' => 'Gudang outlet ' . $outlet->nama_outlet,
                ]);
            }

            // Toko outlet
            $warehouseToko = Wirehouse::where('uuid_user', Auth::user()->uuid)
                ->where('tipe', 'toko')
                ->first();

            if (!$warehouseToko) {
                $warehouseToko = Wirehouse::create([
                    'uuid_user'  => Auth::user()->uuid,
                    'tipe'       => 'toko',
                    'lokasi'     => 'outlet',
                    'keterangan' => 'Toko outlet ' . $outlet->nama_outlet,
                ]);
            }

            // 🔎 Step 1: validasi stok semua produk dulu
            foreach ($request->uuid_produk as $i => $uuid_produk) {
                $qty = $request->qty[$i];
                $item = $produk->firstWhere('uuid', $uuid_produk);

                $stokGudang = WirehouseStock::where('uuid_warehouse', $warehouseGudang->uuid)
                    ->where('uuid_produk', $uuid_produk)
                    ->sum('qty');

                if ($stokGudang < $qty) {
                    throw new \Exception('Stok gudang tidak cukup untuk produk ' . $item->nama_barang);
                }
            }

            // 🔎 Step 2: kalau stok cukup → generate nomor transfer
            $today = now()->format('dmy');
            $prefix = "TRF-" . $today;
            $lastDo = TransferBarang::whereDate('created_at', now()->toDateString())
                ->orderBy('created_at', 'desc')
                ->first();
            $nextNumber = $lastDo
                ? intval(substr($lastDo->no_bukti, strrpos($lastDo->no_bukti, '-') + 1)) + 1
                : 1;
            $no_bukti = $prefix . "-" . $nextNumber;

            // Simpan header
            $transfer = TransferBarang::create([
                'uuid_outlet'       => Auth::user()->uuid,
                'no_bukti'          => $no_bukti,
                'tanggal_transfer'  => $request->tanggal_transfer,
                'created_by'        => Auth::user()->nama,
            ]);

            // Catat status barang
            StatusBarang::create([
                'uuid_log_barang' => $transfer->uuid,
                'ref'             => $no_bukti,
                'ketarangan'      => 'Pengiriman barang dari outlet ' . $outlet->nama_outlet . ' ke toko',
            ]);

            // 🔎 Step 3: simpan detail + pergerakan stok
            foreach ($request->uuid_produk as $i => $uuid_produk) {
                $qty  = $request->qty[$i];
                $item = $produk->firstWhere('uuid', $uuid_produk);

                DetailTransferBarang::create([
                    'uuid_transfer_barangs' => $transfer->uuid,
                    'uuid_produk'           => $uuid_produk,
                    'qty'                   => $qty,
                ]);

                // keluar gudang
                WirehouseStock::create([
                    'uuid_warehouse' => $warehouseGudang->uuid,
                    'uuid_produk'    => $uuid_produk,
                    'qty'            => -$qty,
                    'jenis'          => 'keluar',
                    'sumber'         => 'transfer',
                    'keterangan'     => 'Transfer ' . $no_bukti . ' ke toko ' . $outlet->nama_outlet,
                ]);

                // masuk toko
                WirehouseStock::create([
                    'uuid_warehouse' => $warehouseToko->uuid,
                    'uuid_produk'    => $uuid_produk,
                    'qty'            => $qty,
                    'jenis'          => 'masuk',
                    'sumber'         => 'transfer',
                    'keterangan'     => 'Transfer ' . $no_bukti . ' dari gudang ' . $outlet->nama_outlet,
                ]);
            }
        });

        return response()->json(['status' => 'success', 'message' => 'Transfer berhasil']);
    }

    public function edit($uuid)
    {
        $transfer_barang = TransferBarang::where('uuid', $uuid)->first();
        if (!$transfer_barang) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }

        // Ambil detail produk
        $detailProduk = DetailTransferBarang::where('uuid_transfer_barangs', $transfer_barang->uuid)
            ->select('uuid_produk', 'qty')
            ->get();
        $transfer_barang->detail_produk = $detailProduk;

        return response()->json($transfer_barang);
    }

    public function update(StoreTransferBarangRequest $request, $uuid)
    {
        DB::transaction(function () use ($request, $uuid) {
            $transfer = TransferBarang::where('uuid', $uuid)->firstOrFail();

            $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();
            if ($produk->count() !== count($request->uuid_produk)) {
                throw new \Exception('Ada produk yang tidak ditemukan.');
            }

            $outlet = Outlet::where('uuid_user', $transfer->uuid_outlet)->first();
            $warehouseGudang = Wirehouse::where('uuid_user', $transfer->uuid_outlet)
                ->where('tipe', 'gudang')->first();
            $warehouseToko = Wirehouse::where('uuid_user', $transfer->uuid_outlet)
                ->where('tipe', 'toko')->first();

            // 🔎 Ambil detail lama dulu (supaya stok bisa dikembalikan sementara)
            $oldDetails = DetailTransferBarang::where('uuid_transfer_barangs', $transfer->uuid)->get();

            // 🔎 Step 1: Validasi stok cukup
            foreach ($request->uuid_produk as $i => $uuid_produk) {
                $qtyBaru = $request->qty[$i];
                $item = $produk->firstWhere('uuid', $uuid_produk);

                // stok tersedia sekarang
                $stokGudang = WirehouseStock::where('uuid_warehouse', $warehouseGudang->uuid)
                    ->where('uuid_produk', $uuid_produk)
                    ->sum('qty');

                if ($stokGudang < $qtyBaru) {
                    throw new \Exception('Stok gudang tidak cukup untuk produk ' . $item->nama_barang);
                }
            }

            // 🔎 Step 2: Update header
            $transfer->update([
                'tanggal_transfer' => $request->tanggal_transfer,
                'updated_by'       => Auth::user()->nama,
            ]);

            // 🔎 Step 3: Hapus detail lama
            DetailTransferBarang::where('uuid_transfer_barangs', $transfer->uuid)->delete();

            // 🔎 Step 4: Hapus stok lama
            WirehouseStock::where('sumber', 'transfer')
                ->where(function ($q) use ($transfer, $outlet) {
                    $q->where('keterangan', 'like', '%Transfer ' . $transfer->no_bukti . ' ke toko ' . $outlet->nama_outlet . '%')
                        ->orWhere('keterangan', 'like', '%Transfer ' . $transfer->no_bukti . ' dari gudang ' . $outlet->nama_outlet . '%');
                })
                ->delete();

            // 🔎 Step 5: Insert detail & stok baru
            foreach ($request->uuid_produk as $i => $uuid_produk) {
                $qty = $request->qty[$i];

                DetailTransferBarang::create([
                    'uuid_transfer_barangs' => $transfer->uuid,
                    'uuid_produk'           => $uuid_produk,
                    'qty'                   => $qty,
                ]);

                // keluar gudang
                WirehouseStock::create([
                    'uuid_warehouse' => $warehouseGudang->uuid,
                    'uuid_produk'    => $uuid_produk,
                    'qty'            => -$qty,
                    'jenis'          => 'keluar',
                    'sumber'         => 'transfer',
                    'keterangan'     => 'Transfer ' . $transfer->no_bukti . ' ke toko ' . $outlet->nama_outlet,
                ]);

                // masuk toko
                WirehouseStock::create([
                    'uuid_warehouse' => $warehouseToko->uuid,
                    'uuid_produk'    => $uuid_produk,
                    'qty'            => $qty,
                    'jenis'          => 'masuk',
                    'sumber'         => 'transfer',
                    'keterangan'     => 'Transfer ' . $transfer->no_bukti . ' dari gudang ' . $outlet->nama_outlet,
                ]);
            }
        });

        return response()->json(['status' => 'success', 'message' => 'Transfer berhasil diperbarui']);
    }

    public function delete($uuid)
    {
        DB::transaction(function () use ($uuid) {
            // Cari transfer_barang yang mau dihapus
            $transfer = TransferBarang::where('uuid', $uuid)->firstOrFail();

            // Hapus detail transfer_barang
            DetailTransferBarang::where('uuid_transfer_barangs', $transfer->uuid)->delete();

            // Hapus stok keluar/masuk yang terkait transfer ini
            WirehouseStock::where('sumber', 'transfer')
                ->where('keterangan', 'like', '%Transfer ' . $transfer->no_bukti . '%')
                ->delete();

            // Hapus status barang (kalau ada)
            StatusBarang::where('uuid_log_barang', $transfer->uuid)->delete();

            // Hapus transfer_barang utama
            $transfer->delete();
        });

        return response()->json(['status' => 'success', 'message' => 'Transfer berhasil dihapus']);
    }
}

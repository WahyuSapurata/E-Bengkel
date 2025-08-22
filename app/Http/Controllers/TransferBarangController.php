<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransferBarangRequest;
use App\Http\Requests\UpdateTransferBarangRequest;
use App\Models\DetailTransferBarang;
use App\Models\Outlet;
use App\Models\Produk;
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
        return view('outlet.transfer.index', compact('module', 'produk'));
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

        $totalData = TransferBarang::count();

        // SELECT dengan alias
        $selects = [];
        foreach ($columns as $dbCol => $alias) {
            $selects[] = "$dbCol as $alias";
        }

        $query = TransferBarang::selectRaw(implode(", ", $selects))
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

            // Generate nomor transfer
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
                'uuid_outlet'       => Auth::user()->uuid, // pastikan ambil outlet user
                'no_bukti'          => $no_bukti,
                'tanggal_transfer'  => $request->tanggal_transfer,
                'created_by'        => Auth::user()->nama,
            ]);

            // Gudang outlet
            $warehouseGudang = Wirehouse::where('uuid_user', $transfer->uuid_outlet)
                ->where('tipe', 'gudang') // gunakan 'tipe' bukan 'lokasi'
                ->first();

            if (!$warehouseGudang) {
                $namaOutlet = Outlet::where('uuid_user', $transfer->uuid_outlet)->value('nama_outlet');

                $warehouseGudang = Wirehouse::create([
                    'uuid_user'  => $transfer->uuid_outlet,
                    'tipe'       => 'gudang',
                    'lokasi'     => 'outlet',
                    'keterangan' => 'Gudang outlet ' . $namaOutlet,
                ]);
            }

            // Toko outlet
            $warehouseToko = Wirehouse::where('uuid_user', $transfer->uuid_outlet)
                ->where('tipe', 'toko')
                ->first();

            if (!$warehouseToko) {
                $namaOutlet = Outlet::where('uuid_user', $transfer->uuid_outlet)->value('nama_outlet');

                $warehouseToko = Wirehouse::create([
                    'uuid_user'  => $transfer->uuid_outlet,
                    'tipe'       => 'toko',
                    'lokasi'     => 'outlet',
                    'keterangan' => 'Toko outlet ' . $namaOutlet,
                ]);
            }

            foreach ($request->uuid_produk as $i => $uuid_produk) {
                $qty = $request->qty[$i];
                $item = $produk->firstWhere('uuid', $uuid_produk);

                // Simpan detail transfer
                DetailTransferBarang::create([
                    'uuid_transfer_barangs' => $transfer->uuid,
                    'uuid_produk'           => $uuid_produk,
                    'qty'                   => $qty,
                ]);

                // Hitung stok gudang
                $stokGudang = WirehouseStock::where('uuid_warehouse', $warehouseGudang->uuid)
                    ->where('uuid_produk', $uuid_produk)
                    ->sum('qty');

                if ($stokGudang < $qty) {
                    throw new \Exception('Stok gudang tidak cukup untuk produk ' . $item->nama_barang);
                }

                // Catat keluar dari gudang
                WirehouseStock::create([
                    'uuid_warehouse' => $warehouseGudang->uuid,
                    'uuid_produk'    => $uuid_produk,
                    'qty'            => -$qty,
                    'jenis'          => 'keluar',
                    'sumber'         => 'transfer',
                    'keterangan'     => 'Transfer ke toko',
                ]);

                // Catat masuk ke toko
                WirehouseStock::create([
                    'uuid_warehouse' => $warehouseToko->uuid,
                    'uuid_produk'    => $uuid_produk,
                    'qty'            => $qty,
                    'jenis'          => 'masuk',
                    'sumber'         => 'transfer',
                    'keterangan'     => 'Transfer dari gudang',
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
        // Cari data transfer
        $transfer = TransferBarang::where('uuid', $uuid)->firstOrFail();

        // Ambil produk berdasarkan UUID
        $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();
        if ($produk->count() !== count($request->uuid_produk)) {
            return response()->json(['status' => 'error', 'message' => 'Ada produk yang tidak ditemukan.'], 404);
        }

        // Update header transfer
        $transfer->update([
            'tanggal_transfer' => $request->tanggal_transfer,
            'updated_by'       => Auth::user()->nama,
        ]);

        // Hapus detail lama
        DetailTransferBarang::where('uuid_transfer_barangs', $transfer->uuid)->delete();

        // Simpan detail baru
        foreach ($request->uuid_produk as $index => $uuid_produk) {
            DetailTransferBarang::create([
                'uuid_transfer_barangs' => $transfer->uuid,
                'uuid_produk'           => $uuid_produk,
                'qty'                   => $request->qty[$index],
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Transfer berhasil diperbarui']);
    }

    public function delete($params)
    {
        // Cari transfer_barang yang mau dihapus
        $transfer_barang = TransferBarang::where('uuid', $params)->firstOrFail();

        // Hapus detail transfer_barang
        DetailTransferBarang::where('uuid_transfer_barangs', $transfer_barang->uuid)->delete();

        // Hapus transfer_barang utama
        $transfer_barang->delete();

        return response()->json(['status' => 'success']);
    }
}

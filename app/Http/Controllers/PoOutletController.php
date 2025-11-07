<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePoOutletRequest;
use App\Http\Requests\UpdatePoOutletRequest;
use App\Models\DetailPoOutlet;
use App\Models\Outlet;
use App\Models\PoOutlet;
use App\Models\Produk;
use App\Models\StatusBarang;
use App\Models\Suplayer;
use App\Models\Wirehouse;
use App\Models\WirehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PoOutletController extends Controller
{
    public function index()
    {
        $module = 'Po';
        $produk = Produk::select('uuid', 'nama_barang')->get();
        $suplayers = Suplayer::select('uuid', 'nama')->get();
        return view('outlet.pooutlet.index', compact('module', 'produk', 'suplayers'));
    }

    public function getProdukBySuplayer($params)
    {
        $produks = Produk::where('uuid_suplayer', $params)
            ->select('uuid', 'nama_barang')
            ->get();

        return response()->json($produks);
    }

    public function vw_pusat()
    {
        $module = 'Po Outlet';
        return view('pages.pooutlet.index', compact('module'));
    }

    public function get_vw_outlet(Request $request)
    {
        $columns = [
            'po_outlets.uuid' => 'uuid',
            'po_outlets.no_po' => 'no_po',
            'po_outlets.tanggal_transaksi' => 'tanggal_transaksi',
            'po_outlets.keterangan' => 'keterangan',
            'po_outlets.created_by' => 'created_by',
            'po_outlets.updated_by' => 'updated_by',
            'po_outlets.status' => 'status',
            'po_outlets.created_at' => 'created_at', // ✅ Tambahkan kolom ini
            'COALESCE(SUM(detail_po_outlets.qty * produks.hrg_modal),0)' => 'total_harga',
            'COALESCE(SUM(detail_po_outlets.qty),0)' => 'total_qty',
            "JSON_ARRAYAGG(
            JSON_OBJECT(
                'uuid_produk', detail_po_outlets.uuid_produk,
                'nama_barang', produks.nama_barang,
                'qty', detail_po_outlets.qty
            )
        )" => 'detail_produk'
        ];

        $totalData = PoOutlet::count();

        // SELECT alias
        $selects = [];
        foreach ($columns as $dbCol => $alias) {
            $selects[] = "$dbCol as $alias";
        }

        $query = PoOutlet::selectRaw(implode(", ", $selects))
            ->leftJoin('detail_po_outlets', 'detail_po_outlets.uuid_po_outlet', '=', 'po_outlets.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_po_outlets.uuid_produk')
            ->groupBy(
                'po_outlets.uuid',
                'po_outlets.no_po',
                'po_outlets.tanggal_transaksi',
                'po_outlets.keterangan',
                'po_outlets.created_by',
                'po_outlets.updated_by',
                'po_outlets.status',
                'po_outlets.created_at' // ✅ ikutkan di groupBy agar bisa di-order
            );

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $dbCol => $alias) {
                    if (str_contains($dbCol, 'SUM') || str_contains($dbCol, 'JSON')) continue;
                    $q->orWhere($dbCol, 'like', "%{$search}%");
                }
            });
        }

        // Clone untuk totalFiltered
        $filteredQuery = clone $query;
        $totalFiltered = $filteredQuery->get()->count();

        // Sorting
        // if ($request->order) {
        //     $orderColIndex = $request->order[0]['column'];
        //     $orderDir = $request->order[0]['dir'];
        //     $dbCol = array_keys($columns)[$orderColIndex];
        //     $query->orderByRaw("$dbCol $orderDir");
        // } else {
        $query->orderBy('po_outlets.tanggal_transaksi', 'asc'); // ✅ urut dari input terakhir
        // }

        // Pagination
        $data = $query
            ->skip($request->start)
            ->take($request->length)
            ->get();

        // Decode JSON kolom detail_produk
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

    public function aprove_po_outlet(Request $request, $params)
    {
        // Cari po_outlet yang mau diapprove
        $po_outlet = PoOutlet::where('uuid', $params)->firstOrFail();

        // Update status atau field yang diperlukan
        $po_outlet->status = $request->status; // Misalnya, ganti status menjadi approved
        $po_outlet->save();

        // Tambahkan logika lain jika perlu, seperti mengirim notifikasi

        return response()->json(['status' => 'success', 'message' => 'PO Outlet berhasil diapprove.']);
    }

    public function get(Request $request)
    {
        // Kolom: database => alias
        $columns = [
            'po_outlets.uuid' => 'uuid',
            'po_outlets.no_po' => 'no_po',
            'po_outlets.tanggal_transaksi' => 'tanggal_transaksi',
            'po_outlets.keterangan' => 'keterangan',
            'po_outlets.created_by' => 'created_by',
            'po_outlets.updated_by' => 'updated_by',
            'po_outlets.status' => 'status',
            'COALESCE(SUM(detail_po_outlets.qty * produks.hrg_modal),0)' => 'total_harga',
        ];

        $totalData = PoOutlet::where('uuid_user', Auth::user()->uuid)->count();

        // SELECT dengan alias
        $selects = [];
        foreach ($columns as $dbCol => $alias) {
            $selects[] = "$dbCol as $alias";
        }

        $query = PoOutlet::where('uuid_user', Auth::user()->uuid)->selectRaw(implode(", ", $selects))
            ->leftJoin('detail_po_outlets', 'detail_po_outlets.uuid_po_outlet', '=', 'po_outlets.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_po_outlets.uuid_produk')
            ->groupBy(
                'po_outlets.uuid',
                'po_outlets.no_po',
                'po_outlets.tanggal_transaksi',
                'po_outlets.keterangan',
                'po_outlets.created_by',
                'po_outlets.updated_by',
                'po_outlets.status'
            );

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $dbCol => $alias) {
                    if (str_contains($dbCol, 'SUM') || str_contains($dbCol, 'JSON')) continue;
                    $q->orWhere($dbCol, 'like', "%{$search}%");
                }
            });
        }

        // Clone untuk totalFiltered
        $filteredQuery = clone $query;
        $totalFiltered = $filteredQuery->get()->count();

        // Sorting
        // if ($request->order) {
        //     $orderColIndex = $request->order[0]['column'];
        //     $orderDir = $request->order[0]['dir'];
        //     $dbCol = array_keys($columns)[$orderColIndex];
        //     $query->orderByRaw("$dbCol $orderDir");
        // } else {
        $query->orderBy('po_outlets.tanggal_transaksi', 'asc'); // ✅ urut dari input terakhir
        // }

        // Pagination
        $data = $query
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

    public function store(StorePoOutletRequest $request)
    {
        // Ambil produk berdasarkan UUID
        $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();

        // Pastikan semua produk ditemukan
        if ($produk->count() !== count($request->uuid_produk)) {
            return response()->json(['status' => 'error', 'message' => 'Ada produk yang tidak ditemukan.'], 404);
        }

        // Simpan po_outlet
        // Format tanggal -> DDMMYY
        $today = now()->format('dmy');
        $prefix = "PO-" . $today;

        // Cari PO terakhir di hari ini
        $lastPo = PoOutlet::whereDate('created_at', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastPo) {
            // Ambil angka urut terakhir (setelah prefix)
            $lastNumber = intval(substr($lastPo->no_po, strrpos($lastPo->no_po, '-') + 1));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $no_po = $prefix . "-" . $nextNumber;

        // Simpan data
        $po_outlet = PoOutlet::create([
            'uuid_user'      => Auth::user()->uuid,
            'no_po'              => $no_po,
            'tanggal_transaksi'  => $request->tanggal_transaksi,
            'keterangan'         => $request->keterangan,
            'created_by'         => Auth::user()->nama,
        ]);

        // Simpan detail po_outlet
        foreach ($request->uuid_produk as $index => $uuid_produk) {
            $item = $produk->firstWhere('uuid', $uuid_produk);
            DetailPoOutlet::create([
                'uuid_po_outlet' => $po_outlet->uuid,
                'uuid_produk'    => $uuid_produk,
                'qty'            => $request->qty[$index],
            ]);
        }

        StatusBarang::create([
            'uuid_log_barang' => $po_outlet->uuid,
            'ref' => $no_po,
            'ketarangan' => $request->keterangan,
        ]);

        // Ambil gudang pusat (jangan bikin baru setiap kali)
        $warehouse = Wirehouse::where('tipe', 'gudang')
            ->where('lokasi', 'outlet')
            ->where('uuid_user', Auth::user()->uuid)
            ->first();

        if (!$warehouse) {
            $warehouse = Wirehouse::create([
                'uuid_user'  => Auth::user()->uuid,
                'tipe'       => 'gudang',
                'lokasi'     => 'outlet',
                'keterangan' => 'Gudang outlet ' . Outlet::where('uuid_user', Auth::user()->uuid)->value('nama_outlet'),
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
                'keterangan'     => 'Pembelian dari outlet: ' . Outlet::where('uuid_user', Auth::user()->uuid)->value('nama_outlet'),
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function edit($uuid)
    {
        $po_outlet = PoOutlet::where('uuid', $uuid)->first();
        if (!$po_outlet) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }

        // Ambil detail produk
        $detailProduk = DetailPoOutlet::where('uuid_po_outlet', $po_outlet->uuid)
            ->select('uuid_produk', 'qty')
            ->get();
        $po_outlet->detail_produk = $detailProduk;

        return response()->json($po_outlet);
    }

    public function update(StorePoOutletRequest $request, $uuid)
    {
        DB::beginTransaction();
        try {
            // Cari data po_outlet
            $po_outlet = PoOutlet::where('uuid', $uuid)->firstOrFail();

            // Ambil produk berdasarkan UUID
            $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();

            if ($produk->count() !== count($request->uuid_produk)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ada produk yang tidak ditemukan.'
                ], 404);
            }

            // Update data po_outlet
            $po_outlet->update([
                'uuid_user'      => Auth::user()->uuid,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'keterangan'        => $request->keterangan,
                'updated_by'        => Auth::user()->nama,
            ]);

            $statusbarang = StatusBarang::where('uuid_log_barang', $po_outlet->uuid)->first();
            if ($statusbarang) {
                $statusbarang->update([
                    'uuid_log_barang' => $po_outlet->uuid,
                    'ref' => $po_outlet->no_po,
                    'ketarangan' => $request->keterangan,
                ]);
            }

            // Ambil gudang outlet
            $warehouse = Wirehouse::firstOrCreate(
                [
                    'uuid_user' => Auth::user()->uuid,
                    'tipe'      => 'gudang',
                    'lokasi'    => 'outlet',
                ],
                [
                    'keterangan' => 'Gudang outlet ' . Outlet::where('uuid_user', Auth::user()->uuid)->value('nama_outlet'),
                ]
            );

            // Rollback stok lama (hapus dari WirehouseStock)
            WirehouseStock::where('uuid_warehouse', $warehouse->uuid)
                ->where('sumber', 'pembelian')
                ->where('keterangan', 'like', '%' . $po_outlet->no_invoice . '%')
                ->delete();

            // Hapus detail lama
            DetailPoOutlet::where('uuid_po_outlet', $po_outlet->uuid)->delete();

            // Simpan detail baru + stok baru
            foreach ($request->uuid_produk as $index => $uuid_produk) {
                $qty = $request->qty[$index];

                DetailPoOutlet::create([
                    'uuid_po_outlet' => $po_outlet->uuid,
                    'uuid_produk'    => $uuid_produk,
                    'qty'            => $qty,
                ]);

                WirehouseStock::create([
                    'uuid_warehouse' => $warehouse->uuid,
                    'uuid_produk'    => $uuid_produk,
                    'qty'            => $qty,
                    'jenis'          => 'masuk',
                    'sumber'         => 'pembelian',
                    'keterangan'     => 'Update PO Outlet #' . $po_outlet->no_invoice,
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($params)
    {
        DB::beginTransaction();
        try {
            $po_outlet = PoOutlet::where('uuid', $params)->firstOrFail();

            // Ambil gudang outlet
            $warehouse = Wirehouse::where('uuid_user', Auth::user()->uuid)
                ->where('tipe', 'gudang')
                ->where('lokasi', 'outlet')
                ->first();

            if ($warehouse) {
                // Hapus stok terkait PO ini
                WirehouseStock::where('uuid_warehouse', $warehouse->uuid)
                    ->where('sumber', 'pembelian')
                    ->where('keterangan', 'like', '%' . $po_outlet->no_invoice . '%')
                    ->delete();
            }

            // Hapus detail
            DetailPoOutlet::where('uuid_po_outlet', $po_outlet->uuid)->delete();

            StatusBarang::where('uuid_log_barang', $po_outlet->uuid)->delete();

            // Hapus header
            $po_outlet->delete();

            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

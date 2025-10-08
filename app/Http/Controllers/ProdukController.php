<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOpnameRequest;
use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use App\Models\DetailPembelian;
use App\Models\KasirOutlet;
use App\Models\Kategori;
use App\Models\Opname;
use App\Models\Outlet;
use App\Models\Pembelian;
use App\Models\PriceHistory;
use App\Models\Produk;
use App\Models\StatusBarang;
use App\Models\SubKategori;
use App\Models\Suplayer;
use App\Models\Wirehouse;
use App\Models\WirehouseStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index()
    {
        $module = 'Produk';
        // Ambil data kategori dan suplayer untuk dropdown
        $kategoris = Kategori::select('uuid', 'nama_kategori', 'sub_kategori')->get();
        $suplayers = Suplayer::select('uuid', 'nama')->get();

        $wirehouse = Wirehouse::all();
        $wirehouse->map(function ($item) {
            $outlet = Outlet::where('uuid_user', $item->uuid_user)->first();

            $item->nama_outlet = $outlet ? $outlet->nama_outlet : 'Pusat';

            return $item;
        });

        return view('pages.produk.index', compact('module', 'kategoris', 'suplayers', 'wirehouse'));
    }

    public function getSubKategori($uuid)
    {
        $kategori = Kategori::where('uuid', $uuid)->firstOrFail();
        $sub = json_decode($kategori->sub_kategori, true);

        return response()->json($sub);
    }


    public function price_history($params)
    {
        $produk = Produk::where('uuid', $params)->first();
        $module = 'Price History ' . $produk->nama_barang;
        return view('pages.produk.price_history', compact('module', 'produk'));
    }

    public function opname_stock($uuid_produk)
    {
        $user = Auth::user();
        $produk = Produk::where('uuid', $uuid_produk)->firstOrFail();

        $wirehouse = Wirehouse::all();
        $wirehouse->map(function ($item) {
            $outlet = Outlet::where('uuid_user', $item->uuid_user)->first();

            $item->nama_outlet = $outlet ? $outlet->nama_outlet : 'Pusat';

            return $item;
        });

        // Ambil stok dari wirehouse_stocks hanya untuk gudang pusat
        $stok_wirehouse = DB::table('wirehouse_stocks as ws')
            ->join('wirehouses as w', 'w.uuid', '=', 'ws.uuid_warehouse')
            ->where('ws.uuid_produk', $produk->uuid)
            ->where('w.tipe', 'pusat') // hanya gudang pusat
            ->sum('ws.qty');

        // Ambil hasil opname terakhir untuk produk ini oleh user pusat
        $stok_opname = DB::table('opnames')
            ->where('uuid_user', $user->uuid)
            ->where('uuid_produk', $produk->uuid)
            ->orderByDesc('created_at')
            ->value('stock');

        // Gunakan hasil opname terakhir jika ada, kalau tidak gunakan stok wirehouse
        $total_stok = $stok_opname !== null ? $stok_opname : $stok_wirehouse;

        $module = 'Opname Stock Gudang Pusat - ' . $produk->nama_barang . ' (' . $total_stok . ')';

        return view('pages.produk.opname_stock', compact('module', 'produk', 'total_stok'));
    }

    public function opname_stock_outlet($uuid_produk)
    {
        $user = Auth::user();
        $produk = Produk::where('uuid', $uuid_produk)->firstOrFail();

        $wirehouse = Wirehouse::where('uuid_user', Auth::user()->uuid)->get();
        $wirehouse->map(function ($item) {
            $outlet = Outlet::where('uuid_user', $item->uuid_user)->first();

            $item->nama_outlet = $outlet ? $outlet->nama_outlet : 'Pusat';

            return $item;
        });

        // Ambil data outlet tempat user / kasir login
        $kasir_outlet = KasirOutlet::where('uuid_user', $user->uuid)->first();

        // 🔹 Ambil stok dari wirehouse untuk toko (outlet)
        $stok_toko = DB::table('wirehouse_stocks as ws')
            ->join('wirehouses as w', 'w.uuid', '=', 'ws.uuid_warehouse')
            ->where('ws.uuid_produk', $produk->uuid)
            ->where('w.uuid_user', $user->uuid)
            ->where('w.lokasi', 'outlet')
            ->where('w.tipe', 'toko')
            ->sum('ws.qty');

        // 🔹 Ambil stok dari wirehouse untuk gudang pusat
        $stok_gudang = DB::table('wirehouse_stocks as ws')
            ->join('wirehouses as w', 'w.uuid', '=', 'ws.uuid_warehouse')
            ->where('ws.uuid_produk', $produk->uuid)
            ->where('w.tipe', 'pusat')
            ->sum('ws.qty');

        // 🔹 Ambil hasil opname terakhir untuk outlet ini (kalau ada)
        $stok_opname = DB::table('opnames')
            ->where('uuid_user', $user->uuid)
            ->where('uuid_produk', $produk->uuid)
            ->orderByDesc('created_at')
            ->value('stock');

        // 🔹 Gunakan opname jika ada, kalau tidak pakai stok toko
        $total_stok = $stok_opname !== null ? $stok_opname : $stok_toko;

        $module = 'Opname Stock ' . $produk->nama_barang . ' (Toko: ' . $stok_toko . ' | Gudang: ' . $stok_gudang . ')';

        return view('outlet.produk.opname_stock', compact('module', 'produk', 'stok_toko', 'stok_gudang', 'total_stok', 'wirehouse'));
    }

    public function get_price_history(Request $request, $params)
    {
        $produk = Produk::where('uuid', $params)->first();
        $columns = [
            'price_historys.uuid',
            'price_historys.uuid_produk',
            'price_historys.harga',
            'price_historys.created_at',
            'produks.nama_barang as nama_barang',
        ];

        // Hitung total data tanpa filter
        $totalData = PriceHistory::where('uuid_produk', $produk->uuid)->count();

        // Query utama dengan join ke tabel produk
        $query = PriceHistory::select($columns)
            ->join('produks', 'produks.uuid', '=', 'price_historys.uuid_produk')
            ->where('price_historys.uuid_produk', $produk->uuid);

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    // Hilangkan alias saat searching
                    $colName = explode(' as ', $column)[0];
                    $q->orWhere($colName, 'like', "%{$search}%");
                }
            });
        }

        // Hitung total data setelah filter
        $totalFiltered = $query->count();

        // Sorting
        if (!empty($request->order)) {
            $orderCol = explode(' as ', $columns[$request->order[0]['column']])[0];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest('created_at');
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        // Ambil data
        $data = $query->get();

        // Response JSON untuk DataTables
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function get(Request $request)
    {
        $user = Auth::user();

        $columns = [
            'produks.uuid',
            'produks.uuid_kategori',
            'produks.uuid_suplayer',
            'produks.sub_kategori',
            'produks.kode',
            'produks.nama_barang',
            'produks.merek',
            'produks.hrg_modal',
            'produks.profit',
            'produks.minstock',
            'produks.maxstock',
            'produks.satuan',
            'produks.foto',
            'produks.created_at',
            'produks.created_by',
            'produks.update_by',
            'kategoris.nama_kategori as kategori',
            'suplayers.nama as suplayer',
        ];

        $totalData = Produk::count();

        $query = Produk::select(array_merge($columns, [
            DB::raw("(SELECT COALESCE(SUM(dp.qty),0)
            FROM detail_pembelians dp
            JOIN pembelians pb ON pb.uuid = dp.uuid_pembelian
            WHERE dp.uuid_produk = produks.uuid) as total_pembelian"),

            DB::raw("(SELECT COALESCE(SUM(dk.qty),0)
            FROM detail_pengiriman_barangs dk
            JOIN pengiriman_barangs pk ON pk.uuid = dk.uuid_pengiriman_barang
            WHERE dk.uuid_produk = produks.uuid) as total_pengiriman"),

            DB::raw("(SELECT COALESCE(SUM(o.stock),0)
            FROM opnames o
            WHERE o.uuid_user = '" . $user->uuid . "'
            AND o.uuid_produk = produks.uuid) as total_opname"),

            // ==== Total stok dengan logika opname + filter warehouse ====
            DB::raw("
(
    CASE
        WHEN EXISTS (
            SELECT 1 FROM opnames o
            WHERE o.uuid_user = '" . $user->uuid . "'
            AND o.uuid_produk = produks.uuid
        )
        THEN (
            (SELECT o.stock
             FROM opnames o
             WHERE o.uuid_user = '" . $user->uuid . "'
             AND o.uuid_produk = produks.uuid
             ORDER BY o.created_at DESC
             LIMIT 1)
            +
            COALESCE((
                SELECT SUM(ws.qty)
                FROM wirehouse_stocks ws
                JOIN wirehouses w ON w.uuid = ws.uuid_warehouse
                WHERE ws.uuid_produk = produks.uuid
                " . (
                $request->filled('uuid_wirehouse')
                ? "AND w.uuid = '" . $request->uuid_wirehouse . "'"
                : ""
            ) . "
                AND ws.created_at > (
                    SELECT o2.created_at
                    FROM opnames o2
                    WHERE o2.uuid_user = '" . $user->uuid . "'
                    AND o2.uuid_produk = produks.uuid
                    ORDER BY o2.created_at DESC
                    LIMIT 1
                )
            ), 0)
        )
        ELSE (
            COALESCE((
                SELECT SUM(ws.qty)
                FROM wirehouse_stocks ws
                JOIN wirehouses w ON w.uuid = ws.uuid_warehouse
                WHERE ws.uuid_produk = produks.uuid
                " . (
                $request->filled('uuid_wirehouse')
                ? "AND w.uuid = '" . $request->uuid_wirehouse . "'"
                : ""
            ) . "
            ), 0)
        )
    END
) AS total_stok
"),

            DB::raw('
            ROUND(
                (
                    CAST(produks.hrg_modal AS DECIMAL(15,2))
                    + (CAST(produks.hrg_modal AS DECIMAL(15,2)) * CAST(produks.profit AS DECIMAL(15,2)) / 100)
                ) / 1000
            ) * 1000 as harga_jual
        ')
        ]))
            ->leftJoin('kategoris', 'kategoris.uuid', '=', 'produks.uuid_kategori')
            ->leftJoin('suplayers', 'suplayers.uuid', '=', 'produks.uuid_suplayer');

        // ==== Filter kategori dan supplier
        if ($request->filled('uuid_kategori')) {
            $query->where('produks.uuid_kategori', $request->uuid_kategori);
        }
        if ($request->filled('uuid_suplayer')) {
            $query->where('produks.uuid_suplayer', $request->uuid_suplayer);
        }

        // ==== Filter warehouse (kalau bukan pusat)
        if (!$user->is_pusat && $request->filled('uuid')) {
            $query->whereExists(function ($sub) use ($request) {
                $sub->select(DB::raw(1))
                    ->from('wirehouse_stocks as ws')
                    ->join('wirehouses as w', 'w.uuid', '=', 'ws.uuid_warehouse')
                    ->whereColumn('ws.uuid_produk', 'produks.uuid')
                    ->where('w.uuid', $request->uuid);
            });
        }

        // ==== Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $colName = explode(' as ', $column)[0];
                    $q->orWhere($colName, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $query->count();

        // ==== Sorting
        if (!empty($request->order)) {
            $columnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'asc';
            $orderCol = explode(' as ', $columns[$columnIndex] ?? 'produks.created_at')[0];
            $query->orderBy($orderCol, $orderDir)->orderBy('produks.created_at', 'desc');
        } else {
            $query->orderBy('produks.created_at', 'desc');
        }

        // ==== Pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        // Hitung total harga modal
        $totalHargaModal = Produk::sum('hrg_modal');

        // Hitung total harga jual (pakai formula dari select harga_jual)
        $totalHargaJual = Produk::select(DB::raw('
    SUM(
        ROUND(
            (
                CAST(hrg_modal AS DECIMAL(15,2))
                + (CAST(hrg_modal AS DECIMAL(15,2)) * CAST(profit AS DECIMAL(15,2)) / 100)
            ) / 1000
        ) * 1000
    ) as total_harga_jual
'))->value('total_harga_jual');

        // Subquery untuk stok per produk
        $sub = Produk::select(
            'produks.uuid',
            'produks.hrg_modal',
            'produks.profit',
            DB::raw("
(
    CASE
        WHEN EXISTS (
            SELECT 1 FROM opnames o
            WHERE o.uuid_user = '" . $user->uuid . "'
            AND o.uuid_produk = produks.uuid
        )
        THEN (
            (SELECT o.stock
             FROM opnames o
             WHERE o.uuid_user = '" . $user->uuid . "'
             AND o.uuid_produk = produks.uuid
             ORDER BY o.created_at DESC
             LIMIT 1)
            +
            COALESCE((
                SELECT SUM(ws.qty)
                FROM wirehouse_stocks ws
                JOIN wirehouses w ON w.uuid = ws.uuid_warehouse
                WHERE ws.uuid_produk = produks.uuid
                " . (
                $request->filled('uuid_wirehouse')
                ? "AND w.uuid = '" . $request->uuid_wirehouse . "'"
                : ""
            ) . "
                AND ws.created_at > (
                    SELECT o2.created_at
                    FROM opnames o2
                    WHERE o2.uuid_user = '" . $user->uuid . "'
                    AND o2.uuid_produk = produks.uuid
                    ORDER BY o2.created_at DESC
                    LIMIT 1
                )
            ), 0)
        )
        ELSE (
            COALESCE((
                SELECT SUM(ws.qty)
                FROM wirehouse_stocks ws
                JOIN wirehouses w ON w.uuid = ws.uuid_warehouse
                WHERE ws.uuid_produk = produks.uuid
                " . (
                $request->filled('uuid_wirehouse')
                ? "AND w.uuid = '" . $request->uuid_wirehouse . "'"
                : ""
            ) . "
            ), 0)
        )
    END
) AS total_stok
")
        );

        // Bungkus subquery supaya bisa dihitung SUM-nya
        $wrapped = DB::table(DB::raw("({$sub->toSql()}) as x"))
            ->mergeBindings($sub->getQuery());

        // Total stock
        $totalStock = $wrapped->sum('total_stok');

        // Total harga modal × stok
        $totalHargaModalKaliStock = $wrapped->selectRaw('SUM(hrg_modal * total_stok) as total')->value('total');

        // Total harga jual × stok (ikutin formula jual)
        $totalHargaJualKaliStock = $wrapped->selectRaw('SUM(
    ROUND(
        (
            CAST(hrg_modal AS DECIMAL(15,2))
            + (CAST(hrg_modal AS DECIMAL(15,2)) * CAST(profit AS DECIMAL(15,2)) / 100)
        ) / 1000
    ) * 1000 * total_stok
) as total')->value('total');

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
            'total' => [
                'hrg_modal'              => $totalHargaModal,
                'harga_jual'             => $totalHargaJual,
                'stock'                  => $totalStock,
                'hrg_modal_kali_stock'   => $totalHargaModalKaliStock,
                'harga_jual_kali_stock'  => $totalHargaJualKaliStock,
            ]
        ]);
    }

    public function store(StoreProdukRequest $request)
    {
        $path = null;
        if ($request->hasFile('foto')) {
            // Buat nama unik
            $fileName = time() . '_' . uniqid() . '.' . $request->foto->extension();

            // Simpan di storage/app/public/foto_produk
            $path = $request->foto->storeAs('foto_produk', $fileName, 'public');
        }

        if ($request->kode) {
            $kode = $request->kode;
        } else {
            $kode = '9' . str_pad(mt_rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
        }

        $produk = Produk::create([
            'uuid_kategori' => $request->uuid_kategori,
            'uuid_suplayer' => $request->uuid_suplayer,
            'sub_kategori' => $request->sub_kategori,
            'kode' => $kode,
            'nama_barang' => $request->nama_barang,
            'merek' => $request->merek,
            'hrg_modal' => preg_replace('/\D/', '', $request->hrg_modal),
            'profit' => $request->profit,
            'minstock' => $request->minstock,
            'maxstock' => $request->maxstock,
            'satuan' => $request->satuan,
            'profit_a' => $request->profit_a,
            'profit_b' => $request->profit_b,
            'profit_c' => $request->profit_c,
            'foto' => $path,
            'created_by' => Auth::user()->nama
        ]);

        PriceHistory::create([
            'uuid_produk' => $produk->uuid,
            'harga' => preg_replace('/\D/', '', $request->hrg_modal),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function store_opname(StoreOpnameRequest $store)
    {
        $produk = Produk::where('uuid', $store->uuid_produk)->first();

        if (!$produk) {
            return response()->json(['status' => 'error', 'message' => 'Produk tidak ditemukan'], 404);
        }

        // Ambil gudang pusat
        $warehouse = Wirehouse::where('tipe', 'gudang')
            ->where('lokasi', 'pusat')
            ->first();

        if (!$warehouse) {
            return response()->json(['status' => 'error', 'message' => 'Gudang pusat tidak ditemukan'], 404);
        }

        // Hitung stok sistem saat ini (total masuk - total keluar untuk produk tsb)
        $stok_sistem = WirehouseStock::where('uuid_warehouse', $warehouse->uuid)
            ->where('uuid_produk', $produk->uuid)
            ->sum(DB::raw("CASE WHEN jenis='masuk' THEN qty ELSE -qty END"));

        // Simpan data opname
        $opname = Opname::create([
            'uuid_produk' => $produk->uuid,
            'uuid_user'   => Auth::user()->uuid,
            'stock'       => $store->stock,
            'keterangan'  => $store->keterangan,
        ]);

        StatusBarang::create([
            'uuid_log_barang' => $opname->uuid,
            'ref' => $produk->nama_barang,
            'ketarangan' => $store->keterangan
        ]);

        // Selisih antara stok fisik dengan stok sistem
        $selisih = $store->stock - $stok_sistem;

        if ($selisih != 0) {
            WirehouseStock::create([
                'uuid_warehouse' => $warehouse->uuid,
                'uuid_produk'    => $produk->uuid,
                'qty'            => $store->stock,
                'jenis'          => $selisih > 0 ? 'masuk' : 'keluar',
                'sumber'         => 'opname',
                'keterangan'     => 'Penyesuaian stok opname #' . $produk->nama_barang,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function store_opname_outlet(StoreOpnameRequest $store)
    {
        return DB::transaction(function () use ($store) {
            $produk = Produk::where('uuid', $store->uuid_produk)->first();

            if (!$produk) {
                return response()->json(['status' => 'error', 'message' => 'Produk tidak ditemukan'], 404);
            }

            // Ambil outlet langsung dari request
            $uuid_user = Auth::user()->uuid;

            if (!$uuid_user) {
                return response()->json(['status' => 'error', 'message' => 'UUID Outlet harus diisi'], 422);
            }

            // Ambil warehouse GUDANG OUTLET
            $warehouseOutlet = Wirehouse::where('uuid', $store->uuid_wirehouse)
                ->first();

            if (!$warehouseOutlet) {
                return response()->json(['status' => 'error', 'message' => 'Wirehouse tidak ditemukan'], 404);
            }

            // Hitung stok sistem saat ini (masuk - keluar)
            $stok_sistem = WirehouseStock::where('uuid_warehouse', $warehouseOutlet->uuid)
                ->where('uuid_produk', $produk->uuid)
                ->sum(DB::raw("CASE WHEN jenis='masuk' THEN qty ELSE -qty END"));

            // Simpan data opname
            $opname = Opname::create([
                'uuid_produk'  => $produk->uuid,
                'uuid_outlet'  => $uuid_user,
                'uuid_user'    => Auth::user()->uuid,
                'stock'        => $store->stock,
                'keterangan'   => $store->keterangan,
            ]);

            StatusBarang::create([
                'uuid_log_barang' => $opname->uuid,
                'ref' => $produk->nama_barang,
                'ketarangan' => $store->keterangan
            ]);

            // Hitung selisih stok
            $selisih = $store->stock - $stok_sistem;

            if ($selisih != 0) {
                // Hapus stok opname sebelumnya (jika ada)
                WirehouseStock::where('uuid_warehouse', $warehouseOutlet->uuid)
                    ->where('uuid_produk', $produk->uuid)
                    ->where('sumber', 'opname')
                    ->delete();

                // Tambahkan stok opname baru
                WirehouseStock::create([
                    'uuid_warehouse' => $warehouseOutlet->uuid,
                    'uuid_produk'    => $produk->uuid,
                    'qty'            => $store->stock,
                    'jenis'          => $selisih > 0 ? 'masuk' : 'keluar',
                    'sumber'         => 'opname',
                    'keterangan'     => 'Penyesuaian stok opname #' . $produk->nama_barang,
                ]);
            }

            return response()->json(['status' => 'success']);
        });
    }

    public function edit($params)
    {
        return response()->json(Produk::where('uuid', $params)->first());
    }

    public function update(UpdateProdukRequest $update, $params)
    {
        $produk = Produk::where('uuid', $params)->first();

        // Ambil harga modal lama & baru
        $hargaModalLama = (int) $produk->hrg_modal;
        $hargaModalBaru = (int) preg_replace('/\D/', '', $update->hrg_modal);

        if ($update->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
                Storage::disk('public')->delete($produk->foto);
            }

            // Simpan foto baru
            $fileName = time() . '_' . uniqid() . '.' . $update->foto->extension();
            $path = $update->foto->storeAs('foto_produk', $fileName, 'public');

            $produk->foto = $path;
        }

        $produk->update([
            'uuid_kategori' => $update->uuid_kategori,
            'uuid_suplayer' => $update->uuid_suplayer,
            'sub_kategori' => $update->sub_kategori,
            'kode' => $update->kode,
            'nama_barang' => $update->nama_barang,
            'merek' => $update->merek,
            'hrg_modal' => preg_replace('/\D/', '', $update->hrg_modal),
            'profit' => $update->profit,
            'minstock' => $update->minstock,
            'maxstock' => $update->maxstock,
            'satuan' => $update->satuan,
            'profit_a' => $update->profit_a,
            'profit_b' => $update->profit_b,
            'profit_c' => $update->profit_c,
            'update_by' => Auth::user()->nama
        ]);

        // Tambahkan price history hanya jika modal berubah
        if ($hargaModalLama !== $hargaModalBaru) {
            PriceHistory::create([
                'uuid_produk' => $produk->uuid,
                'harga' => $hargaModalBaru,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $produk = Produk::where('uuid', $params)->first();

        // Cek apakah produk sudah pernah dipakai di detail pembelian
        $cekDetail = DetailPembelian::where('uuid_produk', $produk->uuid)->exists();

        if ($cekDetail) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Produk tidak bisa dihapus karena sudah tercatat di pembelian.'
            ], 400);
        }

        // Hapus foto jika ada
        if ($produk->foto && Storage::disk('public')->exists($produk->foto)) {
            Storage::disk('public')->delete($produk->foto);
        }

        // Hapus data produk
        $produk->delete();
        PriceHistory::where('uuid_produk', $produk->uuid)->delete();
        return response()->json(['status' => 'success']);
    }

    // outlet
    public function vw_outlet()
    {
        $module = 'Produk';
        // Ambil data kategori dan suplayer untuk dropdown
        $kategoris = Kategori::select('uuid', 'nama_kategori')->get();
        $suplayers = Suplayer::select('uuid', 'nama')->get();

        $wirehouse = Wirehouse::where('uuid_user', Auth::user()->uuid)->get();
        $wirehouse->map(function ($item) {
            $outlet = Outlet::where('uuid_user', $item->uuid_user)->first();

            $item->nama_outlet = $outlet ? $outlet->nama_outlet : 'Pusat';

            return $item;
        });

        return view('outlet.produk.index', compact('module', 'kategoris', 'suplayers', 'wirehouse'));
    }

    public function get_outlet(Request $request)
    {
        $user = Auth::user();
        $columns = [
            'produks.uuid',
            'produks.uuid_kategori',
            'produks.uuid_suplayer',
            'produks.sub_kategori',
            'produks.kode',
            'produks.nama_barang',
            'produks.merek',
            'produks.hrg_modal',
            'produks.profit',
            'produks.minstock',
            'produks.maxstock',
            'produks.satuan',
            'produks.foto',
            'produks.created_at',
            'produks.created_by',
            'produks.update_by',
            'kategoris.nama_kategori as kategori',
            'suplayers.nama as suplayer',
        ];

        $totalData = Produk::count();

        $query = Produk::select(array_merge($columns, [
            // total pengiriman (barang keluar pusat)
            DB::raw("(SELECT COALESCE(SUM(dk.qty),0)
                  FROM detail_pengiriman_barangs dk
                  JOIN pengiriman_barangs pk ON pk.uuid = dk.uuid_pengiriman_barang
                  WHERE dk.uuid_produk = produks.uuid) as total_pengiriman"),

            DB::raw("(SELECT COALESCE(SUM(dt.qty),0)
                  FROM detail_transfer_barangs dt
                  JOIN transfer_barangs tb ON tb.uuid = dt.uuid_transfer_barangs
                  WHERE dt.uuid_produk = produks.uuid) as total_transfer"),

            // total opname
            DB::raw("(SELECT COALESCE(SUM(o.stock),0)
                  FROM opnames o
                  WHERE o.uuid_user = '" . Auth::user()->uuid . "'
                  AND o.uuid_produk = produks.uuid) as total_opname"),

            // total stok dihitung dari 3 sumber
            DB::raw("
(
    CASE
        WHEN EXISTS (
            SELECT 1 FROM opnames o
            WHERE o.uuid_user = '" . $user->uuid . "'
            AND o.uuid_produk = produks.uuid
        )
        THEN (
            COALESCE((
                -- Tambahkan semua pergerakan stok setelah opname terakhir
                SELECT SUM(CASE WHEN ws.jenis = 'masuk' THEN ws.qty ELSE -ws.qty END)
                FROM wirehouse_stocks ws
                JOIN wirehouses w ON w.uuid = ws.uuid_warehouse
                WHERE ws.uuid_produk = produks.uuid
                " . (
                $request->filled('uuid_wirehouse')
                ? "AND w.uuid = '" . $request->uuid_wirehouse . "'"
                : ""
            ) . "
            ), 0)
        )
        ELSE (
            -- Jika belum pernah opname, total semua stok dari wirehouse terpilih
            COALESCE((
                SELECT SUM(CASE WHEN ws.jenis = 'masuk' THEN ws.qty ELSE -ws.qty END)
                FROM wirehouse_stocks ws
                JOIN wirehouses w ON w.uuid = ws.uuid_warehouse
                WHERE ws.uuid_produk = produks.uuid
                " . (
                $request->filled('uuid_wirehouse')
                ? "AND w.uuid = '" . $request->uuid_wirehouse . "'"
                : ""
            ) . "
            ), 0)
        )
    END
) AS total_stok
"),
            DB::raw('
    ROUND(
        (
            CAST(produks.hrg_modal AS DECIMAL(15,2))
            + (CAST(produks.hrg_modal AS DECIMAL(15,2)) * CAST(produks.profit AS DECIMAL(15,2)) / 100)
        ) / 1000
    ) * 1000 as harga_jual
')
        ]))
            ->leftJoin('kategoris', 'kategoris.uuid', '=', 'produks.uuid_kategori')
            ->leftJoin('suplayers', 'suplayers.uuid', '=', 'produks.uuid_suplayer');

        // ==== filter kategori & supplier
        if ($request->filled('uuid_kategori')) {
            $query->where('produks.uuid_kategori', $request->uuid_kategori);
        }
        if ($request->filled('uuid_suplayer')) {
            $query->where('produks.uuid_suplayer', $request->uuid_suplayer);
        }

        // ==== searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $colName = explode(' as ', $column)[0];
                    $q->orWhere($colName, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $query->count();

        // ==== sorting
        if (!empty($request->order)) {
            $columnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'asc'; // default ke 'asc'

            // Ambil nama kolom, hilangkan alias jika ada
            $orderCol = $columns[$columnIndex] ?? 'produks.created_at';
            $orderCol = explode(' as ', $orderCol)[0];

            // Jika user sorting di kolom selain created_at, gunakan secondary sort by created_at
            if ($orderCol !== 'produks.created_at') {
                $query->orderBy($orderCol, $orderDir)
                    ->orderBy('produks.created_at', 'desc');
            } else {
                // Kalau sorting di created_at, cukup satu order
                $query->orderBy($orderCol, $orderDir);
            }
        } else {
            // Default tampilkan data terbaru
            $query->orderBy('produks.created_at', 'desc');
        }

        // ==== pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        // Hitung total harga modal
        $totalHargaModal = Produk::sum('hrg_modal');

        // Hitung total harga jual (pakai formula dari select harga_jual)
        $totalHargaJual = Produk::select(DB::raw('
    SUM(
        ROUND(
            (
                CAST(hrg_modal AS DECIMAL(15,2))
                + (CAST(hrg_modal AS DECIMAL(15,2)) * CAST(profit AS DECIMAL(15,2)) / 100)
            ) / 1000
        ) * 1000
    ) as total_harga_jual
'))->value('total_harga_jual');

        // Subquery untuk stok per produk
        $sub = Produk::select(
            'produks.uuid',
            'produks.hrg_modal',
            'produks.profit',
            DB::raw("
(
    CASE
        WHEN EXISTS (
            SELECT 1 FROM opnames o
            WHERE o.uuid_user = '" . $user->uuid . "'
            AND o.uuid_produk = produks.uuid
        )
        THEN (
            -- stok terakhir opname
            (SELECT o.stock
             FROM opnames o
             WHERE o.uuid_user = '" . $user->uuid . "'
             AND o.uuid_produk = produks.uuid
             ORDER BY o.created_at DESC
             LIMIT 1)
            +
            COALESCE((
                SELECT SUM(ws.qty)
                FROM wirehouse_stocks ws
                JOIN wirehouses w ON w.uuid = ws.uuid_warehouse
                WHERE ws.uuid_produk = produks.uuid
                " . (
                // === Jika user bukan pusat, filter warehouse berdasarkan request
                !$user->is_pusat && $request->filled('uuid_wirehouse')
                ? "AND w.uuid = '" . $request->uuid_wirehouse . "'"
                : ""
            ) . "
                " . (
                // === Jika bukan pusat, hanya lihat warehouse miliknya
                !$user->is_pusat
                ? "AND w.uuid_user = '" . $user->uuid . "'"
                : ""
            ) . "
                " . (
                // === Jika user outlet, batasi lokasi ke outlet
                !$user->is_pusat
                ? "AND w.lokasi = 'outlet'"
                : ""
            ) . "
                AND ws.created_at > (
                    SELECT o2.created_at
                    FROM opnames o2
                    WHERE o2.uuid_user = '" . $user->uuid . "'
                    AND o2.uuid_produk = produks.uuid
                    ORDER BY o2.created_at DESC
                    LIMIT 1
                )
            ), 0)
        )
        ELSE (
            -- Jika belum opname, total semua pergerakan stok
            COALESCE((
                SELECT SUM(ws.qty)
                FROM wirehouse_stocks ws
                JOIN wirehouses w ON w.uuid = ws.uuid_warehouse
                WHERE ws.uuid_produk = produks.uuid
                " . (
                !$user->is_pusat && $request->filled('uuid_wirehouse')
                ? "AND w.uuid = '" . $request->uuid_wirehouse . "'"
                : ""
            ) . "
                " . (
                !$user->is_pusat
                ? "AND w.uuid_user = '" . $user->uuid . "'"
                : ""
            ) . "
                " . (
                !$user->is_pusat
                ? "AND w.lokasi = 'outlet'"
                : ""
            ) . "
            ), 0)
        )
    END
) AS total_stok
")
        );

        // Bungkus subquery supaya bisa dihitung SUM-nya
        $wrapped = DB::table(DB::raw("({$sub->toSql()}) as x"))
            ->mergeBindings($sub->getQuery());

        // Total stock
        $totalStock = $wrapped->sum('total_stok');

        // Total harga modal × stok
        $totalHargaModalKaliStock = $wrapped->selectRaw('SUM(hrg_modal * total_stok) as total')->value('total');

        // Total harga jual × stok (ikutin formula jual)
        $totalHargaJualKaliStock = $wrapped->selectRaw('SUM(
    ROUND(
        (
            CAST(hrg_modal AS DECIMAL(15,2))
            + (CAST(hrg_modal AS DECIMAL(15,2)) * CAST(profit AS DECIMAL(15,2)) / 100)
        ) / 1000
    ) * 1000 * total_stok
) as total')->value('total');

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
            'total' => [
                'hrg_modal'              => $totalHargaModal,
                'harga_jual'             => $totalHargaJual,
                'stock'                  => $totalStock,
                'hrg_modal_kali_stock'   => $totalHargaModalKaliStock,
                'harga_jual_kali_stock'  => $totalHargaJualKaliStock,
            ]
        ]);
    }

    public function kartu_stock($params)
    {
        $produk = Produk::where('uuid', $params)->first();
        $module = 'Kartu Stock ' . $produk->nama_barang;

        $wirehouse = Wirehouse::all();
        $wirehouse->map(function ($item) {
            $outlet = Outlet::where('uuid_user', $item->uuid_user)->first();

            $item->nama_outlet = $outlet ? $outlet->nama_outlet : 'Pusat';

            return $item;
        });

        return view('pages.produk.kartustock', compact('module', 'produk', 'wirehouse'));
    }

    public function get_kartu_stock(Request $request, $params)
    {
        $produk = Produk::where('uuid', $params)->first();
        $columns = [
            'wirehouse_stocks.uuid',
            'wirehouse_stocks.uuid_warehouse',
            'wirehouse_stocks.uuid_produk',
            'wirehouse_stocks.qty',
            'wirehouse_stocks.jenis',
            'wirehouse_stocks.sumber',
            'wirehouse_stocks.keterangan',
            'produks.nama_barang as nama_barang',
        ];

        // 🔹 Filter tanggal default bulan berjalan
        $tanggal_awal  = $request->get('tanggal_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggal_akhir = $request->get('tanggal_akhir', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Hitung total data tanpa filter
        $totalData = WirehouseStock::where('uuid_produk', $produk->uuid)->count();

        // Query utama
        $query = WirehouseStock::select($columns)
            ->join('produks', 'produks.uuid', '=', 'wirehouse_stocks.uuid_produk')
            ->join('wirehouses', 'wirehouses.uuid', '=', 'wirehouse_stocks.uuid_warehouse')
            ->where('wirehouse_stocks.uuid_produk', $produk->uuid)
            ->whereBetween('wirehouse_stocks.created_at', [
                Carbon::parse($tanggal_awal)->startOfDay(),
                Carbon::parse($tanggal_akhir)->endOfDay()
            ]);

        // 🔹 Filter berdasarkan outlet tertentu
        if (!empty($request->uuid)) {
            $query->where('wirehouses.uuid', $request->uuid);
        }

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $colName = explode(' as ', $column)[0];
                    $q->orWhere($colName, 'like', "%{$search}%");
                }
            });
        }

        // Hitung total data setelah filter
        $totalFiltered = $query->count();

        // Sorting
        if (!empty($request->order)) {
            $orderCol = explode(' as ', $columns[$request->order[0]['column']])[0];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest('wirehouse_stocks.created_at');
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        // Ambil data
        $data = $query->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function cetakBarcode(Request $request, $params)
    {
        $produk = Produk::where('uuid', $params)->firstOrFail();

        $jumlah = (int) $request->input('jumlah', 1);
        if ($jumlah % 2 != 0) $jumlah++; // genapkan

        // Setting ukuran label (mm)
        $dpi = 203; // DPI printer Zebra umum
        $labelWidthMM  = 33;
        $labelHeightMM = 15;

        // Konversi mm -> dot
        $singleWidth = round($labelWidthMM * ($dpi / 25.4));
        $labelHeight = round($labelHeightMM * ($dpi / 25.4));

        // Margin fixed
        $marginX = 5;
        $marginY = 10;

        // Data produk
        $nama   = strtoupper($produk->nama_barang);
        $harga  = round($produk->hrg_modal + ($produk->hrg_modal * $produk->profit / 100), -3);
        $harga  = number_format($harga, 0, ',', '.');
        $barcode = $produk->kode;

        // Perhitungan tinggi teks nama barang
        $fontHeight   = 16;   // tinggi font (dot)
        $charsPerLine = 17;   // kira2 muat 14 huruf per baris
        $calcLines    = ceil(mb_strlen($nama) / $charsPerLine);

        // maksimal 2 baris, minimal 1
        $lines = min(2, max(1, $calcLines));

        // kalau lebih dari 2 → potong teks
        if ($calcLines > 2) {
            $nama = mb_substr($nama, 0, $charsPerLine * 2);
        }

        // Hitung posisi barcode berdasarkan jumlah baris nama
        if ($lines == 1) {
            $barcodeYOffset = ($fontHeight * 1) + 3; // 1 baris → agak naik
        } else {
            $barcodeYOffset = ($fontHeight * 2) + 4;  // 2 baris → lebih turun
        }

        // Posisi harga setelah barcode
        $hargaYOffset = $barcodeYOffset + 55;

        $zpl = "";
        for ($i = 0; $i < $jumlah; $i += 2) {
            $zpl .= "^XA\n^CI28\n";
            $zpl .= "^PW" . ($singleWidth * 2) . "\n";
            $zpl .= "^LL$labelHeight\n";

            // ------------------------
            // KOLOM KIRI
            // ------------------------
            $zpl .= "
            ^FO" . ($marginX) . "," . ($marginY) . "
            ^A0N,$fontHeight,$fontHeight
            ^FB" . ($singleWidth - 20) . ",$lines,0,C,0
            ^FD$nama^FS

            ^BY1,2,35
            ^FO" . ($marginX + 15) . "," . ($marginY + $barcodeYOffset) . "^BCN,35,Y,N,N^FD>:$barcode^FS

            ^FO" . ($marginX) . "," . ($marginY + $hargaYOffset) . "
^A0N,27,23
^FB" . ($singleWidth - 20) . ",1,0,C,0
^FDRp. $harga^FS
        ";

            // ------------------------
            // KOLOM KANAN
            // ------------------------
            $xOffset = $singleWidth + 30 + $marginX;
            $zpl .= "
            ^FO$xOffset," . ($marginY) . "
            ^A0N,$fontHeight,$fontHeight
            ^FB" . ($singleWidth - 20) . ",$lines,0,C,0
            ^FD$nama^FS

            ^BY1,2,35
            ^FO" . ($xOffset + 15) . "," . ($marginY + $barcodeYOffset) . "^BCN,35,Y,N,N^FD>:$barcode^FS

            ^FO" . ($xOffset) . "," . ($marginY + $hargaYOffset) . "
^A0N,27,23
^FB" . ($singleWidth - 20) . ",1,0,C,0
^FDRp. $harga^FS
        ";

            $zpl .= "^XZ\n";
        }

        // Simpan file sementara
        $tmpFile = tempnam(sys_get_temp_dir(), 'zpl');
        file_put_contents($tmpFile, $zpl);

        // Kirim ke printer (pakai raw biar tidak diubah driver)
        exec("lp -d ZEBRA_RAW -o raw " . escapeshellarg($tmpFile));

        return response()->json([
            'success' => true,
            'message' => "Label produk {$produk->nama_barang} berhasil dicetak ($jumlah label)"
        ]);
    }
}

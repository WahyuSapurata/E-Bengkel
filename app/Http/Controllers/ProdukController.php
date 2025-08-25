<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOpnameRequest;
use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use App\Models\Kategori;
use App\Models\Opname;
use App\Models\PriceHistory;
use App\Models\Produk;
use App\Models\SubKategori;
use App\Models\Suplayer;
use App\Models\Wirehouse;
use App\Models\WirehouseStock;
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
        $kategoris = Kategori::select('uuid', 'nama_kategori')->get();
        $sub_kategoris = SubKategori::select('uuid', 'nama_sub_kategori')->get();
        $suplayers = Suplayer::select('uuid', 'nama')->get();
        return view('pages.produk.index', compact('module', 'kategoris', 'sub_kategoris', 'suplayers'));
    }

    public function price_history($params)
    {
        $produk = Produk::where('uuid', $params)->first();
        $module = 'Price History ' . $produk->nama_barang;
        return view('pages.produk.price_history', compact('module', 'produk'));
    }

    public function opname_stock($params)
    {
        $produk = Produk::where('uuid', $params)->first();

        // Hitung total pembelian
        $total_pembelian = DB::table('detail_pembelians as dp')
            ->join('pembelians as pb', 'pb.uuid', '=', 'dp.uuid_pembelian')
            ->where('dp.uuid_produk', $produk->uuid)
            ->sum('dp.qty');

        // Hitung total pengiriman
        $total_pengiriman = DB::table('detail_pengiriman_barangs as dk')
            ->join('pengiriman_barangs as pk', 'pk.uuid', '=', 'dk.uuid_pengiriman_barang')
            ->where('dk.uuid_produk', $produk->uuid)
            ->sum('dk.qty');

        // Hitung total opname
        $total_opname = DB::table('opnames')
            ->where('uuid_user', Auth::user()->uuid)
            ->where('uuid_produk', $produk->uuid)
            ->sum('stock');

        // Rumus stok akhir
        $total_stok = $total_pembelian - $total_pengiriman + $total_opname;

        $module = 'Opname Stock ' . $produk->nama_barang . ' (' . $total_stok . ')';
        return view('pages.produk.opname_stock', compact('module', 'produk'));
    }

    public function opname_stock_outlet($params)
    {
        $produk = Produk::where('uuid', $params)->first();

        // Hitung total pengiriman
        $total_pengiriman = DB::table('detail_pengiriman_barangs as dk')
            ->join('pengiriman_barangs as pk', 'pk.uuid', '=', 'dk.uuid_pengiriman_barang')
            ->where('pk.uuid_outlet', Auth::user()->uuid)
            ->where('dk.uuid_produk', $produk->uuid)
            ->sum('dk.qty');

        $total_transfer = DB::table('detail_transfer_barangs as dt')
            ->join('transfer_barangs as tb', 'tb.uuid', '=', 'dt.uuid_transfer_barangs')
            ->where('tb.uuid_outlet', Auth::user()->uuid)
            ->where('dt.uuid_produk', $produk->uuid)
            ->sum('dt.qty');

        // Hitung total opname
        $total_opname = DB::table('opnames')
            ->where('uuid_user', Auth::user()->uuid)
            ->where('uuid_produk', $produk->uuid)
            ->sum('stock');

        // Rumus stok akhir
        $total_stok = $total_pengiriman - $total_transfer + $total_opname;

        $module = 'Opname Stock ' . $produk->nama_barang . ' (' . $total_stok . ')';
        return view('outlet.produk.opname_stock', compact('module', 'produk'));
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
        $columns = [
            'produks.uuid',
            'produks.uuid_kategori',
            'produks.uuid_sub_kategori',
            'produks.uuid_suplayer',
            'produks.kode',
            'produks.nama_barang',
            'produks.merek',
            'produks.hrg_modal',
            'produks.profit',
            'produks.minstock',
            'produks.maxstock',
            'produks.satuan',
            'produks.foto',
            'kategoris.nama_kategori as kategori',
            'sub_kategoris.nama_sub_kategori as sub_kategori',
            'suplayers.nama as suplayer',
        ];

        $totalData = Produk::count();

        $query = Produk::select(array_merge($columns, [
            // total pembelian
            DB::raw("(SELECT COALESCE(SUM(dp.qty),0)
                  FROM detail_pembelians dp
                  JOIN pembelians pb ON pb.uuid = dp.uuid_pembelian
                  WHERE dp.uuid_produk = produks.uuid) as total_pembelian"),

            // total pengiriman (barang keluar pusat)
            DB::raw("(SELECT COALESCE(SUM(dk.qty),0)
                  FROM detail_pengiriman_barangs dk
                  JOIN pengiriman_barangs pk ON pk.uuid = dk.uuid_pengiriman_barang
                  WHERE dk.uuid_produk = produks.uuid) as total_pengiriman"),

            // total opname
            DB::raw("(SELECT COALESCE(SUM(o.stock),0)
                  FROM opnames o
                  WHERE o.uuid_user = '" . Auth::user()->uuid . "'
                  AND o.uuid_produk = produks.uuid) as total_opname"),

            // total stok dihitung dari 3 sumber
            DB::raw("(
                        CASE
                            WHEN EXISTS (
                                SELECT 1 FROM opnames o
                                WHERE o.uuid_user = '" . Auth::user()->uuid . "'
                                AND o.uuid_produk = produks.uuid
                            )
                            THEN (
                                -- ambil stock terakhir + transaksi setelah opname
                                (SELECT o.stock
                                FROM opnames o
                                WHERE o.uuid_user = '" . Auth::user()->uuid . "'
                                AND o.uuid_produk = produks.uuid
                                ORDER BY o.created_at DESC
                                LIMIT 1
                                )
                                +
                                (
                                    SELECT COALESCE(SUM(dp.qty),0)
                                    FROM detail_pembelians dp
                                    JOIN pembelians pb ON pb.uuid = dp.uuid_pembelian
                                    WHERE dp.uuid_produk = produks.uuid
                                    AND pb.created_at > (
                                        SELECT o2.created_at FROM opnames o2
                                        WHERE o2.uuid_user = '" . Auth::user()->uuid . "'
                                        AND o2.uuid_produk = produks.uuid
                                        ORDER BY o2.created_at DESC LIMIT 1
                                    )
                                )
                                -
                                (
                                    SELECT COALESCE(SUM(dk.qty),0)
                                    FROM detail_pengiriman_barangs dk
                                    JOIN pengiriman_barangs pk ON pk.uuid = dk.uuid_pengiriman_barang
                                    WHERE dk.uuid_produk = produks.uuid
                                    AND pk.created_at > (
                                        SELECT o2.created_at FROM opnames o2
                                        WHERE o2.uuid_user = '" . Auth::user()->uuid . "'
                                        AND o2.uuid_produk = produks.uuid
                                        ORDER BY o2.created_at DESC LIMIT 1
                                    )
                                )
                            )
                            ELSE (
                                -- kalau belum ada opname, hitung normal
                                (SELECT COALESCE(SUM(dp.qty),0)
                                FROM detail_pembelians dp
                                JOIN pembelians pb ON pb.uuid = dp.uuid_pembelian
                                WHERE dp.uuid_produk = produks.uuid)
                                -
                                (SELECT COALESCE(SUM(dk.qty),0)
                                FROM detail_pengiriman_barangs dk
                                JOIN pengiriman_barangs pk ON pk.uuid = dk.uuid_pengiriman_barang
                                WHERE dk.uuid_produk = produks.uuid)
                            )
                        END
                    ) as total_stok")
        ]))
            ->leftJoin('kategoris', 'kategoris.uuid', '=', 'produks.uuid_kategori')
            ->leftJoin('sub_kategoris', 'sub_kategoris.uuid', '=', 'produks.uuid_sub_kategori')
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
            $orderCol = explode(' as ', $columns[$request->order[0]['column']])[0];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest('produks.created_at');
        }

        // ==== pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
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

        $produk = Produk::create([
            'uuid_kategori' => $request->uuid_kategori,
            'uuid_sub_kategori' => $request->uuid_sub_kategori,
            'uuid_suplayer' => $request->uuid_suplayer,
            'kode' => $request->kode,
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

        // Selisih antara stok fisik dengan stok sistem
        $selisih = $store->stock - $stok_sistem;

        if ($selisih != 0) {
            WirehouseStock::create([
                'uuid_warehouse' => $warehouse->uuid,
                'uuid_produk'    => $produk->uuid,
                'qty'            => abs($selisih),
                'jenis'          => $selisih > 0 ? 'masuk' : 'keluar',
                'sumber'         => 'opname',
                'keterangan'     => 'Penyesuaian stok opname #' . $opname->id,
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
            $warehouseOutlet = Wirehouse::where('uuid_user', $uuid_user)
                ->where('tipe', 'gudang')
                ->first();

            if (!$warehouseOutlet) {
                return response()->json(['status' => 'error', 'message' => 'Gudang outlet tidak ditemukan'], 404);
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

            // Hitung selisih stok
            $selisih = $store->stock - $stok_sistem;

            if ($selisih != 0) {
                WirehouseStock::create([
                    'uuid_warehouse' => $warehouseOutlet->uuid,
                    'uuid_produk'    => $produk->uuid,
                    'qty'            => abs($selisih),
                    'jenis'          => $selisih > 0 ? 'masuk' : 'keluar',
                    'sumber'         => 'opname',
                    'keterangan'     => 'Penyesuaian stok opname #' . $opname->id,
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
            'uuid_sub_kategori' => $update->uuid_sub_kategori,
            'uuid_suplayer' => $update->uuid_suplayer,
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
        return view('outlet.produk.index', compact('module', 'kategoris', 'suplayers'));
    }

    public function get_outlet(Request $request)
    {
        $columns = [
            'produks.uuid',
            'produks.uuid_kategori',
            'produks.uuid_sub_kategori',
            'produks.uuid_suplayer',
            'produks.kode',
            'produks.nama_barang',
            'produks.merek',
            'produks.hrg_modal',
            'produks.profit',
            'produks.minstock',
            'produks.maxstock',
            'produks.satuan',
            'produks.foto',
            'kategoris.nama_kategori as kategori',
            'sub_kategoris.nama_sub_kategori as sub_kategori',
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
            DB::raw("(
                        CASE
                            WHEN EXISTS (
                                SELECT 1 FROM opnames o
                                WHERE o.uuid_user = '" . Auth::user()->uuid . "'
                                AND o.uuid_produk = produks.uuid
                            )
                            THEN (
                                -- ambil stock opname terakhir
                                (SELECT o.stock
                                FROM opnames o
                                WHERE o.uuid_user = '" . Auth::user()->uuid . "'
                                AND o.uuid_produk = produks.uuid
                                ORDER BY o.created_at DESC
                                LIMIT 1
                                )
                                +
                                -- tambah pengiriman setelah opname
                                (
                                    SELECT COALESCE(SUM(dk.qty),0)
                                    FROM detail_pengiriman_barangs dk
                                    JOIN pengiriman_barangs pk ON pk.uuid = dk.uuid_pengiriman_barang
                                    WHERE dk.uuid_produk = produks.uuid
                                    AND pk.uuid_outlet = '" . Auth::user()->uuid . "'
                                    AND pk.created_at > (
                                        SELECT o2.created_at FROM opnames o2
                                        WHERE o2.uuid_user = '" . Auth::user()->uuid . "'
                                        AND o2.uuid_produk = produks.uuid
                                        ORDER BY o2.created_at DESC LIMIT 1
                                    )
                                )
                                -
                                -- kurangi transfer setelah opname
                                (
                                    SELECT COALESCE(SUM(dt.qty),0)
                                    FROM detail_transfer_barangs dt
                                    JOIN transfer_barangs tb ON tb.uuid = dt.uuid_transfer_barangs
                                    WHERE dt.uuid_produk = produks.uuid
                                    AND tb.uuid_outlet = '" . Auth::user()->uuid . "'
                                    AND tb.created_at > (
                                        SELECT o2.created_at FROM opnames o2
                                        WHERE o2.uuid_user = '" . Auth::user()->uuid . "'
                                        AND o2.uuid_produk = produks.uuid
                                        ORDER BY o2.created_at DESC LIMIT 1
                                    )
                                )
                            )
                            ELSE (
                                -- kalau belum ada opname, hitung normal
                                (SELECT COALESCE(SUM(dk.qty),0)
                                FROM detail_pengiriman_barangs dk
                                JOIN pengiriman_barangs pk ON pk.uuid = dk.uuid_pengiriman_barang
                                WHERE dk.uuid_produk = produks.uuid
                                AND pk.uuid_outlet = '" . Auth::user()->uuid . "')
                                -
                                (SELECT COALESCE(SUM(dt.qty),0)
                                FROM detail_transfer_barangs dt
                                JOIN transfer_barangs tb ON tb.uuid = dt.uuid_transfer_barangs
                                WHERE dt.uuid_produk = produks.uuid
                                AND tb.uuid_outlet = '" . Auth::user()->uuid . "')
                            )
                        END
                    ) as total_stok")
        ]))
            ->leftJoin('kategoris', 'kategoris.uuid', '=', 'produks.uuid_kategori')
            ->leftJoin('sub_kategoris', 'sub_kategoris.uuid', '=', 'produks.uuid_sub_kategori')
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
            $orderCol = explode(' as ', $columns[$request->order[0]['column']])[0];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest('produks.created_at');
        }

        // ==== pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }
}

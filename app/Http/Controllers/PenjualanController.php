<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenjualanRequest;
use App\Http\Requests\UpdatePenjualanRequest;
use App\Models\Costumer;
use App\Models\DetailPenjualan;
use App\Models\KasirOutlet;
use App\Models\Outlet;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\ProdukPrice;
use App\Models\Wirehouse;
use App\Models\WirehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index()
    {
        // Kasir yang login
        $kasir_login = KasirOutlet::where('uuid_user', Auth::user()->uuid)->first();

        if (!$kasir_login) {
            abort(404, 'Kasir tidak ditemukan');
        }

        // Ambil semua kasir dalam outlet yang sama
        $semua_kasir = KasirOutlet::where('uuid_outlet', $kasir_login->uuid_outlet)
            ->orderBy('created_at', 'asc')
            ->get();

        // Tentukan nomor urut kasir yang login
        $nomor_urut = $semua_kasir->search(function ($kasir) use ($kasir_login) {
            return $kasir->id === $kasir_login->id;
        }) + 1; // index mulai 0 → +1

        // Ambil data outlet
        $data_outlet = Outlet::where('uuid_user', $kasir_login->uuid_outlet)->first();

        $module = 'Kasir ' . $data_outlet->nama_outlet;

        return view('outlet.kasir.index', compact('module', 'kasir_login', 'nomor_urut', 'data_outlet'));
    }

    public function get_produk(Request $request)
    {
        $kasir = KasirOutlet::where('uuid_user', Auth::user()->uuid)->first();
        $kode  = $request->kode; // kode/barcode hasil scan

        // Ambil produk
        $produk = Produk::select(
            'produks.uuid',
            'produks.nama_barang',
            'produks.hrg_modal',
            'produks.profit',
            'produks.kode',
            'produks.foto',
            DB::raw('COALESCE(SUM(dtb.qty),0) as stock_toko'),
            DB::raw('(produks.hrg_modal + (produks.hrg_modal * produks.profit / 100)) as harga_jual_default')
        )
            ->join('detail_transfer_barangs as dtb', 'produks.uuid', '=', 'dtb.uuid_produk')
            ->join('transfer_barangs as tb', 'tb.uuid', '=', 'dtb.uuid_transfer_barangs')
            ->where('tb.uuid_outlet', $kasir->uuid_outlet)
            ->where('produks.kode', $kode)
            ->groupBy(
                'produks.uuid',
                'produks.nama_barang',
                'produks.hrg_modal',
                'produks.profit',
                'produks.kode',
                'produks.foto'
            )
            ->havingRaw('stock_toko > 0')
            ->first();

        if (!$produk) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Produk tidak ditemukan atau stok habis'
            ], 404);
        }

        // Ambil daftar harga berdasarkan qty
        $harga_prices = ProdukPrice::where('uuid_produk', $produk->uuid)
            ->orderBy('qty', 'asc')
            ->get(['qty', 'harga_jual']);

        return response()->json([
            'status' => 'success',
            'data'   => $produk,
            'prices' => $harga_prices
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                // Validasi produk
                $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();
                if ($produk->count() !== count($request->uuid_produk)) {
                    throw new \Exception('Ada produk yang tidak ditemukan.');
                }

                // Generate nomor penjualan
                $today = now()->format('dmy');
                $prefix = "TRS-" . $today;
                $lastPenjualan = Penjualan::whereDate('created_at', now()->toDateString())
                    ->orderBy('created_at', 'desc')
                    ->first();
                $nextNumber = $lastPenjualan
                    ? intval(substr($lastPenjualan->no_bukti, strrpos($lastPenjualan->no_bukti, '-') + 1)) + 1
                    : 1;
                $no_bukti = $prefix . "-" . $nextNumber;

                // Ambil outlet dari kasir
                $kasir = KasirOutlet::where('uuid_user', Auth::user()->uuid)->firstOrFail();

                // costumer
                if (
                    $request->nama &&
                    $request->alamat &&
                    $request->nomor &&
                    $request->plat
                ) {
                    Costumer::create([
                        'nama' => $request->nama,
                        'alamat' => $request->alamat,
                        'nomor' => $request->nomor,
                        'plat' => $request->plat,
                    ]);
                }

                // Simpan header penjualan
                $penjualan = Penjualan::create([
                    'uuid_outlet'       => $kasir->uuid_outlet,
                    'no_bukti'          => $no_bukti,
                    'tanggal_transaksi' => now()->format('d-m-Y'),
                    'pembayaran'        => $request->pembayaran,
                    'created_by'        => Auth::user()->nama,
                ]);

                // Pastikan warehouse toko ada
                $warehouseToko = Wirehouse::where('uuid_user', $penjualan->uuid_outlet)
                    ->where('tipe', 'toko')
                    ->first();

                if (!$warehouseToko) {
                    $namaOutlet = Outlet::where('uuid_user', $penjualan->uuid_outlet)->value('nama_outlet');

                    $warehouseToko = Wirehouse::create([
                        'uuid_user'  => $penjualan->uuid_outlet,
                        'tipe'       => 'toko',
                        'lokasi'     => 'outlet',
                        'keterangan' => 'Toko outlet ' . $namaOutlet,
                    ]);
                }

                // Simpan detail & kurangi stok
                foreach ($request->uuid_produk as $i => $uuid_produk) {
                    $qty = $request->qty[$i];
                    $total_harga = $request->total_harga[$i];

                    DetailPenjualan::create([
                        'uuid_penjualans'  => $penjualan->uuid,
                        'uuid_produk'      => $uuid_produk,
                        'qty'              => $qty,
                        'total_harga'      => $total_harga,
                    ]);

                    // Catat keluar stok dari toko
                    WirehouseStock::create([
                        'uuid_warehouse' => $warehouseToko->uuid,
                        'uuid_produk'   => $uuid_produk,
                        'qty'           => $qty,
                        'jenis'         => 'keluar',
                        'sumber'        => 'penjualan',
                        'keterangan'    => 'Penjualan kasir',
                    ]);
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi penjualan berhasil disimpan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

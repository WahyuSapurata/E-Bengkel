<?php

namespace App\Http\Controllers;

use App\Helpers\JurnalHelper;
use App\Http\Requests\StorePenjualanRequest;
use App\Http\Requests\UpdatePenjualanRequest;
use App\Models\ClosingKasir;
use App\Models\Coa;
use App\Models\Costumer;
use App\Models\DetailPenjualan;
use App\Models\DetailPenjualanPaket;
use App\Models\HargaBackupPenjualan;
use App\Models\Jasa;
use App\Models\KasirOutlet;
use App\Models\Outlet;
use App\Models\PaketHemat;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\ProdukPrice;
use App\Models\StatusBarang;
use App\Models\Wirehouse;
use App\Models\WirehouseStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

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

        $aset = Coa::where('tipe', 'aset')
            ->whereNotIn('nama', ['Kas Outlet', 'Kas', 'Persediaan Sparepart'])
            ->select('uuid', 'nama')
            ->get();

        return view('outlet.kasir.index', compact('module', 'kasir_login', 'nomor_urut', 'data_outlet', 'aset'));
    }

    public function get_stock()
    {
        $user = Auth::user();
        $kasir_login = KasirOutlet::where('uuid_user', $user->uuid)->first();

        // Pastikan kasir punya outlet
        if (!$kasir_login) {
            return response()->json(['message' => 'Kasir belum terhubung ke outlet'], 400);
        }

        $uuid_outlet = $kasir_login->uuid_outlet;

        $query = Produk::select([
            'kode',
            'nama_barang',
            'satuan',
            DB::raw("
            (
                COALESCE((
                    SELECT SUM(ws.qty)
                    FROM wirehouse_stocks ws
                    JOIN wirehouses w ON w.uuid = ws.uuid_warehouse
                    WHERE ws.uuid_produk = produks.uuid
                    AND w.lokasi = 'outlet'
                    AND w.tipe = 'toko'
                    AND w.uuid_user = '$uuid_outlet'
                ), 0)
            ) AS total_stok
        ")
        ])->get();

        return response()->json($query);
    }

    public function get_jasa()
    {
        $jasa = Jasa::all();
        return response()->json($jasa);
    }

    public function get_produk(Request $request)
    {
        $kasir = KasirOutlet::where('uuid_user', Auth::user()->uuid)->first();
        $kode  = $request->kode; // kode/barcode hasil scan

        // Ambil produk dengan perhitungan stok yang benar
        $produk = Produk::select(
            'produks.uuid',
            'produks.nama_barang',
            'produks.hrg_modal',
            'produks.profit',
            'produks.kode',
            'produks.satuan',
            'produks.foto',
            DB::raw("
            (
                COALESCE((
                    SELECT SUM(ws.qty)
                    FROM wirehouse_stocks ws
                    JOIN wirehouses w ON w.uuid = ws.uuid_warehouse
                    WHERE ws.uuid_produk = produks.uuid
                    AND w.lokasi = 'outlet'
                    AND w.tipe = 'toko'
                    AND w.uuid_user = '$kasir->uuid_outlet'
                ), 0)
            ) AS total_stok
        "),
            DB::raw('
    ROUND(
        (
            CAST(produks.hrg_modal AS DECIMAL(15,2))
            + (CAST(produks.hrg_modal AS DECIMAL(15,2)) * CAST(produks.profit AS DECIMAL(15,2)) / 100)
        ) / 1000
    ) * 1000 as harga_jual_default
')
        )
            ->where('produks.kode', $kode)
            ->having('total_stok', '>', 0)
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
            $penjualan = null;
            $details   = [];

            DB::transaction(function () use ($request, &$penjualan, &$details) {
                // tanggal hari ini (d-m-Y)
                $today = now()->format('d-m-Y');

                // Ambil outlet dari kasir
                $kasir = KasirOutlet::where('uuid_user', Auth::user()->uuid)->first();
                $closingToday = ClosingKasir::where('uuid_kasir_outlet', $kasir->uuid_user)
                    ->where('tanggal_closing', $today)
                    ->first();

                if ($closingToday) {
                    throw new \Exception('Kasir telah closing hari ini.');
                }

                // Validasi produk
                if ($request->filled('uuid_produk') && is_array($request->uuid_produk)) {
                    $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();
                    if ($produk->count() !== count($request->uuid_produk)) {
                        throw new \Exception('Ada produk yang tidak ditemukan.');
                    }
                } else {
                    $produk = collect(); // biar tetap bisa dipakai foreach kosong
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

                // costumer (opsional)
                if ($request->nama && $request->alamat && $request->nomor && $request->plat) {
                    Costumer::create([
                        'nama'   => $request->nama,
                        'alamat' => $request->alamat,
                        'nomor'  => $request->nomor,
                        'plat'   => $request->plat,
                    ]);
                }

                // Simpan header penjualan
                $penjualan = Penjualan::create([
                    'uuid_outlet'       => $kasir->uuid_outlet,
                    'uuid_jasa'         => $request->uuid_jasa,
                    'no_bukti'          => $no_bukti,
                    'tanggal_transaksi' => now()->format('d-m-Y'),
                    'pembayaran'        => $request->pembayaran,
                    'created_by'        => Auth::user()->nama,
                ]);

                StatusBarang::create([
                    'uuid_log_barang' => $penjualan->uuid,
                    'ref' => 'Penjualan',
                    'ketarangan' => 'Penjualan oleh ' . Auth::user()->nama,
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

                $grandTotal = 0;
                $totalHpp   = 0;

                if (!empty($request->uuid_produk) && is_array($request->uuid_produk)) {
                    // Simpan detail & kurangi stok
                    foreach ($request->uuid_produk as $i => $uuid_produk) {
                        $qty = $request->qty[$i];
                        $total_harga = $request->total_harga[$i];

                        $detail = DetailPenjualan::create([
                            'uuid_penjualans'  => $penjualan->uuid,
                            'uuid_produk'      => $uuid_produk,
                            'qty'              => $qty,
                            'total_harga'      => $total_harga,
                        ]);

                        // Catat keluar stok dari toko
                        WirehouseStock::create([
                            'uuid_warehouse' => $warehouseToko->uuid,
                            'uuid_produk'    => $uuid_produk,
                            'qty'            => -$qty,
                            'jenis'          => 'keluar',
                            'sumber'         => 'penjualan',
                            'keterangan'     => 'Penjualan kasir',
                        ]);

                        // simpan detail untuk frontend
                        $produkInfo = $produk->where('uuid', $uuid_produk)->first();
                        $hargaJual = round(
                            $produkInfo->hrg_modal + ($produkInfo->hrg_modal * $produkInfo->profit / 100),
                            -3
                        );

                        HargaBackupPenjualan::create([
                            'uuid_detail_penjualan' => $detail->uuid,
                            'harga_modal' => $produkInfo->hrg_modal,
                            'harga_jual' => $hargaJual,
                        ]);

                        $details[] = [
                            'nama'     => $produkInfo->nama_barang ?? 'Produk',
                            'qty'      => $qty,
                            'harga'    => $hargaJual,
                            'subtotal' => $total_harga,
                        ];

                        $grandTotal += $total_harga;
                        $totalHpp   += $produkInfo->hrg_modal * $qty;
                    }
                }

                if ($request->uuid_paket) {
                    $qtyPaket = 1; // default 1
                    $totalHargaPaket = $request->total_harga ?? 0;

                    $paketInfo = PaketHemat::where('uuid', $request->uuid_paket)->firstOrFail();

                    // Simpan detail paket
                    $detailPaket = DetailPenjualanPaket::create([
                        'uuid_penjualans' => $penjualan->uuid,
                        'uuid_paket'      => $request->uuid_paket,
                        'qty'             => $qtyPaket,
                        'total_harga'     => $totalHargaPaket,
                    ]);

                    // Backup harga paket
                    HargaBackupPenjualan::create([
                        'uuid_detail_penjualan' => $detailPaket->uuid,
                        'harga_modal'           => $paketInfo->total_modal,
                        'harga_jual'            => $totalHargaPaket,
                    ]);

                    // Tambahkan ke summary penjualan
                    $details[] = [
                        'nama'     => $paketInfo->nama_paket ?? 'Paket Hemat',
                        'qty'      => $qtyPaket,
                        'harga'    => $totalHargaPaket,
                        'subtotal' => $totalHargaPaket,
                    ];

                    $grandTotal += $totalHargaPaket;
                    $totalHpp   += $paketInfo->total_modal * $qtyPaket;

                    // Kurangi stok produk isi paket
                    if (is_array($paketInfo->uuid_produk)) {
                        foreach ($paketInfo->uuid_produk as $uuid_produk) {
                            $qtyKeluar = (int) $qtyPaket; // semua produk qty sama dengan jumlah paket yang dibeli

                            WirehouseStock::create([
                                'uuid_warehouse' => $warehouseToko->uuid,
                                'uuid_produk'    => $uuid_produk,
                                'qty'            => -$qtyKeluar,
                                'jenis'          => 'keluar',
                                'sumber'         => 'penjualan',
                                'keterangan'     => 'Penjualan paket hemat',
                            ]);
                        }
                    }
                }

                // === Catat ke jurnal penjualan ===
                $penjualanSparepart = Coa::where('nama', 'Pendapatan Penjualan Sparepart')->firstOrFail();
                $hpp                = Coa::where('nama', 'Beban Selisih Persediaan / HPP')->firstOrFail();
                $persediaan         = Coa::where('nama', 'Persediaan Sparepart')->firstOrFail();

                $totalJasa = 0;

                if (!empty($request->uuid_jasa)) {
                    $jasaCoa = Coa::where('nama', 'Pendapatan Jasa Service')->firstOrFail();

                    // Pastikan uuid_jasa berupa array
                    $uuidJasa = is_array($request->uuid_jasa)
                        ? $request->uuid_jasa
                        : json_decode($request->uuid_jasa, true);

                    if (!empty($uuidJasa)) {
                        // Hitung frekuensi tiap UUID
                        $counts = array_count_values($uuidJasa);

                        // Ambil semua harga jasa sekaligus
                        $hargaJasa = DB::table('jasas')
                            ->whereIn('uuid', array_keys($counts))
                            ->pluck('harga', 'uuid'); // pluck(harga, uuid) supaya gampang diakses

                        // Hitung total harga jasa
                        foreach ($counts as $uuid => $qty) {
                            $totalJasa += ($hargaJasa[$uuid] ?? 0) * $qty;
                        }
                    }
                }

                // Tentukan akun debit sesuai metode pembayaran
                if ($request->pembayaran === 'Tunai') {
                    // Masuk ke Kas Outlet
                    $kasOutlet = Coa::where('nama', 'Kas Outlet')->firstOrFail();
                    $akunDebit = $kasOutlet;
                    $judulJurnal = 'Penjualan Cash';
                } else {
                    // Masuk ke Kas (pusat) sesuai bank
                    $kas = Coa::where('nama', $request->nama_bank)->firstOrFail(); // khusus setor transfer masuk ke kas
                    $akunDebit = $kas;
                    $judulJurnal = 'Penjualan Transfer';
                }

                // === Siapkan entri jurnal ===
                $entries = [];

                // Debit kas/kas outlet sebesar total grand
                $entries[] = ['uuid_coa' => $akunDebit->uuid, 'debit' => $grandTotal];

                // Kredit pendapatan jasa (kalau ada)
                if ($totalJasa > 0) {
                    $entries[] = ['uuid_coa' => $jasaCoa->uuid, 'kredit' => $totalJasa];
                }

                // Kredit pendapatan sparepart (grandTotal - jasa)
                $totalSparepart = $grandTotal - $totalJasa;
                if ($totalSparepart > 0) {
                    $entries[] = ['uuid_coa' => $penjualanSparepart->uuid, 'kredit' => $totalSparepart];
                    // Catat HPP dan persediaan hanya untuk sparepart
                    if ($totalHpp > 0) {
                        $entries[] = ['uuid_coa' => $hpp->uuid, 'debit' => $totalHpp];
                        $entries[] = ['uuid_coa' => $persediaan->uuid, 'kredit' => $totalHpp];
                    }
                }

                // === Simpan ke jurnal ===
                JurnalHelper::create(
                    now()->format('d-m-Y'),
                    $no_bukti,
                    $judulJurnal,
                    $entries,
                    $kasir->uuid_outlet,
                );
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi penjualan berhasil disimpan.',
                'data'    => [
                    'no_bukti'   => $penjualan['no_bukti'],
                    'tanggal'    => $penjualan['tanggal_transaksi'],
                    'kasir'      => $penjualan['created_by'],
                    'pembayaran' => $penjualan['pembayaran'],
                    'items'      => $details,
                    'grandTotal' => collect($details)->sum('subtotal'),
                    'totalItem'  => collect($details)->sum('qty'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel_penjualan(Request $request)
    {
        $uuid_produk  = $request->input('uuid_produk', []);
        $qty          = $request->input('qty', []);
        $total_harga  = $request->input('total_harga', []);

        $logs = [];

        foreach ($uuid_produk as $i => $uuid) {
            $produk = Produk::where('uuid', $uuid)->first();

            if ($produk) {
                $logs[] = [
                    'uuid' => Uuid::uuid4()->toString(),
                    'uuid_log_barang' => $uuid,
                    'ref'             => 'Cancel Barang',
                    'ketarangan'      => 'Produk tercancel oleh '
                        . (Auth::user()->nama ?? 'Unknown')
                        . ' | ' . $produk->nama_barang
                        . ' | Qty: ' . ($qty[$i] ?? 0)
                        . ' | Total: ' . ($total_harga[$i] ?? 0),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }

        if (!empty($logs)) {
            StatusBarang::insert($logs);
        }

        return response()->json([
            'success' => true,
            'message' => 'Penjualan dibatalkan dan log cancel tersimpan.',
        ]);
    }

    public function get_penjualan()
    {
        $kasir = KasirOutlet::where('uuid_user', Auth::user()->uuid)->first();

        // tanggal hari ini (d-m-Y)
        $today = now()->format('d-m-Y');

        // cek apakah sudah closing untuk tanggal hari ini
        $closing = ClosingKasir::where('uuid_kasir_outlet', $kasir->uuid_user)
            ->where('tanggal_closing', $today)
            ->first();

        if ($closing) {
            return response()->json([
                'status' => false,
                'message' => 'Data penjualan sudah ditutup (closing)',
                'penjualans' => []
            ]);
        }

        $closingDates = ClosingKasir::where('uuid_kasir_outlet', $kasir->uuid_user)
            ->pluck('tanggal_closing')
            ->toArray();

        // ambil penjualan untuk tanggal hari ini
        $penjualans = Penjualan::where('uuid_outlet', $kasir->uuid_outlet)
            ->where('created_by', Auth::user()->nama)
            ->where('tanggal_transaksi', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'penjualans' => $penjualans
        ]);
    }

    public function get_detail_penjualan($uuid)
    {
        // Ambil data penjualan utama
        $penjualan = Penjualan::where('uuid', $uuid)->firstOrFail();

        // === Detail Produk Biasa ===
        $detailsProduk = DB::table('detail_penjualans')
            ->leftJoin('produks', 'detail_penjualans.uuid_produk', '=', 'produks.uuid')
            ->where('detail_penjualans.uuid_penjualans', $penjualan->uuid)
            ->select(
                'detail_penjualans.qty',
                'detail_penjualans.total_harga',
                'produks.nama_barang as nama',
            )
            ->get()
            ->map(function ($d) {
                return [
                    'nama'     => $d->nama ?? '-',
                    'qty'      => $d->qty,
                    'harga'    => $d->total_harga / max(1, $d->qty),
                    'subtotal' => $d->total_harga,
                    'tipe'     => 'produk',
                ];
            });

        // === Detail Paket Hemat ===
        $detailsPaket = DB::table('detail_penjualan_pakets')
            ->leftJoin('paket_hemats', 'detail_penjualan_pakets.uuid_paket', '=', 'paket_hemats.uuid')
            ->where('detail_penjualan_pakets.uuid_penjualans', $penjualan->uuid)
            ->select(
                'detail_penjualan_pakets.qty',
                'detail_penjualan_pakets.total_harga',
                'paket_hemats.nama_paket as nama'
            )
            ->get()
            ->map(function ($d) {
                return [
                    'nama'     => $d->nama ?? '-',
                    'qty'      => $d->qty,
                    'harga'    => $d->total_harga / max(1, $d->qty),
                    'subtotal' => $d->total_harga,
                    'tipe'     => 'paket',
                ];
            });

        // Gabungkan keduanya
        $allDetails = $detailsProduk->merge($detailsPaket);

        // Hitung total
        $totalItem  = $allDetails->sum('qty');
        $grandTotal = $allDetails->sum('subtotal');

        // Ambil jasa (kalau ada)
        $jasa = 0;
        if ($penjualan->uuid_jasa) {
            $uuidJasa = is_array($penjualan->uuid_jasa) ? $penjualan->uuid_jasa : json_decode($penjualan->uuid_jasa, true);

            // Hitung frekuensi tiap UUID
            $counts = array_count_values($uuidJasa);

            // Ambil semua harga jasa
            $hargaJasa = DB::table('jasas')
                ->whereIn('uuid', array_keys($counts))
                ->pluck('harga', 'uuid');

            foreach ($counts as $uuid => $qty) {
                $jasa += ($hargaJasa[$uuid] ?? 0) * $qty;
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'no_bukti'   => $penjualan->no_bukti,
                'tanggal'    => $penjualan->tanggal_transaksi,
                'kasir'      => $penjualan->created_by,
                'pembayaran' => $penjualan->pembayaran,
                'items'      => $allDetails,
                'grandTotal' => $grandTotal + ($jasa ? $jasa : 0),
                'totalItem'  => $totalItem,
                'totalJasa'  => $jasa ? $jasa : 0,
            ]
        ]);
    }

    // public function cetakStrukThermal(Request $request)
    // {
    //     $data = $request->all(); // ambil semua data dari frontend

    //     // panggil fungsi yang sudah kita buat tadi
    //     $this->printStruk($data);

    //     return response()->json([
    //         'data' => $data,
    //         'status' => 'success',
    //         'message' => 'Struk berhasil dicetak'
    //     ]);
    // }

    // function printStruk($data)
    // {
    //     $width = 48; // lebar karakter untuk kertas 80mm
    //     $struk = "";

    //     // ===============================
    //     // HEADER
    //     // ===============================
    //     $struk .= $this->centerText($data['outlet_nama'], $width) . "\n";
    //     $struk .= $this->centerText($data['outlet_alamat'], $width) . "\n";
    //     $struk .= $this->centerText("Telp: " . $data['outlet_telp'], $width) . "\n";
    //     $struk .= str_repeat("=", $width) . "\n";

    //     // ===============================
    //     // INFO TRANSAKSI
    //     // ===============================
    //     $struk .= "No       : " . $data['no_bukti'] . "\n";
    //     $struk .= "Tanggal  : " . $data['tanggal'] . "\n";
    //     $struk .= "Kasir    : " . $data['kasir'] . "\n";
    //     $struk .= "Bayar    : " . $data['pembayaran'] . "\n";
    //     $struk .= str_repeat("-", $width) . "\n";

    //     // ===============================
    //     // ITEMS (lebar total = 48)
    //     // Nama: 20 | Qty: 5 | Harga: 10 | Sub: 13
    //     // ===============================
    //     $struk .= str_pad("Barang", 20);
    //     $struk .= str_pad("Qty", 5, " ", STR_PAD_LEFT);
    //     $struk .= str_pad("Harga", 10, " ", STR_PAD_LEFT);
    //     $struk .= str_pad("Sub", 13, " ", STR_PAD_LEFT) . "\n";
    //     $struk .= str_repeat("-", $width) . "\n";

    //     foreach ($data['items'] as $item) {
    //         $nama = $item['nama'];
    //         $qty = $item['qty'];
    //         $harga = number_format($item['harga'], 0, ',', '.');
    //         $subtotal = number_format($item['subtotal'], 0, ',', '.');

    //         // Cetak nama produk (maks 20 char di baris utama)
    //         $struk .= str_pad(substr($nama, 0, 20), 20);
    //         $struk .= str_pad($qty, 5, " ", STR_PAD_LEFT);
    //         $struk .= str_pad($harga, 10, " ", STR_PAD_LEFT);
    //         $struk .= str_pad($subtotal, 13, " ", STR_PAD_LEFT) . "\n";

    //         // Kalau nama produk panjang, lanjutkan di baris bawah
    //         if (strlen($nama) > 20) {
    //             $sisa = wordwrap(substr($nama, 20), $width - 1, "\n", true);
    //             $lines = explode("\n", $sisa);
    //             foreach ($lines as $line) {
    //                 $struk .= " " . $line . "\n";
    //             }
    //         }
    //     }

    //     // ===============================
    //     // TOTAL JASA (jika ada)
    //     // ===============================
    //     if (!empty($data['totalJasa']) && $data['totalJasa'] > 0) {
    //         $struk .= str_pad("Jasa", 20);
    //         $struk .= str_pad("1", 5, " ", STR_PAD_LEFT);
    //         $struk .= str_pad(number_format($data['totalJasa'], 0, ',', '.'), 10, " ", STR_PAD_LEFT);
    //         $struk .= str_pad(number_format($data['totalJasa'], 0, ',', '.'), 13, " ", STR_PAD_LEFT) . "\n";
    //     }

    //     // ===============================
    //     // TOTAL
    //     // ===============================
    //     $struk .= str_repeat("-", $width) . "\n";

    //     if (!empty($data['totalItem'])) {
    //         $struk .= str_pad("Total Item", $width - 15, " ", STR_PAD_LEFT);
    //         $struk .= str_pad(number_format($data['totalItem'], 0, ',', '.'), 15, " ", STR_PAD_LEFT) . "\n";
    //     }

    //     $struk .= str_repeat("-", $width) . "\n";
    //     $struk .= str_pad("Grand Total", $width - 15, " ", STR_PAD_LEFT);
    //     $struk .= str_pad(number_format($data['grandTotal'], 0, ',', '.'), 15, " ", STR_PAD_LEFT) . "\n";
    //     $struk .= str_repeat("=", $width) . "\n";

    //     // ===============================
    //     // FOOTER
    //     // ===============================
    //     $struk .= $this->centerText("*** Terima Kasih ***", $width) . "\n";
    //     $struk .= $this->centerText("Barang yang sudah dibeli", $width) . "\n";
    //     $struk .= $this->centerText("tidak dapat ditukar/dikembalikan", $width) . "\n";

    //     // Feed kosong (biar struk tidak kepotong)
    //     $struk .= "\n\n";

    //     // CUT PAPER (GS V A 0 = full cut)
    //     $struk .= chr(29) . chr(86) . chr(65) . chr(0);

    //     // SIMPAN & PRINT (raw mode)
    //     $tmpFile = '/tmp/struk.txt';
    //     file_put_contents($tmpFile, $struk);
    //     shell_exec("lp -d Codeshop -o raw " . escapeshellarg($tmpFile));
    // }

    // // ===============================
    // // Helper: Center Text
    // // ===============================
    function centerText($text, $width = 48)
    {
        $len = strlen($text);
        if ($len >= $width) return $text;
        $left = floor(($width - $len) / 2);
        $right = $width - $len - $left;
        return str_repeat(" ", $left) . $text . str_repeat(" ", $right);
    }

    public function cetakStrukThermal(Request $request)
    {
        $data = $request->all();
        $struk = $this->printStruk($data);

        return response()->json([
            'raw' => base64_encode($struk), // kirim dalam base64 biar aman di JSON
            'status' => 'success',
            'message' => 'Struk siap dicetak di client'
        ]);
    }

    function printStruk($data)
    {
        $width = 48;
        $ESC = "\x1B";
        $GS  = "\x1D";
        $struk = "";

        // Reset printer
        $struk .= $ESC . "@";
        $struk .= $ESC . "a" . "\x01"; // Center
        $struk .= strtoupper($data['outlet_nama']) . "\n";
        $struk .= $data['outlet_alamat'] . "\n";
        $struk .= "Telp: " . $data['outlet_telp'] . "\n";
        $struk .= str_repeat("=", $width) . "\n";

        // Info transaksi
        $struk .= $ESC . "a" . "\x00";
        $struk .= "No      : {$data['no_bukti']}\n";
        $struk .= "Tanggal : {$data['tanggal']}\n";
        $struk .= "Kasir   : {$data['kasir']}\n";
        $struk .= "Bayar   : {$data['pembayaran']}\n";
        $struk .= str_repeat("-", $width) . "\n";

        // Header tabel
        $struk .= sprintf("%-30s %5s %12s\n", "Barang", "Qty", "Harga");
        $struk .= str_repeat("-", $width) . "\n";

        // Isi barang
        foreach ($data['items'] as $item) {
            $nama = trim($item['nama']);
            $qty = $item['qty'];
            $harga = number_format($item['harga'], 0, ',', '.');
            $subtotal = number_format($item['subtotal'], 0, ',', '.');

            // Bungkus nama barang biar tidak nabrak
            $wrapped = wordwrap($nama, 30, "\n", true);
            $lines = explode("\n", $wrapped);

            // Cetak baris pertama dengan qty dan harga
            $struk .= sprintf("%-30s %5s %12s\n", $lines[0], $qty, $subtotal);

            // Kalau nama barang lebih dari 1 baris, cetak baris lanjutannya
            for ($i = 1; $i < count($lines); $i++) {
                $struk .= sprintf("%-30s\n", $lines[$i]);
            }
        }

        // Total jasa
        if (!empty($data['totalJasa']) && $data['totalJasa'] > 0) {
            $totalJasa = number_format($data['totalJasa'], 0, ',', '.');
            $struk .= str_repeat("-", $width) . "\n";
            $struk .= sprintf("%-30s %5s %12s\n", "Jasa", 1, $totalJasa);
        }

        // Total & Grand total
        $struk .= str_repeat("-", $width) . "\n";
        $struk .= sprintf("%-30s %5s %12s\n", "Total Item", $data['totalItem'], "");
        $struk .= sprintf("%-30s %5s %12s\n", "Grand Total", "", number_format($data['grandTotal'], 0, ',', '.'));
        $struk .= str_repeat("=", $width) . "\n";

        // Footer
        $struk .= $ESC . "a" . "\x01"; // Center
        $struk .= "*** Terima Kasih ***\n";
        $struk .= "Barang yang sudah dibeli\n";
        $struk .= "tidak dapat ditukar/dikembalikan\n";
        $struk .= $ESC . "d" . "\x03"; // Feed
        $struk .= $GS . "V" . "\x42" . "\x00"; // Cut

        return $struk;
    }



    public function getPaket()
    {
        $paket = PaketHemat::all();
        return response()->json([
            'status' => true,
            'paket' => $paket
        ]);
    }

    public function detailPaket($params)
    {
        $paket = PaketHemat::where('uuid', $params)
            ->select(
                'uuid',
                'uuid_produk',
                'nama_paket',
                DB::raw('
                ROUND(
                    (
                        CAST(total_modal AS DECIMAL(15,2))
                        + (CAST(total_modal AS DECIMAL(15,2)) * CAST(profit AS DECIMAL(15,2)) / 100)
                    ) / 1000
                ) * 1000 as harga_jual
            ')
            )
            ->first();

        // Jika paket tidak ditemukan
        if (!$paket) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Paket tidak ditemukan'
            ], 404);
        }

        // Ambil produk berdasarkan array uuid_produk
        $produk = Produk::whereIn('uuid', $paket->uuid_produk ?? [])->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'nama_paket' => $paket->nama_paket,
                'items'      => $produk->map(function ($detail) {
                    return [
                        'nama' => $detail->nama_barang ?? '-',
                    ];
                }),
                'grandTotal' => $paket->harga_jual,
            ]
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\JurnalHelper;
use App\Http\Requests\StorePenjualanRequest;
use App\Http\Requests\UpdatePenjualanRequest;
use App\Models\ClosingKasir;
use App\Models\Coa;
use App\Models\Costumer;
use App\Models\DetailPenjualan;
use App\Models\Jasa;
use App\Models\KasirOutlet;
use App\Models\Outlet;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\ProdukPrice;
use App\Models\Wirehouse;
use App\Models\WirehouseStock;
use Carbon\Carbon;
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

        $aset = Coa::where('tipe', 'aset')
            ->whereNotIn('nama', ['Kas Outlet', 'Kas', 'Persediaan Sparepart'])
            ->select('uuid', 'nama')
            ->get();

        return view('outlet.kasir.index', compact('module', 'kasir_login', 'nomor_urut', 'data_outlet', 'aset'));
    }

    public function get_stock()
    {
        $kasir_login = KasirOutlet::where('uuid_user', Auth::user()->uuid)->first();
        $query = Produk::select(array_merge(['kode', 'nama_barang', 'satuan'], [
            DB::raw("(SELECT COALESCE(SUM(dt.qty),0)
                  FROM detail_transfer_barangs dt
                  JOIN transfer_barangs tb ON tb.uuid = dt.uuid_transfer_barangs
                  WHERE dt.uuid_produk = produks.uuid) as total_transfer"),

            DB::raw("(SELECT COALESCE(SUM(dp.qty),0)
                  FROM detail_penjualans dp
                  JOIN penjualans pj ON pj.uuid = dp.uuid_penjualans
                  WHERE dp.uuid_produk = produks.uuid) as total_pejualan"),

            // total stok dihitung dari 3 sumber
            DB::raw("(
            (SELECT COALESCE(SUM(dt.qty),0)
             FROM detail_transfer_barangs dt
             JOIN transfer_barangs tb ON tb.uuid = dt.uuid_transfer_barangs
             WHERE tb.uuid_outlet = '" . $kasir_login->uuid_outlet . "' AND dt.uuid_produk = produks.uuid)
            -
            (SELECT COALESCE(SUM(dp.qty),0)
             FROM detail_penjualans dp
             JOIN penjualans pj ON pj.uuid = dp.uuid_penjualans
             WHERE pj.uuid_outlet = '" . $kasir_login->uuid_outlet . "' AND dp.uuid_produk = produks.uuid)
        ) as total_stok")
        ]))->get();

        return response()->json($query);
    }

    public function get_jasa()
    {
        $jamLalu = Carbon::now()->subHour();

        $jasa = Jasa::whereNotIn('uuid', function ($q) use ($jamLalu) {
            $q->select('uuid_jasa')
                ->from('penjualans')
                ->whereNotNull('uuid_jasa')
                ->where('created_at', '>=', $jamLalu);
        })
            ->get();
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
            DB::raw("(
            COALESCE((
                SELECT SUM(dtb.qty)
                FROM detail_transfer_barangs dtb
                JOIN transfer_barangs tb ON tb.uuid = dtb.uuid_transfer_barangs
                WHERE tb.uuid_outlet = '{$kasir->uuid_outlet}'
                  AND dtb.uuid_produk = produks.uuid
            ),0)
            -
            COALESCE((
                SELECT SUM(dp.qty)
                FROM detail_penjualans dp
                JOIN penjualans pj ON pj.uuid = dp.uuid_penjualans
                WHERE pj.uuid_outlet = '{$kasir->uuid_outlet}'
                  AND dp.uuid_produk = produks.uuid
            ),0)
        ) as stock_toko"),
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
            $penjualan = null;
            $details   = [];

            DB::transaction(function () use ($request, &$penjualan, &$details) {
                // Ambil outlet dari kasir
                $kasir = KasirOutlet::where('uuid_user', Auth::user()->uuid)->firstOrFail();
                $closingToday = ClosingKasir::where('uuid_kasir_outlet', $kasir->uuid_outlet)
                    ->whereDate('tanggal_closing', now()->format('d-m-Y'))
                    ->first();

                if ($closingToday) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Kasir sudah closing hari ini, tidak bisa transaksi lagi.'
                    ], 403);
                }

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
                        'qty'            => $qty,
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

                    $details[] = [
                        'nama'     => $produkInfo->nama_barang ?? 'Produk',
                        'qty'      => $qty,
                        'harga'    => $hargaJual,
                        'subtotal' => $total_harga,
                    ];

                    $grandTotal += $total_harga;
                    $totalHpp   += $produkInfo->hrg_modal * $qty;
                }

                // === Catat ke jurnal penjualan ===
                $penjualanSparepart = Coa::where('nama', 'Pendapatan Penjualan Sparepart')->firstOrFail();
                $hpp                = Coa::where('nama', 'Beban Selisih Persediaan / HPP')->firstOrFail();
                $persediaan         = Coa::where('nama', 'Persediaan Sparepart')->firstOrFail();

                $totalJasa = 0;
                if ($request->uuid_jasa) {
                    $jasaCoa = Coa::where('nama', 'Pendapatan Jasa Service')->firstOrFail();
                    $totalJasa = Jasa::where('uuid', $request->uuid_jasa)->firstOrFail()->harga;
                }

                // Tentukan akun debit sesuai metode pembayaran
                if ($request->pembayaran === 'Tunai') {
                    // Masuk ke Kas Outlet
                    $kasOutlet = Coa::where('nama', 'Kas Outlet')->firstOrFail();
                    $akunDebit = $kasOutlet;
                    $judulJurnal = 'Penjualan Cash';
                } else {
                    // Masuk ke Kas (pusat) sesuai bank
                    $kas = Coa::where('nama', 'Kas')->firstOrFail(); // khusus setor transfer masuk ke kas
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

    public function get_penjualan()
    {
        $kasir = KasirOutlet::where('uuid_user', Auth::user()->uuid)->first();

        // Ambil semua penjualan hari ini
        $penjualans = Penjualan::where('uuid_outlet', $kasir->uuid_outlet)
            // ->where('tanggal_transaksi', now()->format('d-m-Y'))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($penjualans);
    }

    public function get_detail_penjualan($uuid)
    {
        // Ambil data penjualan utama
        $penjualan = Penjualan::where('uuid', $uuid)->firstOrFail();

        // Ambil detail penjualan + produk (JOIN manual)
        $details = DB::table('detail_penjualans')
            ->leftJoin('produks', 'detail_penjualans.uuid_produk', '=', 'produks.uuid')
            ->where('detail_penjualans.uuid_penjualans', $penjualan->uuid)
            ->select(
                'detail_penjualans.qty',
                'detail_penjualans.total_harga',
                'produks.nama_barang',
                'produks.hrg_modal',
                'produks.profit'
            )
            ->get();

        // Hitung total
        $totalItem  = $details->sum('qty');
        $grandTotal = $details->sum('total_harga');

        // Ambil jasa (kalau ada)
        $jasa = null;
        if ($penjualan->uuid_jasa) {
            $jasa = DB::table('jasas')->where('uuid', $penjualan->uuid_jasa)->first();
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'no_bukti'   => $penjualan->no_bukti,
                'tanggal'    => $penjualan->tanggal_transaksi,
                'kasir'      => $penjualan->created_by,
                'pembayaran' => $penjualan->pembayaran,
                'items'      => $details->map(function ($detail) {
                    return [
                        'nama'     => $detail->nama_barang ?? '-',
                        'qty'      => $detail->qty,
                        'harga'    => $detail->total_harga / $detail->qty,
                        'subtotal' => $detail->total_harga,
                    ];
                }),
                'grandTotal' => $grandTotal,
                'totalItem'  => $totalItem,
                'totalJasa'  => $jasa ? $jasa->harga : 0,
            ]
        ]);
    }


    public function cetakStrukThermal(Request $request)
    {
        $data = $request->all(); // ambil semua data dari frontend

        // panggil fungsi yang sudah kita buat tadi
        $this->printStruk($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Struk berhasil dicetak'
        ]);
    }

    function printStruk($data)
    {
        $width = 48; // lebar karakter untuk kertas 80mm
        $struk = "";

        // ===============================
        // HEADER
        // ===============================
        $struk .= $this->centerText($data['outlet_nama'], $width) . "\n";
        $struk .= $this->centerText($data['outlet_alamat'], $width) . "\n";
        $struk .= $this->centerText("Telp: " . $data['outlet_telp'], $width) . "\n";
        $struk .= str_repeat("=", $width) . "\n";

        // ===============================
        // INFO TRANSAKSI
        // ===============================
        $struk .= "No       : " . $data['no_bukti'] . "\n";
        $struk .= "Tanggal  : " . $data['tanggal'] . "\n";
        $struk .= "Kasir    : " . $data['kasir'] . "\n";
        $struk .= "Bayar    : " . $data['pembayaran'] . "\n";
        $struk .= str_repeat("-", $width) . "\n";

        // ===============================
        // ITEMS
        // ===============================
        $struk .= str_pad("Barang", 20);
        $struk .= str_pad("Qty", 5, " ", STR_PAD_LEFT);
        $struk .= str_pad("Harga", 10, " ", STR_PAD_LEFT);
        $struk .= str_pad("Sub", 13, " ", STR_PAD_LEFT) . "\n";
        $struk .= str_repeat("-", $width) . "\n";

        foreach ($data['items'] as $item) {
            $nama = substr($item['nama'], 0, 20);
            $qty = $item['qty'];
            $harga = number_format($item['harga'], 0, ',', '.');
            $subtotal = number_format($item['subtotal'], 0, ',', '.');

            $struk .= str_pad($nama, 20);
            $struk .= str_pad($qty, 5, " ", STR_PAD_LEFT);
            $struk .= str_pad($harga, 10, " ", STR_PAD_LEFT);
            $struk .= str_pad($subtotal, 13, " ", STR_PAD_LEFT) . "\n";

            // Kalau nama produk panjang > 20, cetak di baris kedua
            if (strlen($item['nama']) > 20) {
                $struk .= " " . substr($item['nama'], 20, $width) . "\n";
            }
        }

        if (!empty($data['totalJasa']) && $data['totalJasa'] > 0) {
            $struk .= str_pad("Total Jasa", 20, " ", STR_PAD_LEFT);
            $struk .= str_pad("1", 5, " ", STR_PAD_LEFT);
            $struk .= str_pad($harga, 10, " ", STR_PAD_LEFT);
            $struk .= str_pad(number_format($data['totalJasa'], 0, ',', '.'), 15, " ", STR_PAD_LEFT) . "\n";
        }

        // ===============================
        // TOTAL
        // ===============================
        $struk .= str_repeat("-", $width) . "\n";

        if (!empty($data['totalItem'])) {
            $struk .= str_pad("Total Item", $width - 15, " ", STR_PAD_LEFT);
            $struk .= str_pad(number_format($data['totalItem'], 0, ',', '.'), 15, " ", STR_PAD_LEFT) . "\n";
        }
        $struk .= str_repeat("-", $width) . "\n";
        $struk .= str_pad("Grand Total", $width - 15, " ", STR_PAD_LEFT);
        $struk .= str_pad(number_format($data['grandTotal'], 0, ',', '.'), 15, " ", STR_PAD_LEFT) . "\n";
        $struk .= str_repeat("=", $width) . "\n";

        // ===============================
        // FOOTER
        // ===============================
        $struk .= $this->centerText("*** Terima Kasih ***", $width) . "\n";
        $struk .= $this->centerText("Barang yang sudah dibeli", $width) . "\n";
        $struk .= $this->centerText("tidak dapat ditukar/dikembalikan", $width) . "\n";

        // Feed kosong (biar struk tidak kepotong)
        $struk .= "\n\n";

        // CUT PAPER (GS V A 0 = full cut)
        $struk .= chr(29) . chr(86) . chr(65) . chr(0);

        // SIMPAN & PRINT (raw mode)
        $tmpFile = '/tmp/struk.txt';
        file_put_contents($tmpFile, $struk);
        shell_exec("lp -o raw $tmpFile");
    }

    // ===============================
    // Helper: Center Text
    // ===============================
    function centerText($text, $width = 48)
    {
        $len = strlen($text);
        if ($len >= $width) return $text;
        $left = floor(($width - $len) / 2);
        $right = $width - $len - $left;
        return str_repeat(" ", $left) . $text . str_repeat(" ", $right);
    }
}

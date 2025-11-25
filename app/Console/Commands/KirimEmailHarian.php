<?php

namespace App\Console\Commands;

use App\Mail\KirimLaporanHarianMail;
use App\Models\Outlet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class KirimEmailHarian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Jalankan pakai: php artisan app:kirim-email-harian
     */
    protected $signature = 'app:kirim-email-harian';

    /**
     * The console command description.
     */
    protected $description = 'Mengirim email laporan harian otomatis setiap malam jam 11.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tanggalHariIni = Carbon::now()->format('d-m-Y');
        $tanggalHariIni = '24-11-2025';

        // Ambil semua outlet
        $outlets = Outlet::all();

        // Email tujuan per outlet
        $emailTujuanSamata = 'adsmotorsamata@gmail.com';
        $emailTujuanPallangga = 'cv.adsmotorindonesia@gmail.com';

        foreach ($outlets as $outlet) {

            // Tentukan email tujuan berdasarkan nama outlet
            $emailTujuan = null;
            if (stripos($outlet->nama_outlet, 'samata') !== false) {
                $emailTujuan = $emailTujuanSamata;
            } elseif (stripos($outlet->nama_outlet, 'pallangga') !== false) {
                $emailTujuan = $emailTujuanPallangga;
            } else {
                $this->warn("⚠️ Tidak ada email yang cocok untuk outlet {$outlet->nama_outlet}");
                continue;
            }

            $transaksis = DB::table('penjualans')
                ->join('users', 'penjualans.created_by', '=', 'users.nama')
                ->where('penjualans.uuid_outlet', $outlet->uuid_user)
                ->where('penjualans.tanggal_transaksi', $tanggalHariIni)
                ->select(
                    'penjualans.uuid',
                    'users.nama as kasir',
                    'penjualans.pembayaran',
                    'penjualans.uuid_jasa'
                )
                ->get();

            if ($transaksis->isEmpty()) {
                $this->info("❌ Tidak ada transaksi di outlet {$outlet->nama_outlet}");
                continue;
            }

            $laporan = [];

            foreach ($transaksis as $trx) {
                // === Produk
                $produkTotals = DB::table('detail_penjualans')
                    ->join('harga_backup_penjualans', 'detail_penjualans.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
                    ->where('uuid_penjualans', $trx->uuid)
                    ->selectRaw('
                    SUM(detail_penjualans.total_harga) as total_penjualan,
                    SUM(detail_penjualans.total_harga) - SUM(harga_backup_penjualans.harga_modal * detail_penjualans.qty) as total_profit
                ')
                    ->first();

                // === Paket
                $paketTotals = DB::table('detail_penjualan_pakets')
                    ->join('harga_backup_penjualans', 'detail_penjualan_pakets.uuid', '=', 'harga_backup_penjualans.uuid_detail_penjualan')
                    ->where('uuid_penjualans', $trx->uuid)
                    ->selectRaw('
                    SUM(detail_penjualan_pakets.total_harga) as total_penjualan,
                    SUM(detail_penjualan_pakets.total_harga - harga_backup_penjualans.harga_modal * detail_penjualan_pakets.qty) as total_profit
                ')
                    ->first();

                // === Jasa
                $totalJasa = 0;
                if (!empty($trx->uuid_jasa)) {
                    $uuidJasa = is_array($trx->uuid_jasa)
                        ? $trx->uuid_jasa
                        : json_decode($trx->uuid_jasa, true);

                    if (!empty($uuidJasa)) {
                        $counts = array_count_values($uuidJasa);

                        $hargaJasa = DB::table('jasas')
                            ->whereIn('uuid', array_keys($counts))
                            ->pluck('harga', 'uuid');

                        foreach ($counts as $uuid => $qty) {
                            $totalJasa += ($hargaJasa[$uuid] ?? 0) * $qty;
                        }
                    }
                }

                $totalPenjualan = ($produkTotals->total_penjualan ?? 0)
                    + ($paketTotals->total_penjualan ?? 0)
                    + $totalJasa;

                $totalProfit    = ($produkTotals->total_profit ?? 0)
                    + ($paketTotals->total_profit ?? 0)
                    + $totalJasa;

                // === Kelompokkan per kasir ===
                if (!isset($laporan[$trx->kasir])) {
                    $laporan[$trx->kasir] = [
                        'kasir' => $trx->kasir,
                        'tunai' => 0,
                        'non_tunai' => 0,
                        'profit' => 0,
                    ];
                }

                if (strtolower($trx->pembayaran) === 'tunai') {
                    $laporan[$trx->kasir]['tunai'] += $totalPenjualan;
                } else {
                    $laporan[$trx->kasir]['non_tunai'] += $totalPenjualan;
                }

                $laporan[$trx->kasir]['profit'] += $totalProfit;
            }

            // Hitung total akhir per kasir
            $hasil = collect($laporan)->map(function ($item) {
                $item['total'] = $item['tunai'] + $item['non_tunai'];
                $target = 1500000;
                $item['persentase'] = $target > 0
                    ? round(($item['profit'] / $target) * 100, 2)
                    : 0;
                return $item;
            })->values()->toArray();

            // === Kirim Email ke Owner yang sesuai outlet ===
            Mail::to($emailTujuan)->send(
                new KirimLaporanHarianMail($hasil, $tanggalHariIni, $outlet)
            );

            $this->info("✅ Laporan outlet {$outlet->nama_outlet} terkirim ke {$emailTujuan}");
        }

        $this->info('🎉 Semua laporan outlet sudah dikirim ke owner.');
    }
}

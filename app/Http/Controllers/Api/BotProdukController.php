<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BotProdukController extends Controller
{
    public function get_outlet()
    {
        // Ambil semua outlet, hanya field yang diperlukan
        $outlets = Outlet::select('uuid', 'uuid_user', 'nama_outlet', 'alamat', 'telepon')->get();

        return response()->json($outlets);
    }

    public function search(Request $request)
    {
        $search = $request->query('q');
        $uuidUser = $request->query('uuid_user'); // ambil dari query string

        if (!$search) {
            return response()->json(['error' => 'Parameter q diperlukan'], 400);
        }

        if (!$uuidUser) {
            return response()->json(['error' => 'Parameter uuid_user diperlukan'], 400);
        }

        $produks = Produk::select(
            'produks.uuid',
            'produks.nama_barang',
            'produks.merek',
            'produks.hrg_modal',
            'produks.profit',
            DB::raw("(
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM opnames o
                        WHERE o.uuid_user = '{$uuidUser}'
                        AND o.uuid_produk = produks.uuid
                    )
                    THEN (
                        (SELECT o.stock
                        FROM opnames o
                        WHERE o.uuid_user = '{$uuidUser}'
                        AND o.uuid_produk = produks.uuid
                        ORDER BY o.created_at DESC
                        LIMIT 1
                        )
                        +
                        (SELECT COALESCE(SUM(dk.qty),0)
                        FROM detail_pengiriman_barangs dk
                        JOIN pengiriman_barangs pk ON pk.uuid = dk.uuid_pengiriman_barang
                        WHERE dk.uuid_produk = produks.uuid
                        AND pk.uuid_outlet = '{$uuidUser}'
                        AND pk.created_at > (
                            SELECT o2.created_at FROM opnames o2
                            WHERE o2.uuid_user = '{$uuidUser}'
                            AND o2.uuid_produk = produks.uuid
                            ORDER BY o2.created_at DESC LIMIT 1
                        )
                        )
                        -
                        (SELECT COALESCE(SUM(dt.qty),0)
                        FROM detail_transfer_barangs dt
                        JOIN transfer_barangs tb ON tb.uuid = dt.uuid_transfer_barangs
                        WHERE dt.uuid_produk = produks.uuid
                        AND tb.uuid_outlet = '{$uuidUser}'
                        AND tb.created_at > (
                            SELECT o2.created_at FROM opnames o2
                            WHERE o2.uuid_user = '{$uuidUser}'
                            AND o2.uuid_produk = produks.uuid
                            ORDER BY o2.created_at DESC LIMIT 1
                        )
                        )
                    )
                    ELSE (
                        (SELECT COALESCE(SUM(dk.qty),0)
                        FROM detail_pengiriman_barangs dk
                        JOIN pengiriman_barangs pk ON pk.uuid = dk.uuid_pengiriman_barang
                        WHERE dk.uuid_produk = produks.uuid
                        AND pk.uuid_outlet = '{$uuidUser}')
                        -
                        (SELECT COALESCE(SUM(dt.qty),0)
                        FROM detail_transfer_barangs dt
                        JOIN transfer_barangs tb ON tb.uuid = dt.uuid_transfer_barangs
                        WHERE dt.uuid_produk = produks.uuid
                        AND tb.uuid_outlet = '{$uuidUser}')
                    )
                END
            ) as total_stok")
        )
            ->where('produks.nama_barang', 'like', "%{$search}%")
            ->orWhere('produks.kode', 'like', "%{$search}%")
            ->get(); // <-- ambil semua produk

        if ($produks->isEmpty()) {
            return response()->json('Maaf tidak ada produk ditemukan', 404);
        }

        // Loop tiap produk untuk hitung harga
        $result = $produks->map(function ($produk) {
            $harga = $produk->hrg_modal + ($produk->hrg_modal * $produk->profit / 100);
            return [
                'nama'  => $produk->nama_barang,
                'merek' => $produk->merek,
                'harga' => $harga,
                'stok'  => $produk->total_stok
            ];
        });

        return response()->json($result);
    }
}

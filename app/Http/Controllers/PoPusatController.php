<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePoPusatRequest;
use App\Http\Requests\UpdatePoPusatRequest;
use App\Models\DetailPoPusat;
use App\Models\PoPusat;
use App\Models\PriceHistory;
use App\Models\Produk;
use App\Models\Suplayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PoPusatController extends Controller
{
    public function index()
    {
        $module = 'Po';
        $suplayers = Suplayer::select('uuid', 'nama')->get();
        return view('pages.popusat.index', compact('module', 'suplayers'));
    }

    public function getProdukBySuplayer($params)
    {
        $produks = Produk::where('uuid_suplayer', $params)
            ->select('uuid', 'nama_barang')
            ->get();

        return response()->json($produks);
    }

    public function get(Request $request)
    {
        // Kolom: database => alias
        $columns = [
            'po_pusats.uuid' => 'uuid',
            'po_pusats.uuid_suplayer' => 'uuid_suplayer',
            'po_pusats.no_po' => 'no_po',
            'po_pusats.tanggal_transaksi' => 'tanggal_transaksi',
            'po_pusats.keterangan' => 'keterangan',
            'po_pusats.created_by' => 'created_by',
            'po_pusats.updated_by' => 'updated_by',
            'suplayers.nama' => 'nama_suplayer',
            'COALESCE(SUM(detail_po_pusats.qty * produks.hrg_modal),0)' => 'total_harga',
        ];

        $totalData = PoPusat::count();

        // SELECT dengan alias
        $selects = [];
        foreach ($columns as $dbCol => $alias) {
            $selects[] = "$dbCol as $alias";
        }

        $query = PoPusat::selectRaw(implode(", ", $selects))
            ->leftJoin('suplayers', 'suplayers.uuid', '=', 'po_pusats.uuid_suplayer')
            ->leftJoin('detail_po_pusats', 'detail_po_pusats.uuid_po_pusat', '=', 'po_pusats.uuid')
            ->leftJoin('produks', 'produks.uuid', '=', 'detail_po_pusats.uuid_produk')
            ->groupBy(
                'po_pusats.uuid',
                'po_pusats.uuid_suplayer',
                'po_pusats.no_po',
                'po_pusats.tanggal_transaksi',
                'po_pusats.keterangan',
                'po_pusats.created_by',
                'po_pusats.updated_by',
                'suplayers.nama'
            );

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $dbCol => $alias) {
                    // kalau kolom pakai SUM() skip pencarian
                    if (str_contains($dbCol, 'SUM')) continue;
                    $q->orWhere($dbCol, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $query->count();

        // Sorting
        if ($request->order) {
            $orderColIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];

            $dbCol = array_keys($columns)[$orderColIndex];
            $query->orderByRaw("$dbCol $orderDir");
        } else {
            $query->orderBy('po_pusats.created_at', 'desc');
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

    public function store(StorePoPusatRequest $request)
    {
        // Ambil produk berdasarkan UUID
        $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();

        // Pastikan semua produk ditemukan
        if ($produk->count() !== count($request->uuid_produk)) {
            return response()->json(['status' => 'error', 'message' => 'Ada produk yang tidak ditemukan.'], 404);
        }

        // Simpan po_pusat
        // Format tanggal -> DDMMYY
        $today = now()->format('dmy');
        $prefix = "PO-" . $today;

        // Cari PO terakhir di hari ini
        $lastPo = PoPusat::whereDate('created_at', now()->toDateString())
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
        $po_pusat = PoPusat::create([
            'uuid_suplayer'      => $request->uuid_suplayer,
            'no_po'              => $no_po,
            'tanggal_transaksi'  => $request->tanggal_transaksi,
            'keterangan'         => $request->keterangan,
            'created_by'         => Auth::user()->nama,
        ]);

        // Simpan detail po_pusat
        foreach ($request->uuid_produk as $index => $uuid_produk) {
            $item = $produk->where('uuid', $uuid_produk)->first();

            if (!$item) {
                continue; // skip jika produk tidak ditemukan
            }

            // Bersihkan harga jadi angka murni
            $hargaBaru = (int) preg_replace('/\D/', '', $request->harga[$index]);

            // Simpan detail PO
            DetailPoPusat::create([
                'uuid_po_pusat' => $po_pusat->uuid,
                'uuid_produk'   => $uuid_produk,
                'qty'           => $request->qty[$index],
                'harga'         => $hargaBaru,
            ]);

            // Simpan harga lama sebelum update
            $hargaLama = (int) $item->hrg_modal;

            // Update harga modal produk
            $item->update(['hrg_modal' => $hargaBaru]);

            // Tambahkan price history hanya jika modal berubah
            if ($hargaLama !== $hargaBaru) {
                PriceHistory::create([
                    'uuid_produk' => $item->uuid,
                    'harga'       => $hargaBaru,
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function edit($uuid)
    {
        $po_pusat = PoPusat::where('uuid', $uuid)->first();
        if (!$po_pusat) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }

        // Ambil detail produk
        $detailProduk = DetailPoPusat::where('uuid_po_pusat', $po_pusat->uuid)
            ->select('uuid_produk', 'qty', 'harga')
            ->get();
        $po_pusat->detail_produk = $detailProduk;

        return response()->json($po_pusat);
    }

    public function update(StorePoPusatRequest $request, $uuid)
    {
        // Cari data po_pusat
        $po_pusat = PoPusat::where('uuid', $uuid)->firstOrFail();

        // Ambil produk berdasarkan UUID
        $produk = Produk::whereIn('uuid', $request->uuid_produk)->get();

        // Pastikan semua produk ditemukan
        if ($produk->count() !== count($request->uuid_produk)) {
            return response()->json(['status' => 'error', 'message' => 'Ada produk yang tidak ditemukan.'], 404);
        }

        // Update data po_pusat
        $po_pusat->update([
            'uuid_suplayer'      => $request->uuid_suplayer,
            'tanggal_transaksi'  => $request->tanggal_transaksi,
            'keterangan'         => $request->keterangan,
            'updated_by'         => Auth::user()->nama,
        ]);

        // Hapus detail lama
        DetailPoPusat::where('uuid_po_pusat', $po_pusat->uuid)->delete();

        // Simpan detail baru
        foreach ($request->uuid_produk as $index => $uuid_produk) {
            $item = $produk->where('uuid', $uuid_produk)->first();

            if (!$item) {
                continue; // skip jika produk tidak ditemukan
            }

            // Bersihkan harga jadi angka murni
            $hargaBaru = (int) preg_replace('/\D/', '', $request->harga[$index]);

            // Simpan detail PO
            DetailPoPusat::create([
                'uuid_po_pusat' => $po_pusat->uuid,
                'uuid_produk'   => $uuid_produk,
                'qty'           => $request->qty[$index],
                'harga'         => $hargaBaru,
            ]);

            // Simpan harga lama sebelum update
            $hargaLama = (int) $item->hrg_modal;

            // Update harga modal produk
            $item->update(['hrg_modal' => $hargaBaru]);

            // Tambahkan price history hanya jika modal berubah
            if ($hargaLama !== $hargaBaru) {
                PriceHistory::create([
                    'uuid_produk' => $item->uuid,
                    'harga'       => $hargaBaru,
                ]);
            }
        }


        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        // Cari po_pusat yang mau dihapus
        $po_pusat = PoPusat::where('uuid', $params)->firstOrFail();

        // Hapus detail po_pusat
        DetailPoPusat::where('uuid_po_pusat', $po_pusat->uuid)->delete();

        // Hapus po_pusat utama
        $po_pusat->delete();

        return response()->json(['status' => 'success']);
    }
}

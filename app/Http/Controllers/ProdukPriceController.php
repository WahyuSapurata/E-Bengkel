<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdukPriceRequest;
use App\Http\Requests\UpdateProdukPriceRequest;
use App\Models\Produk;
use App\Models\ProdukPrice;
use Illuminate\Http\Request;

class ProdukPriceController extends Controller
{
    public function index($params)
    {
        $produk = Produk::where('uuid', $params)->first();
        $module = 'Produk Price ' . $produk->nama_barang;
        return view('pages.produkprice.index', compact('module', 'produk'));
    }

    public function get(Request $request, $params)
    {
        $columns = [
            'uuid',
            'qty',
            'harga_jual',
        ];

        $totalData = ProdukPrice::where('uuid_produk', $params)->count();

        $query = ProdukPrice::where('uuid_produk', $params)->select($columns);

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $query->count();

        // Sorting
        if ($request->order) {
            $orderCol = $columns[$request->order[0]['column']];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest();
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        // Format response DataTables
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function store(StoreProdukPriceRequest $request)
    {
        ProdukPrice::create([
            'uuid_produk' => $request->uuid_produk,
            'qty' => $request->qty,
            'harga_jual' => preg_replace('/\D/', '', $request->harga_jual),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(ProdukPrice::where('uuid', $params)->first());
    }

    public function update(StoreProdukPriceRequest $update, $params)
    {
        $kategori = ProdukPrice::where('uuid', $params)->first();
        $kategori->update([
            'uuid_produk' => $update->uuid_produk,
            'qty' => $update->qty,
            'harga_jual' => preg_replace('/\D/', '', $update->harga_jual),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        ProdukPrice::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Classes\Table;
use App\Models\ProductSupplierPrice;
use App\Models\ProductSupplierPriceRepository;
use Cookie;
use Log;
use Response;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ProductSupplierPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $totals = ProductSupplierPriceRepository::totals();
        $total = $totals['total'];
        $tableHead = Table::head(ProductSupplierPriceRepository::$columns);
        $tableBody = Table::body(ProductSupplierPriceRepository::firstPage());
        return view('pages.productSupplierPrice', compact(
            'total', 'tableHead', 'tableBody'
        ));
    }

    /**
     * Json for Datatable
     */
    public function search(Request $request)
    {
        $input = $request->all();

        // set items per page
        if (config('items_per_page') != $input['length'])
            setcookie('items_per_page', $input['length'], time() + 60 * 24 * 1000, '/');

        $totals = ProductSupplierPriceRepository::totals($input);
        $data = ProductSupplierPriceRepository::search($input);

        $ret = [
            'draw' => $input['draw'],
            'recordsTotal' => $totals['total'],
            'recordsFiltered' => $totals['filtered'],
            'data' => $data
        ];

        return Response::json($ret);
    }

    /**
     * Select search
     */
    public function selectSearch(Request $request)
    {
        $input = $request->all();
        $results = ProductSupplierPriceRepository::selectSearch($input['q']);
        return Response::json([ 'results' => $results ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $item = ProductSupplierPriceRepository::create($input);
        // for loading into select2
        return Response::json(['status' => 'success', 'item' => $item]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $productSupplierPrice = ProductSupplierPrice::find($id);
        $item = $productSupplierPrice->toArray();

        $product = $productSupplierPrice->product;
        $item['product'] = ['id' => $product->id, 'text' => $product->name];
        $item['price'] = $product->price;

        $supplier = $productSupplierPrice->supplier;
        $item['supplier'] = ['id' => $supplier->id, 'text' => $supplier->name];

        return Response::json(['status' => 'success', 'item' => $item]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $item = ProductSupplierPriceRepository::update($input);
        return Response::json(['status' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ProductSupplierPrice::find($id)->delete();
        return Response::json(['status' => 'success']);
    }
}

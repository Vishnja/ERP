<?php

namespace App\Http\Controllers;

use App\Classes\Table;
use App\Models\Product;
use App\Models\ProductRepository;
use Cookie;
use Gate;
use Log;
use Response;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $totals = ProductRepository::totals();
        $total = $totals['total'];
        $tableHead = Table::head(ProductRepository::$columns);
        $tableBody = Table::body(ProductRepository::firstPage());
        return view('pages.products', compact(
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

        $totals = ProductRepository::totals($input);
        $data = ProductRepository::search($input);

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
        $results = ProductRepository::selectSearch($input['q']);
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
        $item = ProductRepository::create($input);
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
        $item = Product::find($id)->toArray();
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
        $item = ProductRepository::update($input);
        return Response::json(['status' => 'success']);
    }

    /**
     * History
     */
    public function history($id)
    {
        if (Gate::denies('access', 'watch-history')) return Response::json(['status' => 'fail']);

        $html = ProductRepository::history($id);
        return Response::json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Actions
     */
    public function action(Request $request)
    {
        $input = $request->all();
        ProductRepository::action($input);
        return Response::json(['status' => 'success']);
    }
}

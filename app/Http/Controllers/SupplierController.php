<?php

namespace App\Http\Controllers;

use App\Classes\Table;
use App\Models\Supplier;
use App\Models\SupplierRepository;
use Cookie;
use Log;
use Response;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $totals = SupplierRepository::totals();
        $total = $totals['total'];
        $tableHead = Table::head(SupplierRepository::$columns);
        $tableBody = Table::body(SupplierRepository::firstPage());
        return view('pages.suppliers', compact(
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

        $totals = SupplierRepository::totals($input);
        $data = SupplierRepository::search($input);

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
        $results = SupplierRepository::selectSearch($input['q']);
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
        $item = Supplier::create($input);
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
        $item = Supplier::find($id)->toArray();
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
        $item = SupplierRepository::update($input);
        return Response::json(['status' => 'success', 'item' => $item]);
    }

    /**
     * Actions
     */
    public function action(Request $request)
    {
        $input = $request->all();
        SupplierRepository::action($input);
        return Response::json(['status' => 'success']);
    }
}

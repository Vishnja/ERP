<?php

namespace App\Http\Controllers;

use App\Classes\Table;
use App\Models\Buyer;
use App\Models\BuyerRepository;
use Cookie;
use Log;
use Response;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;


class BuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $totals = BuyerRepository::totals();
        $total = $totals['total'];
        $tableHead = Table::head(BuyerRepository::$columns);
        $tableBody = Table::body(BuyerRepository::firstPage());
        return view('pages.buyers', compact(
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

        $totals = BuyerRepository::totals($input);
        $data = BuyerRepository::search($input);

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
        $results = BuyerRepository::selectSearch($input['q']);
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
        $item = BuyerRepository::create($input);
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
        $buyer = Buyer::find($id)->toArray();
        // for loading into select2
        return Response::json(['status' => 'success', 'item' => $buyer]);
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
        $item = BuyerRepository::update($input);
        // for loading into select2
        return Response::json(['status' => 'success', 'item' => $item]);
    }

    /**
     * Actions
     */
    public function action(Request $request)
    {
        $input = $request->all();
        BuyerRepository::action($input);
        return Response::json(['status' => 'success']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Classes\Table;
use App\Models\Purchase;
use App\Models\PurchaseRepository;
use App\Models\PurchaseProductRepository;
use Cookie;
use Gate;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Log;
use Response;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $totals = PurchaseRepository::totals();
        $total = $totals['total'];
        $purchasesTableHead = Table::head(PurchaseRepository::$columns);
        $purchasesTableBody = Table::body(PurchaseRepository::firstPage());
        $productsTableHead = Table::head(PurchaseProductRepository::$columns);
        // optional. products sent from 'order' to create 'return'.
        $purchaseProducts = PurchaseProductRepository::purchaseProductsFromOrder();

        return view('pages.purchases', compact(
            'total', 'purchasesTableHead', 'purchasesTableBody', 'productsTableHead', 'purchaseProducts'
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

        $totals = PurchaseRepository::totals($input);
        $data = PurchaseRepository::search($input);

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
        $results = PurchaseRepository::selectSearch($input['q']);
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
        $item = PurchaseRepository::create($input);
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
        $item = PurchaseRepository::show($id);
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
        $item = PurchaseRepository::update($input);
        return Response::json(['status' => 'success']);
    }

    /**
     * History
     */
    public function history($id)
    {
        if (Gate::denies('access', 'watch-history')) return Response::json(['status' => 'fail']);

        $html = PurchaseRepository::history($id);
        return Response::json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Actions
     */
    public function action(Request $request)
    {
        $input = $request->all();
        $actionButtonsHtml = PurchaseRepository::action($input);
        return Response::json(['status' => 'success', 'action_buttons_html' => $actionButtonsHtml]);
    }

}

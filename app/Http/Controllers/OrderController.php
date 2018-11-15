<?php

namespace App\Http\Controllers;

use App\Classes\Table;
use App\Models\Order;
use App\Models\OrderRepository;
use App\Models\OrderProductRepository;
use Cookie;
use Gate;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Log;
use Response;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $totals = OrderRepository::totals();
        $total = $totals['total'];
        $ordersTableHead = Table::head(OrderRepository::$columns);
        $ordersTableBody = Table::body(OrderRepository::firstPage());
        $orderProductsTableHead = Table::head(OrderProductRepository::$columns);
        return view('pages.orders', compact(
            'total', 'ordersTableHead', 'ordersTableBody', 'orderProductsTableHead'
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

        $totals = OrderRepository::totals($input);
        $data = OrderRepository::search($input);

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
        $results = OrderRepository::selectSearch($input['q']);
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
        $item = OrderRepository::create($input);
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
        $item = OrderRepository::show($id);
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
        $item = OrderRepository::update($input, $id);
        return Response::json(['status' => 'success']);
    }

    /**
     * Tabs
     */

    // History
    public function history($id)
    {
        if (Gate::denies('access', 'watch-history')) return Response::json(['status' => 'fail']);

        $html = OrderRepository::history($id);
        return Response::json(['status' => 'success', 'html' => $html]);
    }

    // Product Balances
    public function productsBalances(Request $request)
    {
        $html = OrderProductRepository::orderProductsBalances($request->all());
        return Response::json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Actions
     */
    public function action(Request $request)
    {
        $input = $request->all();
        $actionButtonsHtml = OrderRepository::action($input);
        return Response::json(['status' => 'success', 'action_buttons_html' => $actionButtonsHtml]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Classes\Table;
use App\Models\Money;
use App\Models\MoneyRepository;
use Cookie;
use Log;
use Response;


use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class MoneyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $totals = MoneyRepository::totals();
        $total = $totals['total'];
        $tableHead = Table::head(MoneyRepository::$columns);
        $tableBody = Table::body(MoneyRepository::firstPage());
        return view('pages.money', compact(
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

        $totals = MoneyRepository::totals($input);
        $data = MoneyRepository::search($input);

        $ret = [
            'draw' => $input['draw'],
            'recordsTotal' => $totals['total'],
            'recordsFiltered' => $totals['filtered'],
            'data' => $data
        ];

        return Response::json($ret);
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
        $item = MoneyRepository::create($input);
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
        $item = MoneyRepository::show($id);
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
        $item = MoneyRepository::update($input);
        return Response::json(['status' => 'success']);
    }

    /**
     * Actions
     */
    public function action(Request $request)
    {
        $input = $request->all();
        $actionButtonsHtml = MoneyRepository::action($input);
        return Response::json(['status' => 'success', 'action_buttons_html' => $actionButtonsHtml]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Classes\Table;
use App\Models\IncomeExpenseItem;
use App\Models\IncomeExpenseItemRepository;
use Cookie;
use Log;
use Response;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class IncomeExpenseItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $totals = IncomeExpenseItemRepository::totals();
        $total = $totals['total'];
        $tableHead = Table::head(IncomeExpenseItemRepository::$columns);
        $tableBody = Table::body(IncomeExpenseItemRepository::firstPage());
        return view('pages.incomeExpenseItems', compact(
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

        $totals = IncomeExpenseItemRepository::totals($input);
        $data = IncomeExpenseItemRepository::search($input);

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
        $results = IncomeExpenseItemRepository::selectSearch($input['q']);
        return Response::json([ 'results' => $results ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
        $item = IncomeExpenseItemRepository::create($input);
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
        $item = IncomeExpenseItem::find($id)->toArray();
        return Response::json(['status' => 'success', 'item' => $item]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $item = IncomeExpenseItemRepository::update($input);
        return Response::json(['status' => 'success', 'item' => $item]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        IncomeExpenseItem::find($id)->delete();
        return Response::json(['status' => 'success']);
    }
}

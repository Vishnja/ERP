<?php

namespace App\Http\Controllers;

use App\Classes\Table;
use App\Models\Role;
use App\Models\RoleRepository;
use Cookie;
use Log;
use Response;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(\Menu $menu)
    {
        $totals = RoleRepository::totals();
        $total = $totals['total'];
        $tableHead = Table::head(RoleRepository::$columns);
        $tableBody = Table::body(RoleRepository::firstPage());
        $menuCapabilities = RoleRepository::menuCapabilities($menu);
        $otherCapabilities = RoleRepository::$otherCapabilities;
        return view('pages.roles', compact(
            'total', 'tableHead', 'tableBody', 'menuCapabilities', 'otherCapabilities'
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

        $totals = RoleRepository::totals($input);
        $data = RoleRepository::search($input);

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
        //Log::info($input);
        $item = RoleRepository::create($input);
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
        $item = Role::find($id)->toArray();
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
        $item = RoleRepository::update($input);
        return Response::json(['status' => 'success', 'item' => $item]);
    }

    /**
     * Actions
     */
    public function action(Request $request)
    {
        $input = $request->all();
        return Response::json(RoleRepository::action($input));
    }
}

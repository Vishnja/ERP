<?php

namespace App\Http\Controllers;

use App\Classes\Table;
use App\Models\User;
use App\Models\UserRepository;
use App\Models\ImageRepository;
use Cookie;
use Log;
use Redirect;
use Response;
use Gate;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $totals = UserRepository::totals();
        $total = $totals['total'];
        $tableHead = Table::head(UserRepository::$columns);
        $tableBody = Table::body(UserRepository::firstPage());

        return view('pages.users', compact(
            'total', 'tableHead', 'tableBody', 'dropzonePreloadedImages'
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

        $totals = UserRepository::totals($input);
        $data = UserRepository::search($input);

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
        $results = UserRepository::selectSearch($input['q']);
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
        $item = UserRepository::create($input);
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
        $item = User::find($id)->toArray();
        if ($item['photo'])
            $item['preloadedImage'] = ImageRepository::dropzonePreloadedImage($item['photo']);

        return Response::json(['status' => 'success', 'item' => $item]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showProfile($id)
    {
        $user = User::find($id);
        if (Gate::denies('access', $user)) abort(403, "Access denied.");

        $pageTitle = 'Профиль';
        $user->preloadedImage = $user->photo ?
                                json_encode(ImageRepository::dropzonePreloadedImage($user->photo)) :
                                json_encode(null);

        return view('pages.profile', compact('user', 'pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request, $id)
    {
        $user = User::find($id);
        if (Gate::denies('access', $user)) abort(403, "Access denied.");

        $input = $request->all();
        UserRepository::update($input);

        return Redirect::back()->with('success', 'true');
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
        $user = User::find($id);
        if (Gate::denies('access', $user)) abort(403, "Access denied.");

        $input = $request->all();
        $item = UserRepository::update($input);
        return Response::json(['status' => 'success', 'item' => $item]);
    }

    /**
     * Actions
     */
    public function action(Request $request)
    {
        $input = $request->all();
        UserRepository::action($input);
        return Response::json(['status' => 'success']);
    }
}

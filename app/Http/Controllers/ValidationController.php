<?php

namespace App\Http\Controllers;

use App\Models\ValidationRepository;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Log;
use Response;

class ValidationController extends Controller
{

    public function validation(Request $request)
    {
        $input = $request->all();

        return Response::json( ValidationRepository::validate($input) );
    }
}

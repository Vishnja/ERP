<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\OrderProductRepository;

class OrderProductController extends Controller
{
    public function balances($id)
    {
        $html = OrderProductRepository::history($id);
        return Response::json(['status' => 'success', 'html' => $html]);
    }
}

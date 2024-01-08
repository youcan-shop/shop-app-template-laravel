<?php

namespace YouCan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QantraBounceController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('youcan::qantra-bounce', [
            'url' => $request->get('bounce_to')
        ]);
    }
}

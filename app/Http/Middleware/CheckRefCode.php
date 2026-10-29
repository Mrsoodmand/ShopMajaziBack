<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckRefCode
{

    public function handle($request, Closure $next)
    {

        if ($request->has('ref')) {
            Session::put('RefCode',$request->input('ref'));
        }
        return $next($request);
    }
}

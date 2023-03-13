<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Viveksingh\DynamicDatatable;

class UserController extends Controller
{

    public function index()
    {
        return view('index');
    }

    public function getUser(Request $request)
    {
        return DynamicDatatable::table($request);
    }

 
}

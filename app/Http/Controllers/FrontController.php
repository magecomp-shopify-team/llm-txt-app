<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Osiset\ShopifyApp\Storage\Models\Plan;

class FrontController extends Controller
{
    public function __construct()
    {
        $this->middleware('verify.shopify')->only('index');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $planConfig = Plan::all();
        return view("react", ["user" => $user, "planConfig" => $planConfig]);
    }
}

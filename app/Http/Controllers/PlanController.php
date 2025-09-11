<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Osiset\ShopifyApp\Actions\GetPlanUrl;
use Osiset\ShopifyApp\Objects\Values\NullablePlanId;
use Osiset\ShopifyApp\Storage\Models\Plan;
use Osiset\ShopifyApp\Storage\Queries\Shop as ShopQuery;

class PlanController extends Controller
{
    function index(int $plan, ShopQuery $shopQuery, GetPlanUrl $getPlanUrl, Request $request)
    {
        $planObj = Plan::where('id', $plan)->first();
        if (!$planObj) {
            return response()->json(['error' => 'Plan not found'], 404);
        }
        $shop = User::where('name', Auth::user()->name)->first();
        $host = urldecode($request->get('host'));

        $url = $getPlanUrl(
            $shop->getId(),
            NullablePlanId::fromNative($plan),
            $host
        );

        return response()->json(['url' => $url]);
    }
}

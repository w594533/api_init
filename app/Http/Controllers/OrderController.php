<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Services\PaymentService;
use Log;

class OrderController extends Controller
{

    public function buy(Request $request)
    {

    }

    /**
     * 支付
     */
    public function pay(Request $request, PaymentService $service)
    {
        $result = $service->pay($request, $this->client());
        return $this->success($result);
    }
}
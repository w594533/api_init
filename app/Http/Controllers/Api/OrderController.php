<?php
/**
 * 订单控制器
 */
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Models\Order;
use Log;

class OrderController extends Controller
{
    public function buy(Request $request, OrderService $service)
    {
        $result = $service->create_order($request);
        // $result = $service->create_pay_return($request, $order, $this->client());
        return $this->success($result);
    }

    /**
     * 显示
     */
    public function show($id, OrderService $service)
    {
        $result = $service->show($id);
        return $this->success($result);
    }

    /**
     * 单独吊起支付
     */
    public function pay($id, Request $request, OrderService $service)
    {
        $result = $service->pay($id, $request, $this->client());
        return $this->success($result);
    }

    /**
     * 列表
     */
    public function index(Request $request, OrderService $service)
    {
        $result = $service->index($request);
        return $this->success($result);
    }

    /**
     * 根据订单号查找订单支付状态
     */
    public function find($id, PaymentService $service)
    {
        $order = Order::where('id', $id)->firstOrFail();

        // 订单已支付
        if ($order->paid_at) {
            return $this->message('支付成功');
        }

        // 判断该订单的支付方式
        switch ($order->payment_method) {
            case Order::PAYMENT_METHOD_WEXIN:
                try {
                    $data = app('wechat_pay')->find($order->no);
                    // \Log::debug('查询订单返回结果', [$data]);
                } catch (\Yansongda\Pay\Exceptions\GatewayException $exception) {
                    \Log::info('查询订单失败' . $exception->getMessage());
                    return $this->failed('查询订单失败');
                }
                if (isset($data->trade_state) && $data->trade_state == 'SUCCESS') {
                    (new OrderService())->afterPaid($order, Order::PAYMENT_METHOD_WEXIN, $data);
                    return $this->message($data->trade_state_desc);
                } else {
                    \Log::debug('查询订单失败', [$data]);
                    return $this->failed(isset($data->err_code_des) ? $data->err_code_des : '系统错误');
                }
                break;
            case Order::PAYMENT_METHOD_ALIPAY:
                try {
                    $data = app('alipay')->find($order->no);
                    // \Log::debug('data', [$data]);
                } catch (\Yansongda\Pay\Exceptions\GatewayException $exception) {
                    \Log::info('查询订单失败' . $exception->getMessage());
                    return $this->failed('查询订单失败');
                }

                if(in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                    (new OrderService())->afterPaid($order, Order::PAYMENT_METHOD_ALIPAY, $data);
                    return $this->message('查询成功');
                } else {
                    return $this->failed(isset($data['msg']) ? $data['msg'] : '系统错误');
                }
                break;
            default:
                // 原则上不可能出现，这个只是为了代码健壮性
                return $this->failed('未知订单支付方式：'.$order->payment_method);
                break;
        }
    }
}
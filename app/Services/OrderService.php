<?php
namespace App\Services;

use App\Models\Order;
use App\Exceptions\InvalidRequestException;

class OrderService{

    public function show($id)
    {
        $order = Order::findOrFail($id);
        return $order;
    }

    /**
     * 生成订单
     */
    public function create_order($request)
    {
        $price = mt_rand(1, 99);

        $order = Order::create([
            'title' => '测试商品' . time(),
            'price' => $price,
            'amount' => 1,
            'total_price' => $price * 1,
            'payment_method' => $request->payment_method
        ]);
        $order->save();

        return $order;
    }

    /**
     * 单独调用支付
     */
    public function pay($id, $request, $client, $alipay_method = 'POST')
    {
        $order = Order::findOrFail($id);
        $order->update(['payment_method' => $request->payment_method]);
        return $this->create_pay_return($request, $order, $client, $alipay_method);
    }

    /**
     * 列表
     */
    public function index($request)
    {
        $limit = $request->input('limit', 15);
        $orders = Order::query()->orderBy('created_at', 'desc')->paginate($limit);
        return $orders;
    }

    /**
     * 生成支付返回参数
     */
    public function create_pay_return($request, $order, $client, $alipay_method = 'POST')
    {
        $weixin_parameters = null;
        $order_data = $order->toArray();
        if ($order->payment_method == Order::PAYMENT_METHOD_WEXIN) {
            $result = (new PaymentService())->createWeixinParameters($order_data, $client);
        } else if ($order->payment_method == Order::PAYMENT_METHOD_ALIPAY) {
            $default_return_url = $request->expectsJson() ? route('payment.alipay.api_return') : route('payment.alipay.return');
            $order_data['return_url'] = $request->input('return_url', $default_return_url); # 这里的returnurl可以根据是api请求或者普通请求改变
            
            $result = (new PaymentService())->createAlipayParameters($order_data, $client, $alipay_method);
        } else {
            throw new InvalidRequestException('不支持的支付方式');
        }

        return $result;
    }

    public function afterPaid($order, $payment_method, $callback_data)
    {
        $payment_no = $payment_method == Order::PAYMENT_METHOD_ALIPAY ? $callback_data->trade_no : $callback_data->transaction_id;
        $order->update([
            'paid_at' => 1, 
            'status' => Order::STATUS_PAID, 
            'payment_method' => $payment_method, 
            'callback_data' => json_encode($callback_data->toArray()),
            'payment_no' => $payment_no
        ]);
    }

}
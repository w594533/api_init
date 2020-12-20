<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Models\Order;
use Log;

class PaymentController
{
    // 前端回调页面
    public function alipayReturn()
    {
        try {
            $data = app('alipay')->verify();
        } catch (\Exception $e) {
            return redirect()->route('page.error')->with('message', '数据不正确');
        }
        $order = Order::where('no', $data->out_trade_no)->first();
        if (!$order) {
            exit('订单不存在');
        }
        exit('支付成功');
        // return redirect()->route('orders.show', ['id' => $order->id]);
    }

    /**
     * 给api用的支付宝return_url
     */
    public function alipayApiReturn()
    {
        try {
            $data = app('alipay')->verify();
        } catch (\Exception $e) {
            exit('数据不正确');
        }
        $order = Order::where('no', $data->out_trade_no)->first();
        if (!$order) {
            exit('订单不存在');
        }
        exit('支付成功');
    }

    public function alipayNotify()
    {
         // 校验输入参数
        $data  = app('alipay')->verify();
        \Log::debug('alipayNotify', $data->toArray());
        // 如果订单状态不是成功或者结束，则不走后续的逻辑
        // 所有交易状态：https://docs.open.alipay.com/59/103672
        if(!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return app('alipay')->success();
        }
        if (isset($data->passback_params) && $data->passback_params == 'test') {//支付宝测试
            \Log::info('测试支付成功回调返回成功');
            return app('alipay')->success();
        }
        // $data->out_trade_no 拿到订单流水号，并在数据库中查询
        $order = Order::where('no', $data->out_trade_no)->first();
        // 正常来说不太可能出现支付了一笔不存在的订单，这个判断只是加强系统健壮性。
        if (!$order) {
            return 'fail';
        }
        // 如果这笔订单的状态已经是已支付
        if ($order->paid_at) {
            // 返回数据给支付宝
            return app('alipay')->success();
        }

        $this->afterPaid($order, Order::PAYMENT_METHOD_ALIPAY, $data);

        return app('alipay')->success();
    }

    public function wechatNotify()
    {
        // 校验回调参数是否正确
        $data  = app('wechat_pay')->verify();
        \Log::debug('微信支付返回结果', [$data]);

        // 找到对应的订单
        $order = Order::where('no', $data->out_trade_no)->first();
        // 订单不存在则告知微信支付
        if (!$order) {
            return 'fail';
        }
        // 订单已支付
        if ($order->paid_at) {
            // 告知微信支付此订单已处理
            return app('wechat_pay')->success();
        }

        //判断支付金额是否与订单一致，暂不判断
        if (isset($data->result_code) && $data->result_code == 'SUCCESS') {
            $this->afterPaid($order, Order::PAYMENT_METHOD_WEXIN, $data);
        }

        return app('wechat_pay')->success();
    }

    private function afterPaid($order, $payment_method, $callback_data)
    {
        (new OrderService())->afterPaid($order, $payment_method, $callback_data);
    }
}
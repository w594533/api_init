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

    /**
     * 支付支付结果通知
     */
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

        $this->afterPaid($order, Order::PAYMENT_METHOD_ALIPAY, $data->toArray());

        return app('alipay')->success();
    }

    /**
     * 微信支付结果通知
     */
    public function wechatNotify()
    {
        $app = app('wechat.payment');
        $response = $app->handlePaidNotify(function($message, $fail){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $order = Order::where('no', $message['out_trade_no'])->first();
        
            if (!$order || $order->paid_at) { // 如果订单不存在 或者 订单已经支付过了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
        
            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////
        
            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if (array_get($message, 'result_code') === 'SUCCESS') {
                    $this->afterPaid($order, Order::PAYMENT_METHOD_WEXIN, $message);
                } elseif (array_get($message, 'result_code') === 'FAIL') {
                    // 用户支付失败
                    // $order->status = 'paid_fail';
                }
            } else {
                \Log::info($message['out_trade_no'] . '通信失败，请稍后再通知我');
                return $fail('通信失败，请稍后再通知我');
            }
            return true; // 返回处理完成
        });
        return $response;
    }

    private function afterPaid($order, $payment_method, $callback_data)
    {
        (new OrderService())->afterPaid($order, $payment_method, $callback_data);
    }
}
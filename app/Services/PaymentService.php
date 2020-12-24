<?php
namespace App\Services;

use App\Models\Order;
use App\Exceptions\InvalidRequestException;

class PaymentService{

    public function createAlipayParameters($order_data, $client, $method = 'POST')
    {
        $pay = [
            'out_trade_no' => $order_data['no'], // 订单编号，需保证在商户端不重复
            'total_amount' => app()->environment() !== 'production' ? 0.01 : $order_data['total_price'],// 订单金额，单位元，支持小数点后两位
            'subject'      => $order_data['title'], // 订单标题
            'return_url' => $order_data['return_url'],
            'http_method' => $method
        ];
        if (isMobile()) {
            // 调用手机支付宝
             return [
                 'form' => app('alipay')->wap($pay)->send()
             ]; 
         }
         // 调用支付宝的网页支付
         return [
            'form' => app('alipay')->web($pay)->send()
        ];
        //如果是原生html,直接使用return app('alipay')->web($pay)
    }

    public function createWeixinParameters($order_data, $client)
    {
        if ($client == 'weixin') {
            return $this->createJsApiParameters($order_data);
        } else if ($client == 'mobile') {
            return $this->createH5Parameters($order_data);
        } else if ($client == 'pc') {
            return $this->createNative($order_data);
        }
        return null;
    }

    //微信内部支付
    public function createJsApiParameters($order_data)
    {
        $app = app('wechat.payment');
        $prepay_result = $app->order->unify($this->unifyParameters($order_data, 'JSAPI'));
        if (isset($prepay_result['return_code']) && $prepay_result['return_code'] == 'SUCCESS') {
            //WeixinJSBridge,仅限于微信内部使用
            $result = $app->jssdk->bridgeConfig($prepay_result['prepay_id']); //json数据
            return json_decode($result, true);
        }
        throw new InvalidRequestException('支付错误');
        
    }

    //手机网站支付
    public function createH5Parameters($order_data)
    {
        $app = app('wechat.payment');
        $prepay_result = $app->order->unify($this->unifyParameters($order_data, 'MWEB'));
        // \Log::debug('prepay_result', $prepay_result);
        if (isset($prepay_result['return_code']) && $prepay_result['return_code'] == 'SUCCESS') {
            //sdkConfig,仅限于手机记浏览器
            $result = $app->jssdk->sdkConfig($prepay_result['prepay_id']); //json数据
            $result = is_string($result) ? json_decode($result, true) : $result;
            $result['mweb_url'] = $prepay_result['mweb_url'] . '&redirect_url=' . urlencode(route('orders.show', ['id' => $order_data['id'], 'pay_return' => 1]));
            // \Log::debug('result', $result);
            return $result;
        }
        throw new InvalidRequestException('支付错误');
    }

    //pc微信支付
    public function createNative($order_data)
    {
        // $app = app('wechat.payment');
        // $result = $app->order->unify($this->unifyParameters($order_data, 'NATIVE'));
        // return base64_encode(QrCode::format('png')->size(300)->generate($result['code_url']));
        return '';
    }

    /**
     * 微信支付参数
     */
    private function unifyParameters($order, $trade_type)
    {
        \Log::info($trade_type);
        if ($trade_type == 'JSAPI') {
            if (!$order['openid']) {
                throw new InvalidRequestException('openid 参数错误');
            }
        }
        
        return [
            'body' => $order['title'],
            'out_trade_no' => $order['no'],
            'total_fee' => app()->environment() !== 'production' ? 0.01 * 100 : $order['total_price'] * 100, //单位，分
            'trade_type' => $trade_type, // 请对应换成你的支付方式对应的值类型
            'openid' => $trade_type == 'JSAPI' ? $order['openid'] : '',
            'product_id' => $trade_type == 'NATIVE' ? date("YmdHis") . $order['id'] : '',
            'attach' => isset($order['attach']) ? $order['attach'] : '', #附加数据
        ];
    }
}
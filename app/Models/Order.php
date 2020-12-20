<?php

namespace App\Models;

use App\Services\Hashids as HashidsService;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    const STATUS_PENDING = 1;#待支付
    const STATUS_PAID = 2;#已支付

    const PAYMENT_METHOD_WEXIN = 1;
    const PAYMENT_METHOD_ALIPAY = 2;
    const PAYMENT_METHOD_SYSTEM = 3; #系统自动计算，免费

    public static $statusMap = [
        self::STATUS_PENDING    => '待支付',
        self::STATUS_PAID    => '已支付',
    ];
    public static $paymentMethodMap = [
        self::PAYMENT_METHOD_WEXIN    => '微信',
        self::PAYMENT_METHOD_ALIPAY    => '支付宝',
        self::PAYMENT_METHOD_SYSTEM => '系统',
    ];

    protected $fillable = [
        'user_id',
        'no',
        'title',
        'price',
        'amount',
        'total_price',
        'payment_method',
        'status',
        'extra',
        'paid_at',
        'callback_data',
        'payment_no',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'extra'     => 'json',
    ];
    protected $dates = [
        'paid_at',
    ];
    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            // 如果模型的 no 字段为空
            if (!$model->no) {
                // 调用 findAvailableNo 生成订单流水号
                $model->no = static::findAvailableNo();
                // 如果生成失败，则终止创建订单
                if (!$model->no) {
                    return false;
                }
            }
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 获取订单编号
     */
    public static function findAvailableNo()
    {
        // 订单流水号前缀
        $prefix = date('YmdHis');
        for ($i = 0; $i < 10; $i++) {
            // 随机生成 6 位的数字
            $no = $prefix.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断是否已经存在
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
        }
        return false;
    }

}
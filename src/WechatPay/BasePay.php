<?php

namespace Jormin\Pay\WechatPay;

include_once dirname(__FILE__) . '/../../sdk/wechat_php_sdk_v3.0.9/lib/WxPay.Config.Interface.php';
include_once dirname(__FILE__) . '/../../sdk/wechat_php_sdk_v3.0.9/lib/WxPay.Api.php';

use Jormin\Pay\BaseObject;

/**
 * Class BasePay
 * @package Jormin\Aliyun
 */
class BasePay extends BaseObject {

    /**
     * 微信支付配置
     *
     * @var PayConfig
     */
    protected $payConfig;

    public function __construct($config)
    {
        $this->payConfig = $config;
    }

    /**
     * 组装统一订单参数
     *
     * @param $order
     * @param $body
     * @param $amount
     * @param array $otherParams
     * @return \WxPayUnifiedOrder
     */
    public static function makeUnifiedOrderContent($order, $body, $amount, $otherParams=[]){
        $unifiedOrderInpiut = new \WxPayUnifiedOrder();
        $unifiedOrderInpiut->SetBody($body);
        $unifiedOrderInpiut->SetOut_trade_no($order);
        $unifiedOrderInpiut->SetTotal_fee($amount);
        isset($otherParams['detail']) && $unifiedOrderInpiut->SetDetail($otherParams['detail']);
        isset($otherParams['attach']) && $unifiedOrderInpiut->SetAttach($otherParams['attach']);
        isset($otherParams['startTime']) && $unifiedOrderInpiut->SetTime_start($otherParams['startTime']);
        isset($otherParams['startExpire']) && $unifiedOrderInpiut->SetTime_expire($otherParams['startExpire']);
        isset($otherParams['goodsTag']) && $unifiedOrderInpiut->SetGoods_tag($otherParams['goodsTag']);
        isset($otherParams['spbillCreateIP']) && $unifiedOrderInpiut->SetSpbill_create_ip($otherParams['spbillCreateIP']);
        isset($otherParams['openId']) && $unifiedOrderInpiut->SetOpenid($otherParams['openId']);
        return $unifiedOrderInpiut;
    }
}

<?php

namespace Jormin\Pay\WxPay;

use WxPayException;

/**
 * Class AppPay
 * @package Jormin\Pay\WxPay
 */
class AppPay extends BasePay
{

    /**
     * App支付参数
     *
     * @param $UnifiedOrderResult
     * @return array
     * @throws WxPayException
     */
	public function GetAppParameters($UnifiedOrderResult)
	{
		if(!array_key_exists("appid", $UnifiedOrderResult)
		|| !array_key_exists("prepay_id", $UnifiedOrderResult)
		|| $UnifiedOrderResult['prepay_id'] == "")
		{
			throw new WxPayException("参数错误");
		}

        $wxPayDataBase = new \WxPayDataBase();
		$parameters = [
		    'appID' => $UnifiedOrderResult["appid"],
            'partnerID' => '',
            'prepayID' => $UnifiedOrderResult['prepay_id'],
            'package' => 'Sign=WXPay',
            'nonceStr' => \WxPayApi::getNonceStr(),
            'timestamp' => time(),
            'sign' => $wxPayDataBase->MakeSign($this->payConfig)
        ];
		return $parameters;
	}
}
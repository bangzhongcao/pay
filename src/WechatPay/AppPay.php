<?php

namespace Jormin\Pay\WechatPay;

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
		    'appid' => $UnifiedOrderResult["appid"],
            'partnerid' => $UnifiedOrderResult['mch_id'],
            'prepayid' => $UnifiedOrderResult['prepay_id'],
            'package' => 'Sign=WXPay',
            'noncestr' => \WxPayApi::getNonceStr(),
            'timestamp' => time()
        ];
        $wxPayDataBase->SetValues($parameters);
        $parameters['sign'] = $wxPayDataBase->MakeSign($this->payConfig, false);
		return $parameters;
	}
}

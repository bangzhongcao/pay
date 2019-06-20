<?php
/**
 * Created by PhpStorm.
 * User: Jormin
 * Date: 2019-06-19
 * Time: 14:31
 */

namespace Jormin\Pay\YQBPay;

/**
 * 微信聚合收银台
 * @package Jormin\Pay\YQBPay
 */
class WechatCashier extends BasePay
{

    /**
     * @var bool 沙箱模式
     */
    protected $sandbox = false;

    /**
     * @var string 支付宝网关
     */
    protected $gatewayUrl = 'https://mapi.1qianbao.com';

    /**
     * @var string 支付宝沙箱网关
     */
    protected $sandboxGatewayUrl = 'https://test-mapi.stg.1qianbao.com';

    /**
     * WechatCashier constructor.
     * @param string $merchantKey
     * @param bool $sandbox
     */
    public function __construct(string $merchantKey, bool $sandbox = false)
    {
        parent::__construct($merchantKey);
        $this->sandbox = $sandbox;
    }

    /**
     * 落单
     * @param array $params
     * @return array
     */
    public function revOrder(array $params)
    {
        $params['signature'] = $this->signature($params);
        $response = $this->http('POST', $this->getRealGatewayUrl() . '/revOrder', $params);
        return $response;
    }

    /**
     * 壹钱包收单005通用交易查询
     * @param array $params
     * @return array
     */
    public function query(array $params){
        $params['signature'] = $this->signature($params);
        $response = $this->http('POST', $this->getRealGatewayUrl() . '/ffastpay', $params);
        return $response;
    }

    /**
     * 获取真实接口地址
     * @return string
     */
    public function getRealGatewayUrl()
    {
        return $this->sandbox ? $this->sandboxGatewayUrl : $this->gatewayUrl;
    }

}
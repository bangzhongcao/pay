<?php

namespace Jormin\Pay;

use Jormin\Pay\YQBPay\WechatCashier;

/**
 * Class YQBPay
 * @package Jormin\Pay
 */
class YQBPay extends BaseObject
{

    /**
     * @var bool 沙箱模式
     */
    protected $sandbox = false;

    /**
     * @var string 商户号
     */
    protected $merchantId = '';

    /**
     * @var string 商户密钥
     */
    protected $merchantKey = '';

    /**
     * @var string 版本号，枚举类型，接口版本号不能低于1.0.0 目前为1.0.0
     */
    protected $version = '1.0.0';

    /**
     * @var string 编码
     */
    protected $charset = 'UTF-8';

    /**
     * @var string 签名方法，枚举类型，在交易应答中该域内容应与交易请求一致。 目前支持的签名算法包括SHA-256
     */
    protected $signMethod = 'SHA-256';

    /**
     * @var string 交易币种
     */
    protected $orderCurrency = 'CNY';

    /**
     * @var string 支付结果异步通知地址
     */
    public $notifyUrl;

    /**
     * @var string 前台页面跳转地址
     */
    public $redirectUrl;

    /**
     * @var string 失败跳转地址
     */
    public $failbackUrl;

    /**
     * YQBPay constructor.
     * @param string $merchantId
     * @param string $merchantKey
     * @param string $notifyUrl
     * @param string $redirectUrl
     * @param string $failbackUrl
     */
    public function __construct(string $merchantId, string $merchantKey, string $notifyUrl, string $redirectUrl, string $failbackUrl)
    {
        $this->merchantId = $merchantId;
        $this->merchantKey = $merchantKey;
        $this->notifyUrl = $notifyUrl;
        $this->redirectUrl = $redirectUrl;
        $this->failbackUrl = $failbackUrl;
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * @param bool $sandbox
     */
    public function setSandbox(bool $sandbox)
    {
        $this->sandbox = $sandbox;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId(string $merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getMerchantKey(): string
    {
        return $this->merchantKey;
    }

    /**
     * @param string $merchantKey
     */
    public function setMerchantKey(string $merchantKey)
    {
        $this->merchantKey = $merchantKey;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset(string $charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getSignMethod(): string
    {
        return $this->signMethod;
    }

    /**
     * @param string $signMethod
     */
    public function setSignMethod(string $signMethod)
    {
        $this->signMethod = $signMethod;
    }

    /**
     * @return string
     */
    public function getOrderCurrency(): string
    {
        return $this->orderCurrency;
    }

    /**
     * @param string $orderCurrency
     */
    public function setOrderCurrency(string $orderCurrency)
    {
        $this->orderCurrency = $orderCurrency;
    }

    /**
     * @return string
     */
    public function getNotifyUrl(): string
    {
        return $this->notifyUrl;
    }

    /**
     * @param string $notifyUrl
     */
    public function setNotifyUrl(string $notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
     * @param string $redirectUrl
     */
    public function setRedirectUrl(string $redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function getFailbackUrl(): string
    {
        return $this->failbackUrl;
    }

    /**
     * @param string $failbackUrl
     */
    public function setFailbackUrl(string $failbackUrl)
    {
        $this->failbackUrl = $failbackUrl;
    }

    /**
     * 微信聚合收银台落单
     * @param string $order
     * @param int $amount
     * @param null $body
     * @param array $otherParams
     * @param array $merReserved
     * @param array $riskInfo
     * @return array
     */
    public function wechatCashierRevOrder(string $order, int $amount, $body = null, $otherParams = [], $merReserved = [], $riskInfo = [])
    {
        $params = [
            'version' => $this->version,
            'charset' => $this->charset,
            'signMethod' => $this->signMethod,
            'transType' => '001',
            'transCode' => '0071',
            'bizType' => '000003',
            'merchantId' => $this->merchantId,
            'backEndUrl' => $this->notifyUrl,
            'frontEndUrl' => $this->redirectUrl,
            'cancelUrl' => $this->failbackUrl,
            'orderTime' => date('YmdHis'),
            'mercOrderNo' => $order,
            'merchantTransDesc' => $body,
            'orderAmount' => $amount,
            'orderCurrency' => $this->orderCurrency,
            'merReserved' => count($merReserved) ? json_encode($merReserved) : null,
            'riskInfo' => count($riskInfo) ? json_encode($riskInfo) : null
        ];
        if ($otherParams) {
            $params = array_merge($params, $otherParams);
        }
        $wechatCashier = new WechatCashier($this->merchantKey, $this->sandbox);
        $response = $wechatCashier->revOrder($params);
        return $response;
    }

    /**
     * 微信聚合收银台005通用交易查询
     * @param string $order
     * @param array $otherParams
     * @return array
     */
    public function wechatCashierQuery(string $order, $otherParams = [])
    {
        $params = [
            'version' => $this->version,
            'charset' => $this->charset,
            'signMethod' => $this->signMethod,
            'transType' => '005',
            'merchantId' => $this->merchantId,
            'cancelUrl' => $this->failbackUrl,
            'mercOrderNo' => $order
        ];
        if ($otherParams) {
            $params = array_merge($params, $otherParams);
        }
        $wechatCashier = new WechatCashier($this->merchantKey, $this->sandbox);
        $response = $wechatCashier->query($params);
        return $response;
    }

    /**
     * 回调校验签名
     * @param array $params
     * @return array
     */
    public function wechatCashierNotifyVerify(array $params)
    {
        $wechatCashier = new WechatCashier($this->merchantKey, $this->sandbox);
        $result = $wechatCashier->verify($params['signature'], $params);
        if (!$result) {
            return $this->error('签名校验不通过');
        }
        return $this->success('签名校验通过');
    }

    /**
     * 回复通知
     * @param bool $success 是否成功
     * @param bool $die 是否结束脚本运行
     */
    public function wechatCashierNotifyReply($success, $die = true)
    {
        $wechatCashier = new WechatCashier($this->merchantKey, $this->sandbox);
        $params = [
            'version' => $this->version,
            'charset' => $this->charset,
            'signMethod' => $this->signMethod,
            'successLable' => $success ? 'S' : 'N',
        ];
        $params['signature'] = $wechatCashier->signature($params);
        $responseData = [];
        foreach ($params as $key => $value) {
            $responseData[] = $key . '=' . $value;
        }
        echo implode('&', $responseData);
        $die && die;
    }

}

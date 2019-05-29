<?php

namespace Jormin\Pay;

include_once dirname(__FILE__) . '/../sdk/alipay-sdk-PHP-3.3.2/AopSdk.php';

/**
 * Class Alipay
 * @package Jormin\Pay
 */
class Alipay extends BaseObject
{

    /**
     * @var string 支付宝应用ID
     */
    protected $appId;

    /**
     * @var bool 沙箱模式
     */
    protected $sandbox = false;

    /**
     * @var string 支付宝网关
     */
    protected $gatewayUrl = 'https://openapi.alipay.com/gateway.do';

    /**
     * @var string 支付宝沙箱网关
     */
    protected $sandboxGatewayUrl = 'https://openapi.alipaydev.com/gateway.do';

    /**
     * @var string 授权Url
     */
    protected $authUrl = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm';

    /**
     * @var string 授权沙箱Url
     */
    protected $sandboxAuthUrl = 'https://openauth.alipaydev.com/oauth2/publicAppAuthorize.htm';

    /**
     * @var string 支付宝公钥
     */
    public $alipayPublicKey;

    /**
     * @var string 商户私钥
     */
    public $merchantPrivateKey;

    /**
     * @var string 编码格式
     */
    public $charset;

    /**
     * @var string Token
     */
    public $token = NULL;

    /**
     * @var string 返回数据格式
     */
    public $format = "json";

    /**
     * @var string 签名方式
     */
    public $signType;

    /**
     * @var string 支付结果异步通知地址
     */
    public $notifyUrl;

    /**
     * @var string 前台页面跳转地址
     */
    public $returnUrl;

    /**
     * @var \AopClient Aop CLient
     */
    public $aopClient;

    /**
     * @var string 业务参数
     */
    protected $payBizContent;

    /**
     * Alipay constructor.
     * @param string $appId AppId
     * @param string $alipayPublicKey 支付宝公钥
     * @param string $merchantPrivateKey 商户私钥
     * @param string $notifyUrl 支付结果异步通知地址
     * @param string $returnUrl 前台页面跳转地址
     * @param string $charset 编码
     * @param string $signType 签名方式
     * @param bool $sandbox
     */
    function __construct($appId, $alipayPublicKey, $merchantPrivateKey, $notifyUrl, $returnUrl = null, $charset = 'UTF-8', $signType = 'RSA2', $sandbox = false)
    {
        $this->appId = $appId;
        $this->alipayPublicKey = $alipayPublicKey;
        $this->merchantPrivateKey = $merchantPrivateKey;
        $this->notifyUrl = $notifyUrl;
        $this->returnUrl = $returnUrl;
        $this->charset = $charset;
        $this->signType = $signType;
        $this->sandbox = $sandbox;
        $aopClient = new \AopClient();
        $aopClient->gatewayUrl = $this->sandbox ? $this->sandboxGatewayUrl : $this->gatewayUrl;
        $aopClient->appId = $this->appId;
        $aopClient->format = $this->format;
        $aopClient->postCharset = $this->charset;
        $aopClient->signType = $this->signType;
        $aopClient->alipayrsaPublicKey = $this->alipayPublicKey;
        $aopClient->rsaPrivateKey = $this->merchantPrivateKey;
        $this-> aopClient = $aopClient;
    }

    /**
     * 启用沙箱
     */
    public function enableSandbox()
    {
        $this->sandbox = true;
        $this->aopClient->gatewayUrl = $this->sandboxGatewayUrl;
    }

    /**
     * 关闭沙箱
     */
    public function disableSandbox()
    {
        $this->sandbox = false;
        $this->aopClient->gatewayUrl = $this->gatewayUrl;
    }

    /**
     * 获取用户授权链接
     * @param string $scope 接口权限值，目前只支持auth_user和auth_base两个值
     * @param string $redirectUrl 回调页面，是经过转义的url链接
     * @param string $state 商户自定义参数，用户授权后，重定向到redirect_uri时会原样回传给商户
     * @return array
     */
    public function userAuthUrl($scope, $redirectUrl, $state = null)
    {
        if (!in_array($scope, ['auth_user', 'auth_base', 'auth_ecard', 'auth_invoice_info', 'auth_puc_charge'])) {
            return $this->error('Scope有误');
        }
        if (!$redirectUrl) {
            return $this->error('授权回调地址不能为空');
        }
        $url = $this->sandbox ? $this->sandboxAuthUrl : $this->authUrl;
        $url = $url . '?app_id=' . $this->appId . '&scope=' . $scope . '&redirect_uri=' . urlencode($redirectUrl) . '&state=' . $state;
        return $this->success('获取成功', ['url' => $url]);
    }

    /**
     * 获取用户ID信息
     * @param string $authCode 授权码
     * @return array
     * @throws \Exception
     */
    public function getUserID($authCode)
    {
        if (!$authCode) {
            return $this->error('临时授权吗不能为空');
        }
        $request = new \AlipaySystemOauthTokenRequest();
        $request->setCode($authCode);
        $request->setGrantType('authorization_code');
        $response = $this->aopClient->execute($request);
        if ($response['code'] != '10000 ') {
            return $this->error(isset($response['sub_msg']) ? $response['sub_msg'] : $response['msg'], $response);
        }
        return $this->success('请求成功', $response);
    }

    /**
     * 组装业务参数
     * @param string $order 商户订单号
     * @param string $subject 商品的标题/交易标题/订单标题/订单关键字等。
     * @param int $amount 交易金额，单位：分
     * @param null $body 对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。
     * @param array $otherParams 其他参数，详见 https://docs.open.alipay.com/203/107090/
     * @param string $buyerID 买家ID
     */
    public function setPayBizContent($order, $subject, $amount, $body = null, $otherParams = [], $buyerID = null)
    {
        $bizContent = [
            'body' => $body,
            'subject' => $subject,
            'out_trade_no' => $order,
            'total_amount' => number_format($amount / 100, 2, '.', ''),
            'product_code' => 'QUICK_MSECURITY_PAY',
            'buyer_id' => $buyerID,
        ];
        if ($otherParams && count($otherParams)) {
            $bizContent = array_merge($bizContent, $otherParams);
        }
        $this->payBizContent = json_encode($bizContent, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 统一支付
     * @param $payWay
     * @return array
     */
    private function pay($payWay)
    {
        $request = $response = null;
        switch ($payWay) {
            case 'app':
                $request = new \AlipayTradeAppPayRequest();
                break;
            case 'wap':
                $request = new \AlipayTradeWapPayRequest();
                $request->setReturnUrl($this->returnUrl);
                break;
            case 'web':
                $request = new \AlipayTradePagePayRequest();
                $request->setReturnUrl($this->returnUrl);
                break;
            case 'bar':
                $request = new \AlipayTradePayRequest();
                break;
            case 'qrcode':
                $request = new \AlipayTradePrecreateRequest();
                break;
        }
        if (!$request) {
            return $this->error('支付方式错误');
        }
        $request->setNotifyUrl($this->notifyUrl);
        $request->setBizContent($this->payBizContent);

        try {
            switch ($payWay) {
                case 'app':
                    $response = $this->aopClient->sdkExecute($request);
                    break;
                case 'wap':
                    $response = $this->aopClient->pageExecute($request);
                    break;
                case 'web':
                    $response = $this->aopClient->pageExecute($request);
                    break;
                case 'bar':
                    $this->aopClient->apiVersion = "1.0";
                    $response = $this->aopClient->execute($request);
                    $response = (array)array_values((array)$response)[0];
                    break;
                case 'qrcode':
                    $this->aopClient->apiVersion = "1.0";
                    $response = $this->aopClient->execute($request);
                    $response = (array)array_values((array)$response)[0];
                    break;
            }
            if (in_array($payWay, ['bar', 'qrcode']) && $response['code'] != '10000 ') {
                return $this->error(isset($response['sub_msg']) ? $response['sub_msg'] : $response['msg'], $response);
            }
            return $this->success('请求成功', $response);
        } catch (\Exception $exception) {
            return $this->error('请求失败，原因：' . $exception->getMessage());
        }
    }

    /**
     * App支付
     * @return array
     */
    public function appPay()
    {
        return $this->pay('app');
    }

    /**
     * Wap 支付
     * @return array
     */
    public function wapPay()
    {
        return $this->pay('wap');
    }

    /**
     * Web 支付
     * @return array
     */
    public function webPay()
    {
        return $this->pay('web');
    }

    /**
     * 条形码 支付
     * @return array
     */
    public function barPay()
    {
        return $this->pay('bar');
    }

    /**
     * 二维码 支付
     * @return array
     */
    public function qrPay()
    {
        return $this->pay('qrcode');
    }

    /**
     * 订单查询
     * @param string $order 商户订单号
     * @param string $tradeOrder 支付宝交易号
     * @return array
     */
    public function query($order = null, $tradeOrder = null)
    {
        if (!$order && !$tradeOrder) {
            return $this->error('商户订单号和支付宝交易号需要至少传一个');
        }
        $bizContent = [
            'out_trade_no' => $order,
            'trade_no' => $tradeOrder
        ];
        $bizContent = json_encode($bizContent, JSON_UNESCAPED_UNICODE);
        $request = new \AlipayTradeQueryRequest();
        $request->setBizContent($bizContent);
        try {
            $response = $this->aopClient->execute($request);
            $response = (array)$response->alipay_trade_query_response;
            if ($response['code'] != '10000') {
                return $this->error(isset($response['sub_msg']) ? $response['sub_msg'] : $response['msg'], $response);
            }
            return $this->success('请求成功', $response);
        } catch (\Exception $exception) {
            return $this->error('请求失败，原因：' . $exception->getMessage());
        }
    }

    /**
     * 回调校验签名
     * @return bool
     */
    public function notifyVerify()
    {
        $result = $this->aopClient->rsaCheckV2($_POST, $this->alipayPublicKey, $this->signType);
        return $result;
    }

    /**
     * 回复通知
     * @param bool $success 是否成功
     * @param bool $die 是否结束脚本运行
     */
    public function notifyReply($success, $die = true)
    {
        echo $success ? 'success' : 'fail';
        $die && die;
    }
}

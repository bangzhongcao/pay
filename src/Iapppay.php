<?php

namespace Jormin\Pay;

use Jormin\Pay\Iapppay\PayTrait;

/**
 * Class Iapppay
 * @package Jormin\Pay
 */
class Iapppay extends BaseObject
{

    use PayTrait;

    /**
     * 应用编号
     * @var
     */
    public $appId;

    /**
     * 爱贝公钥
     * @var
     */
    public $iapppayPublicKey;

    /**
     * 商户私钥
     * @var
     */
    public $merchantPrivateKey;

    /**
     * 签名方式
     * @var string
     */
    public $signType;

    /**
     * 支付结果异步通知地址
     * @var string
     */
    public $notifyUrl;

    /**
     * 支付跳转地址
     * @var string
     */
    public $returnUrl;

    /**
     * 下单Url
     * @var string
     */
    private $orderUrl = 'https://cp.iapppay.com/payapi/order';

    /**
     * 订单查询Url
     * @var string
     */
    private $queryOrderUrl = 'https://cp.iapppay.com/payapi/queryresult';

    /**
     * H5支付Url
     * @var string
     */
    private $h5Url = 'https://web.iapppay.com/h5/gateway';

    /**
     * PC支付Url
     * @var string
     */
    private $pcUrl = 'https://web.iapppay.com/pc/gateway';

    /**
     * Iapppay constructor.
     * @param $appId
     * @param $iapppayPublicKey
     * @param $merchantPrivateKey
     * @param null $notifyUrl
     * @param null $returnUrl
     * @param string $signType
     */
    public function __construct($appId, $iapppayPublicKey, $merchantPrivateKey, $notifyUrl = null, $returnUrl = null, $signType = 'RSA')
    {
        $this->appId = $appId;
        $this->iapppayPublicKey = $this->formatPubKey($iapppayPublicKey);
        $this->merchantPrivateKey = $this->formatPriKey($merchantPrivateKey);
        $this->notifyUrl = $notifyUrl;
        $this->returnUrl = $returnUrl;
        $this->signType = $signType;
    }

    /**
     * 生成下单参数
     * @param $commonData
     * @return string
     */
    private function setUnifiedOrderContent($commonData)
    {
        $params = [
            'appid' => $this->appId,
            'waresid' => $commonData['waresid'],
            'cporderid' => $commonData['cporderid'],
            'price' => $commonData['price'],
            'currency' => 'RMB',
            'appuserid' => $commonData['appuserid'],
            'cpprivateinfo' => $commonData['cpprivateinfo'] ?? '',
            'waresname' => $commonData['waresname'] ?? '',
            'notifyurl' => $commonData['notifyurl']
        ];
        $requestData = $this->composeRequestData($params, $this->merchantPrivateKey);
        return $requestData;
    }

    /**
     * 下单支付
     * @param string $payway 支付方式
     * @param array $commonData 常规参数
     * @param array $paywayData 额外参数
     * @return array
     */
    public function order(string $payway, array $commonData, array $paywayData)
    {
        if (!$commonData['cporderid'] || !$commonData['price'] || !$commonData['appuserid'] || !$commonData['waresid']) {
            return $this->error('参数错误');
        }
        $params = $this->setUnifiedOrderContent($commonData);
        $response = $this->http('POST', $this->orderUrl, $params);
        if (!$response['success']) {
            return $response;
        }
        $transid = $response['data']['transid'];
        switch ($payway) {
            case 'js':
                $orderData = [
                    'tid' => $transid,
                    'app' => $this->appId,
                    'url_r' => $paywayData['url_r'],
                    'url_h' => $paywayData['url_h'],
                ];
                $requestData = $this->composeRequestData($orderData, $this->merchantPrivateKey, true);
                $gateway = $paywayData['h5'] ? $this->h5Url : $this->pcUrl;
                return $this->success('下单成功', $gateway . '?' . $requestData);
                break;
        }
        return $response;
    }

    /**
     * JS 支付
     * @param $commonData
     * @param $extraData
     * @return array
     */
    public function jsPay($commonData, $extraData)
    {
        return $this->order('js', $commonData, $extraData);
    }

    /**
     * 查询订单
     * @param $cporderid
     * @return array
     */
    public function queryOrder($cporderid)
    {
        if(!$cporderid){
            return $this->error('订单不能为空');
        }
        $params = [
            'appid' => $this->appId,
            'cporderid' => $cporderid
        ];
        $params = $this->composeRequestData($params, $this->merchantPrivateKey);
        $response = $this->http('POST', $this->queryOrderUrl, $params);
        return $response;
    }

    /**
     * 回调校验签名
     * @param string $transdata 校验数据
     * @param string $sign 校验签名
     * @return array
     */
    public function notifyVerify(string $transdata, string $sign)
    {
        $result = $this->verify($transdata, $sign, $this->iapppayPublicKey);
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
    public function notifyReply($success, $die = true)
    {
        echo $success ? 'SUCCESS' : 'FAIL';
        $die && die;
    }


}

<?php

namespace Jormin\Pay\YQBPay;

include_once dirname(__FILE__) . '/../../sdk/wechat_php_sdk_v3.0.9/lib/WxPay.Config.Interface.php';
include_once dirname(__FILE__) . '/../../sdk/wechat_php_sdk_v3.0.9/lib/WxPay.Api.php';

use Jormin\Pay\BaseObject;

/**
 * Class BasePay
 * @package Jormin\Aliyun
 */
class BasePay extends BaseObject
{

    /**
     * @var string 商户密钥
     */
    protected $merchantKey = '';

    /**
     * BasePay constructor.
     * @param string $merchantKey
     */
    public function __construct(string $merchantKey)
    {
        $this->merchantKey = $merchantKey;
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
     * 网络请求
     * @param $method
     * @param $url
     * @param $params
     * @return array
     */
    public function http($method, $url, $params)
    {
        if (strtoupper($method) == 'GET') {
            $args = [];
            foreach ($params as $key => $value) {
                $args[] = $key . '=' . $value;
            }
            $url .= '?' . implode('&', $args);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if (strtoupper($method) == 'POST') {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        $curlInfo = curl_getinfo($curl);
        curl_close($curl);
        if ($curlInfo['http_code'] === 302) {
            return $this->success('请求成功', ['url' => $curlInfo['redirect_url']]);
        } else {
            return $this->parseResponse(urldecode($data));
        }
    }

    /**
     * 解析response报文
     * @param string $content
     * @return array
     */
    public function parseResponse(string $content)
    {
        $arr = array_map(function ($v) {
            return explode("=", $v);
        }, explode('&', $content));
        $responseData = [];
        foreach ($arr as $value) {
            $responseData[($value[0])] = $value[1];
        }
        if ($responseData['respCode'] !== '0000') {
            return $this->error('请求失败，原因：' . $responseData['respMsg']);
        }
        //校验签名
        $result = $this->verify($responseData["signature"], $responseData);
        if (!$result) {
            return $this->error('壹钱包响应签名校验不通过');
        }
        return $this->success('请求成功', $responseData);
    }

    /**
     * 签名
     * @param array $params
     * @return string
     */
    public function signature(array $params)
    {
        unset($params['signMethod']);
        unset($params['signature']);
        ksort($params);
        $hashParamsStr = $this->buildSignatureParams($params) . $this->merchantKey;
        return hash('sha256', $hashParamsStr);
    }

    /**
     * 校验签名
     * @param string $signature
     * @param array $params
     * @return bool
     */
    public function verify(string $signature, array $params)
    {
        return $signature === $this->signature($params);
    }

    /**
     * 生成签名参数
     * @param array $params
     * @return string
     */
    private function buildSignatureParams(array $params)
    {
        $signatureParams = [];
        foreach ($params as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            $signatureParams[] = $key . '=' . (is_array($value) ? '['.$this->buildSignatureParams($value).']' : $value);
        }
        return implode('&', $signatureParams);
    }

}

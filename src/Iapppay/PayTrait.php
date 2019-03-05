<?php
/**
 * Created by PhpStorm.
 * User: Jormin
 * Date: 2019-03-05
 * Time: 10:34
 */

namespace Jormin\Pay\Iapppay;


/**
 * Trait PayTrait
 * @package Jormin\Pay\Iapppay
 */
trait PayTrait
{
    /**
     * 格式化公钥
     * @param string $pubKey PKCS#1格式的公钥串
     * @return string pem格式公钥，可以保存为.pem文件
     */
    public function formatPubKey($pubKey)
    {
        $fKey = "-----BEGIN PUBLIC KEY-----\n";
        $len = strlen($pubKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($pubKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END PUBLIC KEY-----";
        return $fKey;
    }

    /**
     * 格式化私钥
     * @param string $priKey PKCS#1格式的私钥串
     * @return string pem格式私钥， 可以保存为.pem文件
     */
    public function formatPriKey($priKey)
    {
        $fKey = "-----BEGIN RSA PRIVATE KEY-----\n";
        $len = strlen($priKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($priKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END RSA PRIVATE KEY-----";
        return $fKey;
    }

    /**
     * RSA签名,签名用商户私钥,使用MD5摘要算法,最后的签名，需要用base64编码
     * @param string $data 待签名数据
     * @param $priKey
     * @return string Sign签名
     */
    public function sign($data, $priKey)
    {
        //转换为openssl密钥
        $res = openssl_get_privatekey($priKey);
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);
        //释放资源
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * RSA验签,验签用爱贝公钥，摘要算法为MD5
     * @param string $data 待签名数据
     * @param string $sign 需要验签的签名
     * @param string $pubKey 爱贝公钥
     * @return bool 验签是否通过
     */
    public function verify($data, $sign, $pubKey)
    {
        //转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);
        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_MD5);
        //释放资源
        openssl_free_key($res);
        //返回资源是否成功
        return $result;
    }

    /**
     * 解析response报文
     * @param string $content 收到的response报文
     * @param string $pkey 爱贝平台公钥，用于验签
     * @return array
     */
    public function parseResponse($content, $pkey)
    {
        $arr = array_map(function ($v) {
            return explode("=", $v);
        }, explode('&', $content));
        $responseArr = [];
        foreach ($arr as $value) {
            $responseArr[($value[0])] = $value[1];
        }
        //解析transdata
        if (!array_key_exists("transdata", $responseArr)) {
            return $this->error('爱贝响应异常');
        }
        $responseData = json_decode($responseArr["transdata"], true);
        if (array_key_exists("errmsg", $responseData)) {
            return $this->error('请求失败，原因：' . $responseData['errmsg']);
        }
        //验证签名，失败应答报文没有sign，跳过验签
        if (array_key_exists("sign", $responseArr)) {
            //校验签名
            $pkey = $this->formatPubKey($pkey);
            $result = $this->verify($responseArr["transdata"], $responseArr["sign"], $pkey);
            if (!$result) {
                return $this->error('爱贝响应签名校验不通过');
            }
        }
        return $this->success('请求成功', $responseData);
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
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        $response = $this->parseResponse(urldecode($data), $this->iapppayPublicKey);
        return $response;
    }

    /**
     * 组装请求数据
     * @param $params
     * @param $privateKey
     * @param bool $isH5
     * @return string
     */
    public function composeRequestData($params, $privateKey, $isH5 = false)
    {
        //获取待签名字符串
        $content = json_encode($params);
        //格式化key，建议将格式化后的key保存，直接调用
        $vkey = $this->formatPriKey($privateKey);
        //生成签名
        $sign = $this->sign($content, $vkey);
        //组装请求报文，目前签名方式只支持RSA这一种
        $requestData = ($isH5 ? 'data' : 'transdata') . "=" . urlencode($content) . "&sign=" . urlencode($sign) . "&signtype=RSA";
        return $requestData;
    }
}
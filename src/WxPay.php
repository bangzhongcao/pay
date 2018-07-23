<?php

namespace Jormin\Pay;

include_once dirname(__FILE__) . '/../sdk/wechat_php_sdk_v3.0.9/lib/WxPay.Config.Interface.php';
include_once dirname(__FILE__) . '/../sdk/wechat_php_sdk_v3.0.9/lib/WxPay.Api.php';

use Jormin\Pay\WxPay\AppPay;
use Jormin\Pay\WxPay\BasePay;
use Jormin\Pay\WxPay\JsApiPay;
use Jormin\Pay\WxPay\PayConfig;

/**
 * Class WxPay
 * @package Jormin\Aliyun
 */
class WxPay extends BaseObject {

    /**
     * 微信支付配置
     *
     * @var PayConfig
     */
    protected $payConfig;

    /**
     * 微信统一下单输入
     *
     * @var \WxPayUnifiedOrder
     */
    protected $unifiedOrderInpiut;

    public function __construct($appID, $appSecret, $merchantID, $key, $notifyUrl, $returnUrl=null, $signType='MD5')
    {
        $this->payConfig = new PayConfig($appID, $appSecret, $merchantID, $key, $notifyUrl, $returnUrl=null, $signType);
    }

    /**
     * 生成业务参数
     *
     * @param $order
     * @param $body
     * @param $amount
     * @param array $otherParams
     */
    public function setUnifiedOrderContent($order, $body, $amount, $otherParams=[]){
        $this->unifiedOrderInpiut = BasePay::makeUnifiedOrderContent($order, $body, $amount, $otherParams);
    }

    /**
     * 统一支付下单
     *
     * @param $payWay
     * @return array
     */
    private function pay($payWay){
        $wxPay = $request = $response = null;
        switch ($payWay){
            case 'js':
                $wxPay = new JsApiPay($this->payConfig);
                $openId = $wxPay->GetOpenid();
                $this->unifiedOrderInpiut->SetTrade_type("JSAPI");
                $this->unifiedOrderInpiut->SetOpenid($openId);
                break;
            case 'app':
                $this->unifiedOrderInpiut->SetTrade_type("APP");
                $wxPay = new AppPay($this->payConfig);
                break;
        }
        try{
            $unifiedOrder = \WxPayApi::unifiedOrder($this->payConfig, $this->unifiedOrderInpiut);
            switch ($payWay){
                case 'js':
                    $response = $wxPay->GetJsApiParameters($unifiedOrder);
                    break;
                case 'app':
                    $response = $wxPay->GetAppParameters($unifiedOrder);
                    break;
                default:
                    $response = [$unifiedOrder];
                    break;
            }
            return $this->success('请求成功', $response);
        }catch(\Exception $e){
            return $this->error('统一下单失败，失败原因：'.$e->getMessage());
        }
    }

    /**
     * App支付
     *
     * @return array
     */
    public function appPay(){
        return $this->pay('app');
    }

    /**
     * js 支付
     *
     * @return array
     */
    public function jsPay(){
        return $this->pay('js');
    }
    /**
     * 二维码 支付
     *
     * @return array
     */
    public function qrPay(){
        return $this->pay('qrcode');
    }

    /**
     * 查询订单
     *
     * @param $transaction_id
     * @return array
     */
    public function queryorder($transaction_id)
    {
        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = \WxPayApi::orderQuery($this->payConfig, $input);
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return $this->success('查询成功', $result);
        }
        return $this->error('查询失败', $result);
    }

    /**
     * 回调校验签名
     *
     * @param bool $query 是否通过微信接口查询订单来判断订单真实性
     * @return array
     */
    public function notifyVerify($query=false)
    {
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $objData = \WxPayNotifyResults::Init($this->payConfig, $xml);
        $data = $objData->GetValues();
        $responseData = ['origin'=>$xml, 'convert'=>$data];
        if(!array_key_exists("return_code", $data)
            ||(array_key_exists("return_code", $data) && $data['return_code'] != "SUCCESS")) {
            return $this->error('通信异常', $responseData);
        }
        if(!array_key_exists("transaction_id", $data) || !array_key_exists("out_trade_no", $data)){
            return $this->error('回调参数异常', $responseData);
        }

        try {
            $checkResult = $objData->CheckSign($this->payConfig);
            if($checkResult == false){
                return $this->error('签名校验失败', $responseData);
            }
        } catch(\Exception $e) {
            return $this->error('签名校验失败', $responseData);
        }
        //查询订单，判断订单真实性
        if($query){
            $result = $this->Queryorder($data["transaction_id"]);
            if(!$result['success']){
                $this->error('订单查询失败', $responseData);
            }
        }
        return $this->success('订单异步校验通过', $responseData);
    }

    /**
     * 回复通知
     *
     * @param $success
     * @param $msg
     * @param bool $die
     */
    private function notifyReply($success, $msg, $die=true){
        $wxPayReply = new \WxPayNotify();
        $wxPayReply->SetReturn_code($success ? 'SUCCESS' : 'FAIL');
        $wxPayReply->SetReturn_msg($success ? 'OK' : $msg);
        $xml = $wxPayReply->ToXml();
        echo $xml;
        $die && die;
    }
}

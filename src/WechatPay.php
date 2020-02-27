<?php

namespace Jormin\Pay;

include_once dirname(__FILE__) . '/../sdk/wechat_php_sdk_v3.0.9/lib/WxPay.Config.Interface.php';
include_once dirname(__FILE__) . '/../sdk/wechat_php_sdk_v3.0.9/lib/WxPay.Api.php';
include_once dirname(__FILE__) . '/../sdk/wechat_php_sdk_v3.0.9/lib/WxPay.Notify.php';
include_once dirname(__FILE__) . '/../sdk/wechat_php_sdk_v3.0.9/lib/WxPay.Data.php';

use Jormin\Pay\WechatPay\AppPay;
use Jormin\Pay\WechatPay\BasePay;
use Jormin\Pay\WechatPay\H5Pay;
use Jormin\Pay\WechatPay\JsApiPay;
use Jormin\Pay\WechatPay\PayConfig;

/**
 * Class WxPay
 * @package Jormin\Pay
 */
class WechatPay extends BaseObject
{

    /**
     * @var PayConfig 微信支付配置
     */
    protected $payConfig;

    /**
     * @var \WxPayUnifiedOrder 微信统一下单输入
     */
    protected $unifiedOrderInpiut;

    /**
     * WxPay constructor.
     * @param string $appId AppId
     * @param string $appSecret AppSecret
     * @param string $merchantID 商户ID
     * @param string $key 支付密钥
     * @param string $notifyUrl 异步通知地址
     * @param string $returnUrl 前端跳转地址
     * @param string $signType 签名方式
     * @param string $sslCertPath 证书
     * @param string $sslKeyPath 证书
     */
    public function __construct($appId, $appSecret, $merchantID, $key, $notifyUrl, $returnUrl = null, $signType = 'MD5', $sslCertPath = '', $sslKeyPath = '')
    {
        $this->payConfig = new PayConfig($appId, $appSecret, $merchantID, $key, $notifyUrl, $returnUrl, $signType, $sslCertPath, $sslKeyPath);
    }

    /**
     * 生成业务参数
     * @param string $order 订单号
     * @param string $body 订单说明
     * @param int $amount 支付金额，单位：分
     * @param array $otherParams 其他参数
     */
    public function setUnifiedOrderContent($order, $body, $amount, $otherParams = [])
    {
        $this->unifiedOrderInpiut = BasePay::makeUnifiedOrderContent($order, $body, $amount, $otherParams);
    }

    /**
     * 统一支付下单
     * @param $payWay
     * @return array
     */
    private function pay($payWay)
    {
        $wxPay = $request = $response = null;
        switch ($payWay) {
            case 'js':
                $wxPay = new JsApiPay($this->payConfig);
                if (!$this->unifiedOrderInpiut->IsOpenidSet()) {
                    $openId = $wxPay->GetOpenid();
                    $this->unifiedOrderInpiut->SetOpenid($openId);
                }
                $this->unifiedOrderInpiut->SetTrade_type("JSAPI");
                break;
            case 'app':
                $this->unifiedOrderInpiut->SetTrade_type("APP");
                $wxPay = new AppPay($this->payConfig);
                break;
            case 'h5':
                $this->unifiedOrderInpiut->SetTrade_type("MWEB");
                break;
        }
        try {
            $unifiedOrder = \WxPayApi::unifiedOrder($this->payConfig, $this->unifiedOrderInpiut);
            if ($unifiedOrder['return_code'] === 'FAIL') {
                return $this->error('统一下单失败，失败原因：' . $unifiedOrder['return_msg'], $unifiedOrder);
            }
            if ($unifiedOrder['result_code'] === 'FAIL') {
                return $this->error('统一下单失败，失败原因：' . $unifiedOrder['err_code_des'], $unifiedOrder);
            }
            switch ($payWay) {
                case 'js':
                    $response = $wxPay->GetJsApiParameters($unifiedOrder);
                    break;
                case 'app':
                    $response = $wxPay->GetAppParameters($unifiedOrder);
                    break;
                case 'h5':
                    return $this->success('统一下单成功', $unifiedOrder);
                    break;
                default:
                    $response = [$unifiedOrder];
                    break;
            }
            return $this->success('统一下单成功', $response);
        } catch (\Exception $e) {
            return $this->error('统一下单失败，失败原因：' . $e->getMessage());
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
     * js 支付
     * @return array
     */
    public function jsPay()
    {
        return $this->pay('js');
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
     * H5 支付
     * @return array
     */
    public function h5Pay()
    {
        return $this->pay('h5');
    }

    /**
     * 查询订单
     * @param string $transaction_id 微信订单号
     * @return array
     * @throws \WxPayException
     */
    public function queryorder($transaction_id)
    {
        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = \WxPayApi::orderQuery($this->payConfig, $input);
        if (array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS") {
            return $this->success('查询成功', $result);
        }
        return $this->error('查询失败', $result);
    }

    /**
     * 回调校验签名
     * @param bool $query 是否通过微信接口查询订单来判断订单真实性
     * @return array
     * @throws \WxPayException
     */
    public function notifyVerify($query = false)
    {
        $xml = file_get_contents("php://input");
        $objData = \WxPayNotifyResults::Init($this->payConfig, $xml);
        $data = $objData->GetValues();
        $responseData = ['origin' => $xml, 'convert' => $data];
        if (!array_key_exists("return_code", $data)
            || (array_key_exists("return_code", $data) && $data['return_code'] != "SUCCESS")) {
            return $this->error('通信异常', $responseData);
        }
        if (!array_key_exists("transaction_id", $data) || !array_key_exists("out_trade_no", $data)) {
            return $this->error('回调参数异常', $responseData);
        }

        try {
            $checkResult = $objData->CheckSign($this->payConfig);
            if ($checkResult == false) {
                return $this->error('签名校验失败', $responseData);
            }
        } catch (\Exception $e) {
            return $this->error('签名校验失败', $responseData);
        }
        //查询订单，判断订单真实性
        if ($query) {
            $result = $this->Queryorder($data["transaction_id"]);
            if (!$result['success']) {
                return $this->error('订单查询失败', $responseData);
            }
        }
        return $this->success('订单异步校验通过', $responseData);
    }

    /**
     * 回复通知
     * @param bool $success 是否成功
     * @param string $msg 回复消息
     * @param bool $die 是否结束脚本运行
     * @throws \WxPayException
     */
    public function notifyReply($success, $msg, $die = true)
    {
        $wxPayReply = new \WxPayNotify();
        $wxPayReply->SetReturn_code($success ? 'SUCCESS' : 'FAIL');
        $wxPayReply->SetReturn_msg($success ? 'OK' : $msg);
        $xml = $wxPayReply->ToXml();
        echo $xml;
        $die && die;
    }

    /**
     * 获取账单数据
     * @param string $billDate 账单日期
     * @param string $billType 账单类型，ALL（默认值），返回当日所有订单信息（不含充值退款订单）；SUCCESS，返回当日成功支付的订单（不含充值退款订单）；REFUND，返回当日退款订单（不含充值退款订单）；RECHARGE_REFUND，返回当日充值退款订单
     * @return array
     */
    public function getBills($billDate, $billType)
    {
        $input = new \WxPayDownloadBill();
        $input->SetBill_date($billDate);
        $input->SetBill_type($billType);
        try {
            $data = \WxPayApi::downloadBill($this->payConfig, $input, 500);
            return $this->success('获取账单成功', $data);
        } catch (\WxPayException $exception) {
            return $this->error('下载账单出错：' . $exception->getMessage());
        }
    }

    /**
     * 退款
     * @param $transactionId
     * @param $outTradeNo
     * @param $outRefundNo
     * @param $totalFee
     * @param $refundFee
     * @param string $refundDesc
     * @param string $notifyUrl
     * @return array
     */
    public function refund($transactionId, $outTradeNo, $outRefundNo, $totalFee, $refundFee, $refundDesc = '', $notifyUrl = '')
    {
        try {
            $input = new \WxPayRefund();
            if ($transactionId) {
                $input->SetTransaction_id($transactionId);
            } else {
                $input->SetOut_trade_no($outTradeNo);
            }
            $input->SetTotal_fee($totalFee);
            $input->SetRefund_fee($refundFee);
            $input->SetOut_refund_no($outRefundNo);
            $input->SetRefund_desc($refundDesc);
            $input->SetNotify_url($notifyUrl);
            $refundData = \WxPayApi::refund($this->payConfig, $input);
            return $this->success('退款请求提交成功', $refundData);
        } catch (\Exception $exception) {
            return $this->error('退款失败,' . $exception->getMessage());
        }
    }

    /**
     * 退款查询
     * @param $outRefundNo
     * @param null $refundId
     * @param null $transactionId
     * @param null $outTradeNo
     * @return array
     */
    public function refundQuery($outRefundNo, $refundId = null, $transactionId = null, $outTradeNo = null)
    {
        try {
            $input = new \WxPayRefundQuery();
            if ($refundId) {
                $input->SetRefund_id($refundId);
            }
            if ($outRefundNo) {
                $input->SetOut_refund_no($outRefundNo);
            }
            if ($transactionId) {
                $input->SetTransaction_id($transactionId);
            }
            if ($outTradeNo) {
                $input->SetOut_trade_no($outTradeNo);
            }
            $refundData = \WxPayApi::refundQuery($this->payConfig, $input);
            return $this->success('退款查询成功', $refundData);
        } catch (\Exception $exception) {
            return $this->error('退款查询失败,' . $exception->getMessage());
        }
    }
}

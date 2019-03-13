微信支付文档

### 使用

1. 生成微信支付对象

    ``` php
    /**
     * WxPay constructor.
     * @param string $appId AppId
     * @param string $appSecret AppSecret
     * @param string $merchantID 商户ID
     * @param string $key 支付密钥
     * @param string $notifyUrl 异步通知地址
     * @param string $returnUrl 前端跳转地址
     * @param string $signType 签名方式
     */
    $wechatpay = new WechatPay($appId, $appSecret, $merchantID, $key, $notifyUrl, $returnUrl = null, $signType = 'MD5')
    ```

2. 生成业务参数

    ```
    /**
     * 生成业务参数
     * @param string $order 订单好
     * @param string $body 订单说明
     * @param int $amount 支付金额，单位：分
     * @param array $otherParams 其他参数
     */
    $wechatpay->setUnifiedOrderContent($order, $body, $amount, $otherParams = [])
    ```
    
    当前支持的其他参数字段:
    
    参数 | 说明
    --- | ---
    detail | 商品详细描述，对于使用单品优惠的商户，改字段必须按照规范上传
    attach | 附加数据，在查询API和支付通知中原样返回，可作为自定义参数使用。
    time_start | 订单生成时间，格式为yyyyMMddHHmmss
    timeExpire | 订单失效时间，格式为yyyyMMddHHmmss
    goodsTag | 订单优惠标记，使用代金券或立减优惠功能时需要的参数

3. 支付

    ```
    // App支付
    $wechatpay->appPay()
    // Js支付
    $wechatpay->jsPay()
    // 二维码支付
    $wechatpay->qrPay()
    ```

4. 订单查询

    ```
    /**
     * 查询订单
     * @param string $transaction_id 微信订单号
     * @return array
     * @throws \WxPayException
     */
    $wechatpay->queryorder($transaction_id)
    ```

5. 异步通知签名校验

    ```
    /**
     * 回调校验签名
     * @param bool $query 是否通过微信接口查询订单来判断订单真实性
     * @return array
     * @throws \WxPayException
     */
    $wechatpay->notifyVerify($query = false)
    ```

6. 回复通知

    ```
    /**
     * 回复通知
     * @param bool $success 是否成功
     * @param string $msg 回复消息
     * @param bool $die 是否结束脚本运行
     * @throws \WxPayException
     */
    $wechatpay->notifyReply($success, $msg, $die = true)
    ```
支付宝文档

### 使用

1. 生成支付宝对象

    ``` php
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
    $alipay = new Alipay($appId, $alipayPublicKey, $merchantPrivateKey, $notifyUrl, $returnUrl = null, $charset = 'UTF-8', $signType = 'RSA2', $sandbox = false)
    ```

2. 沙箱模式

    ```php
    // 启用沙箱模式
    $alipay->enableSandbox();
    // 关闭沙箱模式
    disableSandbox
    ```

3. 获取用户授权链接

    ```
    /**
     * 获取用户授权链接
     * @param string $scope 接口权限值，目前只支持auth_user和auth_base两个值
     * @param string $redirectUrl 回调页面，是经过转义的url链接
     * @param string $state 商户自定义参数，用户授权后，重定向到redirect_uri时会原样回传给商户
     * @return array
     */
    $alipay->userAuthUrl($scope, $redirectUrl, $state = null)
    ```

4. 获取用户ID信息

    ```
    /**
     * 获取用户ID信息
     * @param string $authCode 授权码
     * @return array
     * @throws \Exception
     */
    $alipay->getUserID($authCode)
    ```

5. 统一下单参数信息

    ```
    /**
     * 组装业务参数
     * @param string $order 商户订单号
     * @param string $subject 商品的标题/交易标题/订单标题/订单关键字等。
     * @param int $amount 交易金额，单位：分
     * @param null $body 对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。
     * @param array $otherParams 其他参数，详见 https://docs.open.alipay.com/203/107090/
     * @param string $buyerID 买家ID
     */
    $alipay->setPayBizContent($order, $subject, $amount, $body = null, $otherParams = [], $buyerID = null)
    ```

6. 支付

    ```
    // App支付
    $alipay->appPay()
    // Wap支付
    $alipay->wapPay()
    // Web支付
    $alipay->webPay()
    // 条形码支付
    $alipay->barPay()
    // 二维码支付
    $alipay->qrPay()
    ```

7. 订单查询

    ```
    /**
     * 订单查询
     * @param string $order 商户订单号
     * @param string $tradeOrder 支付宝交易号
     * @return array
     */
    $alipay->query($order = null, $tradeOrder = null)
    ```

8. 异步通知签名校验

    ```
    /**
     * 回调校验签名
     * @return bool
     */
    $alipay->notifyVerify()
    ```

9. 回复通知

    ```
    /**
     * 回复通知
     * @param bool $success 是否成功
     * @param bool $die 是否结束脚本运行
     */
    $alipay->notifyReply($success, $die = true)
    ```
    
 10. 获取账单下载地址
 
    ```
    /**
     * 获取账单数据下载地址
     * @param string $billDate 账单日期
     * @param string $billType 账单类型，trade、signcustomer；trade指商户基于支付宝交易收单的业务账单；signcustomer是指基于商户支付宝余额收入及支出等资金变动的帐务账单。
     * @return array
     */
    $alipay->getBillUrl($billDate, $billType)
    ```
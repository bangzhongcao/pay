平安支付文档

### 使用

1. 生成平安支付对象

    ``` php
    /**
     * YQBPay constructor.
     * @param string $merchantId 商户号
     * @param string $merchantKey 商户密钥
     * @param string $notifyUrl 支付结果异步通知地址
     * @param string $redirectUrl 前台页面跳转地址
     * @param string $failbackUrl 失败跳转地址
     */
    $yqbpay = new YQBPay(string $merchantId, string $merchantKey, string $notifyUrl, string $redirectUrl, string $failbackUrl);
    ```

2. 检测是否测试环境

    ```php
    /**
     * 检测是否测试环境
     * @return bool
     */
   $yqbpay->isSandbox();
    ```
    
3. 设置测试环境

    ```php
    /**
     * 设置测试环境
     * @param bool $sandbox
     */
   $yqbpay->setSandbox();
    ```
    
4. 微信聚合收银台落单

    ```
    /**
     * 微信聚合收银台落单
     * @param string $order 订单号
     * @param int $amount 订单金额
     * @param null $body 订单说明
     * @param array $otherParams 其他数据
     * @param array $merReserved 商户保留域
     * @param array $riskInfo 风控信息
     * @return array
     */
    $yqbpay->wechatCashierRevOrder(string $order, int $amount, $body = null, $otherParams = [], $merReserved = [], $riskInfo = []);
    ```

5. 微信聚合收银台005通用交易查询

    ```
    /**
     * 微信聚合收银台005通用交易查询
     * @param string $order 订单号
     * @param array $otherParams 其他数据
     * @return array
     */
    $yqbpay->wechatCashierQuery(string $order, $otherParams = [])；
    ```

6. 回调校验签名

    ```
    /**
     * 回调校验签名
     * @param array $params 通知参数
     * @return array
     */
    $yqbpay->wechatCashierNotifyVerify(array $params);
    ```

7. 回复通知

    ```
    /**
     * 回复通知
     * @param bool $success 是否成功
     * @param bool $die 是否结束脚本运行
     */
    $yqbpay->wechatCashierNotifyReply($success, $die = true);
    ```
    


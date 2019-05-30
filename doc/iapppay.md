爱贝支付文档

### 使用

1. 生成爱贝支付对象

    ``` php
    $appId = 'your iapppay app id';
    $iapppayPublicKey = 'iapppay rsa public key';
    $merchantPrivateKey = 'your rsa private key';
    $iapppay = new \Jormin\Pay\Iapppay($appId, $iapppayPublicKey, $merchantPrivateKey);
    ```

2. js 支付

    ```php
    $commonData = [
        'cporderid' => '2019097868686823', // 商户唯一订单号
        'price' => 0.01, // 支付金额
        'appuserid' => '1', // 商户平台用户ID
        'waresid' => 1, // 商品ID
        'waresname' => '测试', // 商品名称
        'cpprivateinfo' => '测试', // 商户预传信息，爱贝原样返回
        'notifyurl' => 'http://www.baidu.com' // 商户异步通知地址
    ];
    
    $extraData = [
        'url_h' => 'http://www.baidu.com', // 用户放弃支付后网页端跳转地址，最大长度512
        'url_r' => 'http://www.baidu.com', // 用户支付完成后的网页端跳转地址。带有支付结果通知数据，最大长度512
        'h5' => false // 是否H5支付
    ];
 
    $iapppay->jsPay($commonData, $extraData);
    ```

3. 订单查询

    ```php
    $iapppay->queryOrder('20190305089799986');
    ```

4. 异步通知校验签名

    ```php
    $transdata = ''; // 异步通知带过来的参数
    $sign = ''; // 异步通知带过来的签名
    $iapppay->notifyVerify($transdata, $sign)
    ```
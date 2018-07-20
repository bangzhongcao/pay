短信服务文档

### 使用

1. 生成短信对象

    ``` php
    $accessKeyId = 'your access key id';
    $accessKeySecret = 'your access key secret';
    $sms = new \Jormin\Aliyun\Sms($accessKeyId, $accessKeySecret);
    ```

2. 发送单条短信

    ```php
    $phone = '手机号';
    $signature = '短信签名';
    $templateCode = '短信模板编码';
    // 短信模板参数数组
    $extData = [];
    $sms->sendSms($phone,$signature, $templateCode, $extData);
    ```

3. 批量发送短信

    ```php
    // 手机号数组
    $phones = [];
    // 短信签名数组
    $signatures = [];
    $templateCode = '短信模板编码';
    // 短信模板参数二维数组
    $extDatas = [];
    $sms->sendBatchSms($phones,$signatures, $templateCode, $extDatas);
    ```

3. 短信查询

    ```php
    $phone = '手机号';
    $sendDate = '发送日期，例如20180710';
    // 页码，默认1
    $page = 1;
    // 每页条数，默认10
    $pageSize = 10;
    $bizId = '发送流水号';
    $sms->querySendDetails($phone, $sendDate, $page, $pageSize, $bizId);
    ```
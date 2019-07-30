集成第三方支付的扩展包

## 安装

``` bash
$ composer require jormin/pay -vvv
```

## 通用响应

| 参数  | 类型  | 是否必须  | 描述  |
| ------------ | ------------ | ------------ | ------------ |
| success | bool | 是 | false：操作失败 true:操作成功 |
| message | string | 是 | 结果说明 |
| data | array | 否 | 返回数据 |


## 功能文档

- [支付宝](doc/alipay.md)

- [微信支付](doc/wechatpay.md)

- [爱贝支付](doc/iapppay.md)

- [平安支付(壹钱包)](doc/pingan_yqb.md)

## 参考文档

1. [蚂蚁金服开放平台文档中心](https://docs.open.alipay.com/200/)

2. [微信支付文档中心](https://pay.weixin.qq.com/wiki/doc/api/index.html)

3. [爱贝支付文档中心](https://www.iapppay.com/portal/gintroduction)

4. [平安支付(壹钱包)文档中心](http://test-open.stg.yqb.com/moap/business/tecDoc;jsessionid=3B77DCFD6A8A68212BAB4D223B7FC920)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

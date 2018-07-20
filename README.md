集成支付宝和微信支付的扩展包

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

- [微信](doc/wechatpay.md)

## 参考文档

1. [蚂蚁金服开放平台文档中心](https://docs.open.alipay.com/200/)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

移动推送文档

### 使用

1. 生成推送对象

    ``` php
    $accessKeyId = 'your access key id';
    $accessKeySecret = 'your access key secret';
    $androidAppKey = 'your android app key';
    $iosAppKey = 'your ios app key';
    $push = new \Jormin\Aliyun\Push($accessKeyId, $accessKeySecret, $androidAppKey, $iosAppKey);
    ```

2. 推送

    ```php
    $push->push($platform, $target, $targetValue, $pushType, $title, $body, $extData=[], $commonConfig = [], $androidConfig=[], $iosConfig=[]);
    ```

3. 接口参数说明

    - $platform: 推送平台 可选值: `\Jormin\Aliyun\Push::$PLATFORM_ANDROID` 和 `\Jormin\Aliyun\Push::$PLATFORM_IOS`
    - $target: 推送目标 可选值: `\Jormin\Aliyun\Push::$TARGET_DEVICE（根据设备推送）`、`\Jormin\Aliyun\Push::$TARGET_ACCOUNT（根据账号推送）`、`\Jormin\Aliyun\Push::$TARGET_ALIAS（根据别名推送）`、`\Jormin\Aliyun\Push::$TARGET_TAG（根据标签推送）`、`\Jormin\Aliyun\Push::$TARGET_ALL(推送给全部设备(同一种DeviceType的两次全推的间隔至少为1秒))`
    - $targetValue: Target值,如果 `targer` 值为 `ALL`，则 `targetValue` 值也为 `ALL`
    - $pushType: 推送类型 可选值: `\Jormin\Aliyun\Push::$PUSHTYPE_MESSAGE（消息）` 和 `\Jormin\Aliyun\Push::$PUSHTYPE_NOTICE（通知）`
    - title: 标题
    - body: 内容
    - extData: 额外数据，数组格式
    - commonConfig: 通用额外配置，数组格式，详细说明见下文
    - androidConfig: Android额外数据，数组格式，详细说明见下文
    - iosConfig: IOS额外数据，数组格式，详细说明见下文

### 额外配置

1. 通用额外配置参数

| 名称  | 类型  | 必须  | 描述  |
| ------------ | ------------ | ------------ | ------------ |
| PushTime | string | 否 | 用于定时发送。不设置缺省是立即发送。时间格式按照ISO8601标准表示，并需要使用UTC时间，格式为YYYY-MM-DDThh:mm:ssZ。 |
| StoreOffline | bool | 否 | 离线消息/通知是否保存。若保存，在推送时候用户不在线，在过期时间（ExpireTime）内用户上线时会被再次发送。StoreOffline默认设置为false，ExpireTime默认为72小时。（iOS通知走Apns链路，不受StoreOffline影响） |
| ExpireTime | string | 否 | 离线消息/通知的过期时间，和StoreOffline配合使用，过期则不会再被发送，最长保存72小时。默认为72小时。时间格式按照ISO8601标准表示，并需要使用UTC时间，格式为YYYY-MM-DDThh:mm:ssZ，过期时间不能小于当前时间或者定时发送时间加上3秒（ExpireTime > PushTime + 3秒），3秒是为了冗余网络和系统延迟造成的误差。 |

2. 安卓额外配置参数

| 名称  | 类型  | 必须  | 描述  |
| ------------ | ------------ | ------------ | ------------ |
| AndroidMusic | string | 否 | Android通知声音（保留参数，当前暂不起作用） |
| AndroidOpenType | string | 否 | 点击通知后动作 `APPLICATION`：打开应用 默认值 `ACTIVITY`：打开应用AndroidActivity `URL`：打开URL `NONE`：无跳转 |
| AndroidNotifyType | string | 否 | 通知的提醒方式 `VIBRATE`：振动 默认值 `SOUND`：声音 `BOTH`：声音和振动 `NONE`：静音 |
| AndroidActivity | string | 否 | 设定通知打开的activity，仅当AndroidOpenType="Activity"有效，如：com.alibaba.cloudpushdemo.bizactivity |
| AndroidOpenUrl | string | 否 | Android收到推送后打开对应的url,仅当AndroidOpenType="URL"有效 |
| AndroidNotificationBarType | integer | 否 | Android自定义通知栏样式，取值：1-100 |
| AndroidNotificationBarPriority | integer | 否 | Android通知在通知栏展示时排列位置的优先级 -2 -1 0 1 2 |
| AndroidNotificationChannel | string | 否 | 设置NotificationChannel参数，具体用途请参考常见问题：[Android 8.0以上设备通知接收不到](https://help.aliyun.com/document_detail/67398.html?spm=a2c4g.11186623.2.7.w7Bo1d) |
| AndroidRemind | bool | 否 | 推送类型为消息时设备不在线，则这条推送会使用辅助弹窗功能。默认值为False，仅当PushType=MESSAGE时生效。 |
| AndroidPopupActivity | string | 否 | 	此处指定通知点击后跳转的Activity。注：原AndroidXiaoMiActivity参数已废弃，所有第三方辅助弹窗都由新参数统一支持。 |
| AndroidPopupTitle | string | 否 | 辅助弹窗模式下Title内容,长度限制:<16字符（中英文都以一个字符计算）。注：原AndroidXiaoMiNotifyTitle参数已废弃，所有第三方辅助弹窗都由新参数统一支持。 |
| AndroidPopupBody | string | 否 | 辅助弹窗模式下Body内容,长度限制:<128字符（中英文都以一个字符计算）。注：原AndroidXiaoMiNotifyBody参数已废弃，所有第三方辅助弹窗都由新参数统一支持。 |
        
3. IOS额外配置参数

| 名称  | 类型  | 必须  | 描述  |
| ------------ | ------------ | ------------ | ------------ |
| iOSMusic | string | 否 | iOS通知声音，指定存放在app bundle或沙盒Library/Sounds目录下的音频文件名，参考：[iOS推送如何设定通知声音](https://help.aliyun.com/document_detail/48906.html?spm=a2c4g.11186623.2.5.w7Bo1d)，（若指定为空串（””），通知为静音；若不设置，默认填充default为系统提示音） |
| iOSBadge | integer | 否 | iOS应用图标右上角角标。注意，若iOSBadgeAutoIncrement设置为True，则此项必须为空。 |
| iOSBadgeAutoIncrement | bool | 否 | 是否开启角标自增功能，默认为False，当该项为True时，iOSBadge必须为为空。角标自增功能由推送服务端维护每个设备的角标计数，需要用户使用1.9.5以上版本的sdk，并且需要用户主动同步角标数字到服务端。 |
| iOSSilentNotification | bool | 否 | 开启iOS静默通知 |
| iOSSubtitle | string | 否 | iOS通知副标题内容（iOS 10+） |
| iOSNotificationCategory | string | 否 | 指定iOS通知Category（iOS 10+） |
| iOSMutableContent | bool | 否 | 是否使能iOS通知扩展处理（iOS 10+） |
| iOSApnsEnv | string | 否 | iOS的通知是通过APNs中心来发送的，需要填写对应的环境信息。 `DEV`：表示开发环境 `PRODUCT`：表示生产环境 |
| iOSRemind | bool | 否 | 消息推送时设备不在线（既与移动推送的服务端的长连接通道不通），则这条推送会做为通知，通过苹果的APNs通道送达一次。注意：离线消息转通知仅适用于生产环境 |
| iOSRemindBody | string | 否 | 	iOS消息转通知时使用的iOS通知内容，仅当iOSApnsEnv=PRODUCT && iOSRemind为true时有效 |

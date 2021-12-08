###  安装
```shell
composer require weeds/wechat-work
```

### 添加服务 修改 config/app.php
```shell
providers 新增 Weeds\WechatWork\WechatWorkServiceProvider::class
aliases 新增 'WechatWork'=>Weeds\WechatWork\Facades\WechatWork::class
```

### 清理缓存
```shell
 composer dump-autoload
```

###  发布配置到框架配置目录
```shell
php artisan vendor:publish --provider="Weeds\WechatWork\WechatWorkServiceProvider"
```
###

### 修改env文件
```shell
# 企业微信sdk配置
#通讯录
WECHATWORK_CORP_ID = xxx
WECHATWORK_AGENTS_CONTACTS_SECRET = xxx
#打卡
WECHATWORK_AGENTS_OA_AGENT_ID = 110xxxx
WECHATWORK_AGENTS_OA_SECRET = xxxx
#自建应用
WECHATWORK_AGENTS_APPLICATION_AGENT_ID = 222xxx
WECHATWORK_AGENTS_APPLICATION_SECRET = xxx
```
###

###  使用
```php
    use WechatWork;
    list($status,$list) = WechatWork::department_list();
    dump($list);exit;
```

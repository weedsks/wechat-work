###  安装
```shell
composer require
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

###  使用
```php
    use WechatWork;
    $token = WechatWork::access_token();
    dump($token);exit;
```

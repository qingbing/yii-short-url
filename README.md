# yii-short-url
yii实现的url短链系统

## 1.该系统使用，需要设置
```php
// 客户短链系统访问时的终端地址
Yii::$app->params['baseUrlForShortUrl']
```

## 2.短链的后台管理控制器可以直接引用
```text
\YiiShortUrl\controllers\ShortUrlController
```

## 3.网页前台使用
```php
# 添加URL使用
\YiiShortUrl\logics\LogicShortUrl::getInstance()->add($params);

# 对于访问短链，提供action: \YiiShortUrl\actions\RedirectAccess
```

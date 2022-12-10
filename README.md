# php-fastdns

Клиент к FastVPS DNS API. Позволяет создавать, редактировать, удалять домены и записи.

Полезно для автоматизации, если у вас много доменов/поддоменов.

## Установка

```shell
composer require ch1p/php-fastdns
```

## Использование

```php
use ch1p\FastDNS;
use ch1p\FastDNSException;

$fastdns = new FastDNS();
try {
	// авторизуемся
	$fastdns->auth('ВАШ ТОКЕН');

	// готово
	// для примера, получим список доменов
	$domains = $fastdns->getDomains();
	var_dump($domains);
} catch (FastDNSException $e) {
	// что-то пошло не так
}
```

## Документация

Все доступные методы и параметры смотрите в классе, он простой.

А вот OpenAPI спека: https://f.ch1p.io/1pCMAoat/fast-api.yaml

## Лицензия

BSD-2c

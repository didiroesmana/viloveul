# viloveul

Micro url-routing.

```batch
$ git clone https://github.com/zafex/viloveul.git
$ cd viloveul
$ composer install

```

### Example to use:

```php
require __DIR__ . '/vendor/autoload.php';

$app = new Viloveul\Application(__DIR__ . '/Project', []);

$app->route('/', function () use ($app) {
    return 'Default Handler';
});

$app->route('/abc', function () use ($app) {
    return 'abc';
});

$app->route('post', '/abc(/.*)?', function () use ($app) {
    return 'post abc sampai z';
});

$app->route('get', '404', function () use ($app) {
    return 'ini 404';
});

$app->run();

```

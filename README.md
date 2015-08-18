# viloveul
mini php mvc with url-routing

### example to use :

```php

require_once 'viloveul/Viloveul/Factory.php';

$app = Viloveul\Factory::serve($applicationDirectory);

$app->handle('/', function() {
    return "Hello World";
});

$app->run();
```

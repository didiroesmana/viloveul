# viloveul
mini php mvc with url-routing

### example to use :

```php

require_once 'viloveul/Viloveul/App.php';

$app = Viloveul\App::serve($applicationDirectory);

$app->handle('/', function() {
    return "Hello World";
});

$app->run();
```

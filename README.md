# viloveul

Mini PHP MVC with url-routing.

### Example to use:

```php
require_once 'path-to-viloveul/Viloveul/Application.php';

$app = new Viloveul\Application($applicationDirectory);

$app->handle('/', function() {
    return "Hello World";
});

$app->run();
```

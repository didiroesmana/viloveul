# viloveul

Mini PHP MVC with url-routing.

### Example to use:

```php
require_once 'path-to-viloveul/Viloveul/Factory.php';

$app = Viloveul\Factory::serve($applicationDirectory);

$app->handle('/', function() {
    return "Hello World";
});

$app->run();
```

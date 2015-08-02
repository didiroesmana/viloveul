# viloveul
mini php mvc with url-routing

## example to use

require_once 'viloveul/Viloveul/App.php';

$app = Viloveul\App::serve(__DIR__);

$app->handle('/', function() {
    return "Hello World";
});

$app->run();

<?php

require __DIR__.'/Viloveul/Factory.php';

Viloveul\Factory::registerSystemAutoloader();

$configs = require __DIR__.'/configs.php';

$app = Viloveul\Factory::serve('Project', $configs);

$app->handle('/', function () use ($app) {
    return 'Default Handler';
});

$app->run();

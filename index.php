<?php

require __DIR__.'/Viloveul/Application.php';

$configs = require __DIR__.'/configs.php';

$app = new Viloveul\Application(__DIR__.'/Project', $configs);

$app->route('/', function () use ($app) {
    return 'Default Handler';
});

$app->run();

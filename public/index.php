<?php

require dirname(__DIR__).'/system/Viloveul/Application.php';

$configs = require __DIR__.'/configs.php';

$app = new Viloveul\Application(dirname(__DIR__).'/project', $configs);

$app->route('/', function () use ($app) {
    return 'Default Handler';
});

$app->run();

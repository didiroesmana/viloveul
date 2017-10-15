<?php

require __DIR__ . '/vendor/autoload.php';

$app = new Viloveul\Application(__DIR__ . '/Project', []);

$app->route('/', function () use ($app) {
    return 'Default Handler';
});

$app->route('/abc', function () use ($app) {
    return 'abc';
});

$app->route('/abc(/.*)?', function () use ($app) {
    return 'abc sampai z';
});

$app->run();

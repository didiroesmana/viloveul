<?php

require __DIR__.'/Viloveul/Factory.php';

Viloveul\Factory::registerSystemAutoloader();

$app = Viloveul\Factory::serve('Project');

$app->handle('/', function () use ($app) {
    return 'Default Handler';
});

$app->run();

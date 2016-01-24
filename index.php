<?php

require __DIR__.'/Viloveul/Factory.php';

Viloveul\Factory::useSystemAutoloader();

$app = Viloveul\Factory::serve(
	__DIR__.'/Project',
	__DIR__.'/configs.php'
);

$app->handle('/', function () use ($app) {
    return 'Default Handler';
});

$app->run();

<?php error_reporting(-1);

require __DIR__.'/Viloveul/Factory.php';

$app = Viloveul\Factory::serve(__DIR__.'/Project');

$app->handle('/', function() use($app){
	return 'Default Handler';
});

$app->run();

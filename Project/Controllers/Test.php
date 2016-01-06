<?php

namespace App\Controllers;

use Viloveul\Core\Controller;

class Test extends Controller
{
	public function actionIndex()
	{
		return __METHOD__;
	}

	public function actionSomething()
	{
		return __METHOD__;
	}

	public function __invoke()
	{
		return __METHOD__;
	}
}
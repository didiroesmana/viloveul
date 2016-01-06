<?php

namespace App\Controllers;

use App\Models\SampleModel;
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
		$model = SampleModel::forge()->some();
		print_r($model);
	}
}
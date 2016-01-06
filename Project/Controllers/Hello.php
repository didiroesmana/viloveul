<?php

namespace App\Controllers;

use Viloveul\Core\Controller;

class Hello extends Controller
{
    public function actionIndex()
    {
        return 'Hello world!';
    }

    public function actionOne()
    {
        return 'One';
    }

    public function actionTwo()
    {
        return 'Two';
    }

    public function actionThree()
    {
        return 'Three';
    }
}

<?php namespace App\Controllers;

use Viloveul\Core\Controller;

class Hello extends Controller {

	/**
	 * Magic Method : __invoke
	 * visit : yourdomain.tld/index.php/hello/anything
	 * it will become only to method __invoke
	 * anything action* will not calling
	 * 
	 * @access	public
	 * @return	String
	 */

	public function __invoke() {
		return "Hello World";
	}

	public function actionOne() {
		return 'one';
	}

	public function actionTwo() {
		return 'two';
	}

	public function actionThree() {
		return 'three';
	}

}

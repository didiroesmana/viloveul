<?php namespace App\Controllers;

use Viloveul\Core\Controller;

class Welcome extends Controller {

	/**
	 * actionIndex
	 * visit : yourdomain.tld/index.php/welcome/index or yourdomain.tld/index.php/hello
	 * action -> (Index) is default handler for controller class
	 * 
	 * @access	public
	 * @return	String
	 */

	public function actionIndex() {
		return 'Welcome !!!';
	}

	/**
	 * actionWithName
	 * visit : yourdomain.tld/index.php/welcome/with-name/yourname
	 * dashed will be converted to camelize (only for controller and method name)
	 * 
	 * @access	public
	 * @return	String
	 */

	public function actionWithName($name = '') {
		return 'Welcome ' . $name;
	}

}

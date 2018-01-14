<?php
/**
 * Copyright 2009-2014, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009-2014, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Tags\Controller;

use App\Controller\AppController;

/**
 * Tags Controller
 */
class TagsController extends AppController {

				/**
				 * Uses
				 *
				 * @var array
				 */
	public $uses = [
		'Tags.Tag'
	];

				/**
				 * Components
				 *
				 * @var array
				 */
	public $components = [
		'Paginator'
	];

				/**
				 * Helpers
				 *
				 * @var array
				 */
	public $helpers = [
		'Html', 'Form'
	];

				/**
				 * Index action
				 *
				 * @return void
				 */
	public function index() {
		//$this->{$this->modelClass}->recursive = 0;
		$this->set('tags', $this->paginate());
	}

				/**
				 * View
				 *
				 * @param string|null $id Tag UUID.
				 * @return void
				 */
	public function view($id = null) {
		try {
			$this->set('tag', $this->{$this->modelClass}->view($id));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect('/');
		}
	}

}

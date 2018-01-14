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

namespace Tags\Controller\Admin;

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
	 * Admin Index
	 *
	 * @return void
	 */
	public function index() {
		$this->set('tags', $this->Paginator->paginate());
	}

	/**
	 * Views a single tag
	 *
	 * @param string $id Tag UUID.
	 * @return void
	 */
	public function view($id) {
		try {
			$this->set('tag', $this->{$this->modelClass}->view($id));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect('/');
		}
	}

	/**
	 * Adds one or more tags
	 *
	 * @return void
	 */
	public function add() {
		if (!empty($this->request->data)) {
			if ($this->{$this->modelClass}->add($this->request->data)) {
				$this->Session->setFlash(__d('tags', 'The Tags has been saved.'));
				$this->redirect(['action' => 'index']);
			}
		}
	}

	/**
	 * Edits a tag
	 *
	 * @param string|null $id Tag UUID.
	 * @return void
	 */
	public function edit($id = null) {
		try {
			$result = $this->{$this->modelClass}->edit($id, $this->request->data);
			if ($result === true) {
				$this->Session->setFlash(__d('tags', 'Tag saved.'));
				$this->redirect(['action' => 'index']);
			} else {
				$this->request->data = $result;
			}
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect(['action' => 'index']);
		}

		if (empty($this->request->data)) {
			$this->request->data = $this->{$this->modelClass}->data;
		}
	}

	/**
	 * Deletes a tag
	 *
	 * @param string|null $id Tag UUID.
	 * @return void
	 */
	public function delete($id = null) {
		if ($this->{$this->modelClass}->delete($id)) {
			$this->Session->setFlash(__d('tags', 'Tag deleted.'));
		} else {
			$this->Session->setFlash(__d('tags', 'Invalid Tag.'));
		}
		$this->redirect(['action' => 'index']);
	}

}

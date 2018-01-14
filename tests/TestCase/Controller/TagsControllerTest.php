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

namespace Tags\Test\TestCase\Controller;

use Cake\Network\Request;
use Cake\TestSuite\TestCase;
use Tags\Controller\TagsController;

class TagsControllerTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.tags.tagged',
		'plugin.tags.tag'
	];

	/**
	 * @var null
	 * Tags Controller Instance
	 *
	 * @param \Tags\Controller\TagsController
	 */
	public $Tags = null;

				/**
				 * setUp
				 *
				 * @return void
				 */
	public function setUp() {
		parent::setUp();
		$this->Tags = new TestTagsController(new Request());
		$this->Tags->params = [
			'named' => [],
			'url' => []];
		//$this->Tags->constructClasses();
		//$this->Tags->Session = $this->getMock('SessionComponent', array(), array(), '', false);
	}

				/**
				 * tearDown
				 *
				 * @return void
				 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Tags);
	}

				/**
				 * testTagsControllerInstance
				 *
				 * @return void
				 */
	public function testTagsControllerInstance() {
		$this->assertTrue(is_a($this->Tags, 'TagsController'));
	}

				/**
				 * testIndex
				 *
				 * @return void
				 */
	public function testIndex() {
		$this->Tags->index();
		$this->assertTrue(!empty($this->Tags->viewVars['tags']));
	}

				/**
				 * testIndex
				 *
				 * @return void
				 */
	public function testView() {
		$this->Tags->view('cakephp');
		$this->assertTrue(!empty($this->Tags->viewVars['tag']));
		$this->assertEquals($this->Tags->viewVars['tag']['Tag']['keyname'], 'cakephp');

		$this->Tags->view('invalid-key-name!');
		$this->assertEquals($this->Tags->redirectUrl, '/');
	}

				/**
				 * testIndex
				 *
				 * @return void
				 */
	public function testAdminView() {
		$this->Tags->admin_view('cakephp');
		$this->assertTrue(!empty($this->Tags->viewVars['tag']));
		$this->assertEquals($this->Tags->viewVars['tag']['Tag']['keyname'], 'cakephp');

		$this->Tags->admin_view('invalid-key-name!');
		$this->assertEquals($this->Tags->redirectUrl, '/');
	}

				/**
				 * testAdminIndex
				 *
				 * @return void
				 */
	public function testAdminIndex() {
		$this->Tags->admin_index();
		$this->assertTrue(!empty($this->Tags->viewVars['tags']));
	}

				/**
				 * testAdminDelete
				 *
				 * @return void
				 */
	public function testAdminDelete() {
		$this->Tags->Session->expects($this->at(0))
			->method('setFlash')
			->with($this->equalTo(__d('tags', 'Invalid Tag.')))
			->will($this->returnValue(true));

		$this->Tags->Session->expects($this->at(1))
			->method('setFlash')
			->with($this->equalTo(__d('tags', 'Tag deleted.')))
			->will($this->returnValue(true));

		$this->Tags->admin_delete('WRONG-ID!!!');
		$this->assertEquals($this->Tags->redirectUrl, ['action' => 'index']);

		$this->Tags->admin_delete('tag-1');
		$this->assertEquals($this->Tags->redirectUrl, ['action' => 'index']);
	}

				/**
				 * testAdminAdd
				 *
				 * @return void
				 */
	public function testAdminAdd() {
		$this->Tags->data = [
			'Tag' => [
				'tags' => 'tag1, tag2, tag3']];
		$this->Tags->admin_add();
		$this->assertEquals($this->Tags->redirectUrl, ['action' => 'index']);

		// adding same tags again.
		$this->Tags->data = [
			'Tag' => [
				'tags' => 'tag1, tag2, tag3']];
		$this->Tags->admin_add();
		$this->assertEquals($this->Tags->redirectUrl, ['action' => 'index']);
	}

				/**
				 * testAdminEdit
				 *
				 * @return void
				 */
	public function testAdminEdit() {
		$this->Tags->admin_edit('tag-1');
		$tag = [
			'Tag' => [
				'id' => 'tag-1',
				'identifier' => null,
				'name' => 'CakePHP',
				'keyname' => 'cakephp',
				'occurrence' => 1,
				'article_occurrence' => 1,
				'created' => '2008-06-02 18:18:11',
				'modified' => '2008-06-02 18:18:37']];

		$this->assertEquals($this->Tags->data, $tag);

		$this->Tags->data = [
			'Tag' => [
				'id' => 'tag-1',
				'name' => 'CAKEPHP']];
		$this->Tags->admin_edit('tag-1');

		$this->assertEquals($this->Tags->redirectUrl, ['action' => 'index']);
	}

}

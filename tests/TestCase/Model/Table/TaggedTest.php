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

namespace Tags\Test\TestCase\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * TagggedArticle Test Model
 */
class TaggedArticle extends Table {

	public $useTable = 'articles';

	public $actsAs = [
		'Tags.Taggable'
	];

	public $belongsTo = [
		'User'
	];

}

/**
 * Short description for class.
 */
class TaggedTest extends TestCase {

				/**
				 * Tagged model
				 *
				 * @var Tagged|null
				 */
	public $Tagged = null;

				/**
				 * Fixtures
				 *
				 * @var array
				 */
	public $fixtures = [
		'plugin.tags.tagged',
		'plugin.tags.tag',
		'plugin.tags.article',
		'plugin.tags.user'
	];

				/**
				 * setUp
				 *
				 * @return void
				 */
	public function setUp() {
		parent::setUp();
		$this->Tagged = TableRegistry::get('Tags.Tagged');
	}

				/**
				 * tearDown
				 *
				 * @return void
				 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Tagged);
		TableRegistry::clear();
	}

				/**
				 * testTaggedInstance
				 *
				 * @return void
				 */
	public function testTaggedInstance() {
		$this->assertTrue(is_a($this->Tagged, 'Tagged'));
	}

				/**
				 * testTaggedInstance
				 *
				 * @return void
				 */
	public function testTaggedFind() {
		$this->Tagged->recursive = -1;
		$result = $this->Tagged->find('first');
		$this->assertTrue(!empty($result));

		$expected = [
			'Tagged' => [
				'id' => '49357f3f-c464-461f-86ac-a85d4a35e6b6',
				'foreign_key' => 'article-1',
				'tag_id' => 'tag-1', //cakephp
				'model' => 'Article',
				'language' => 'eng',
				'times_tagged' => 1,
				'created' => '2008-12-02 12:32:31',
				'modified' => '2008-12-02 12:32:31']];

		$this->assertEquals($result, $expected);
	}

				/**
				 * testFindCloud
				 *
				 * @return void
				 */
	public function testFindCloud() {
		$result = $this->Tagged->find('cloud', [
			'model' => 'Article']);

		$this->assertEquals(count($result), 3);
		$this->assertTrue(isset($result[0][0]['occurrence']));
		$this->assertEquals($result[0][0]['occurrence'], 1);

		$result = $this->Tagged->find('cloud');
		$this->assertTrue(is_array($result) && !empty($result));

		$result = $this->Tagged->find('cloud', [
			'limit' => 1]);
		$this->assertEquals(count($result), 1);
	}

				/**
				 * Test custom _findTagged method
				 *
				 * @return void
				 */
	public function testFindTagged() {
		$this->Tagged->recursive = -1;
		$result = $this->Tagged->find('tagged', [
			'by' => 'cakephp',
			'model' => 'Article']);
		$this->assertEquals(count($result), 1);
		$this->assertEquals($result[0]['Article']['id'], 'article-1');

		$result = $this->Tagged->find('tagged', [
			'model' => 'Article']);
		$this->assertEquals(count($result), 2);
		// Test call to paginateCount by Controller::pagination()
		$result = $this->Tagged->paginateCount([], 1, [
			'model' => 'Article',
			'type' => 'tagged']);
		$this->assertEquals($result, 2);
	}

				/**
				 * Test custom _findTagged method with additional conditions on the model
				 *
				 * @return void
				 */
	public function testFindTaggedWithConditions() {
		$this->Tagged->recursive = -1;
		$result = $this->Tagged->find('tagged', [
			'by' => 'cakephp',
			'model' => 'Article',
			'conditions' => ['Article.title LIKE' => 'Second %']]);
		$this->assertEquals(count($result), 0);

		$result = $this->Tagged->find('tagged', [
			'by' => 'cakephp',
			'model' => 'Article',
			'conditions' => ['Article.title LIKE' => 'First %']]);
		$this->assertEquals(count($result), 1);
		$this->assertEquals($result[0]['Article']['id'], 'article-1');
	}

				/**
				 * testDeepAssociations
				 *
				 * @link https://github.com/CakeDC/tags/issues/15
				 * @return void
				 */
	public function testDeepAssociationsHasOne() {
		$this->Tagged->bindModel([
			'belongsTo' => [
				'Article' => [
					'className' => 'TaggedArticle',
					'foreignKey' => 'foreign_key']]]);

		$this->Tagged->Article->bindModel([
			'hasOne' => [
				'User' => []]]);

		$result = $this->Tagged->find('all', [
			'contain' => [
				'Article' => [
					'User']]]);

		$this->assertEquals($result[0]['Article']['User']['name'], 'CakePHP');
	}

				/**
				 * testDeepAssociationsBelongsTo
				 *
				 * @link https://github.com/CakeDC/tags/issues/15
				 * @return void
				 */
	public function testDeepAssociationsBelongsTo() {
		$this->Tagged->bindModel([
			'belongsTo' => [
				'Article' => [
					'className' => 'TaggedArticle',
					'foreignKey' => 'foreign_key']]]);

		$this->Tagged->Article->bindModel([
			'belongsTo' => [
				'User' => []]]);

		$result = $this->Tagged->find('all', [
			'contain' => [
				'Article' => [
				'User']]]);

		$this->assertEquals($result[0]['Article']['User']['name'], 'CakePHP');
	}

}

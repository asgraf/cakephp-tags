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

namespace Tags\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

/**
 * TaggableTest
 */
class TaggableBehaviorTest extends TestCase {

    /**
     * Plugin name used for fixtures loading
     *
     * @var string
     */
	public $plugin = 'tags';

    /**
     * Holds the instance of the model
     *
     * @var \App\Model\Table\TaggedArticlesTable
     */
	public $TaggedArticles;

    /**
     * Fixtures associated with this test case
     *
     * @var array
     * @return void
     */
	public $fixtures = [
		'plugin.tags.tagged',
		'plugin.tags.tags',
		'plugin.tags.tagged_articles'
	];

    /**
     * Method executed before each test
     *
     * @return void
     */
	public function setUp() {
		parent::setUp();
		$this->TaggedArticles = TableRegistry::get('TaggedArticles');
		Configure::write('Config.language', 'eng');
	}

    /**
     * Method executed after each test
     *
     * @return void
     */
	public function tearDown() {
		parent::tearDown();
		unset($this->TaggedArticles);
		TableRegistry::clear();
	}

    /**
     * Test the occurrence cache
     *
     * @return void
     */
	public function testOccurrenceCache() {
		$resultBefore = $this->TaggedArticles->Tags->find('all', [
			'contain' => [
				//'Tagged'
			],
			'conditions' => [
				'keyname' => 'cakephp'
			]
		])->first();

		// adding a new record with the cakephp tag to increase the occurrence
		$entity = $this->TaggedArticles->newEntity(['title' => 'Test Article', 'tags' => 'cakephp, php']);
		$this->TaggedArticles->save($entity);

		$resultAfter = $this->TaggedArticles->Tags->find('all', [
			'contain' => [],
			'conditions' => [
				'Tags.keyname' => 'cakephp'
			]
		])->first();
		debug($resultBefore);
        debug($resultAfter);
		$this->assertEquals($resultAfter['occurrence'] - $resultBefore['occurrence'], 1);

		// updating the record to not have the cakephp tag anymore, decreases the occurrence
		$entity = $this->TaggedArticles->newEntity([
			'id' => $entity->id,
			'title' => 'Test Article',
			'tags' => 'php, something, else'
		]);
		$entity->isNew(false);
		$this->TaggedArticles->save($entity);
		$resultAfter = $this->TaggedArticles->Tags->find('all', [
			'contain' => [],
			'conditions' => [
				'Tags.keyname' => 'cakephp'
			]
		])->first();
		$this->assertEquals($resultAfter['occurrence'], 1);
	}

    /**
     * Testings saving of tags trough the specified field in the tagable model
     *
     * @return void
     */
	public function testTagSaving() {
		$data['id'] = 'article-1';
		$data['tags'] = 'foo, bar, test';

		$data = $this->TaggedArticles->newEntity($data);
		$this->TaggedArticles->save($data);
		$result = $this->TaggedArticles->find('all', [
			'conditions' => [
				'id' => '1'
			]
		])->first();
		$this->assertTrue(!empty($result['tags']));

		$data['tags'] = 'foo, developer, developer, php';
		$data = $this->TaggedArticles->newEntity($data);
		$this->TaggedArticles->save($data);
		$result = $this->TaggedArticles->find('all', [
			'contain' => ['Tags'],
			'conditions' => [
				'id' => '1'
			]
		])->first();

		$this->assertTrue(!empty($result['tags']));
		$this->assertEquals(3, count($result));

		$data['tags'] = 'cakephp:foo, developer, cakephp:developer, cakephp:php';
		$data = $this->TaggedArticles->newEntity($data);
		$this->TaggedArticles->save($data);
		$result = $this->TaggedArticles->Tags->find('all', [
			'recursive' => -1,
			'order' => 'Tags.identifier DESC, Tags.name ASC',
			'conditions' => [
				'Tags.identifier' => 'cakephp'
            ]
        ])->toArray();

		$result = Hash::extract($result, '{n}.keyname');
		$this->assertEquals($result, [
			'developer', 'foo', 'php']);

		$this->assertFalse($this->TaggedArticles->saveTags('foo, bar', null));
		$this->assertFalse($this->TaggedArticles->saveTags(['foo', 'bar'], 'something'));
	}

				/**
				 * Tests that toggling taggedCounter will update the time_tagged counter in the tagged table
				 *
				 * @return void
				 */
	public function testSaveTimesTagged() {
		$this->TaggedArticles->behaviors()->Taggable->setConfig('taggedCounter', true);
		$tags = 'foo, bar , test';
		$this->assertTrue($this->TaggedArticles->saveTags($tags, 'article-1', false));
		$this->assertTrue($this->TaggedArticles->saveTags($tags, 'article-1', false));

		$result = $this->TaggedArticles->Tagged->find('all', [
			'conditions' => ['model' => 'Article'],
			'contain' => ['Tags'],
		]);
		$fooCount = Set::extract('/Tag[keyname=foo]/../Tagged/times_tagged', $result);
		$this->assertEquals($fooCount, [2]);

		$barCount = Set::extract('/Tag[keyname=bar]/../Tagged/times_tagged', $result);
		$this->assertEquals($barCount, [2]);

		$testCount = Set::extract('/Tag[keyname=test]/../Tagged/times_tagged', $result);
		$this->assertEquals($testCount, [2]);
	}

    /**
     * Testing Taggable::tagArrayToString()
     *
     * @return void
     */
	public function testTagArrayToString() {
		$data['id'] = 'article-1';
		$data['tags'] = 'foo, bar, test';
		$data = $this->TaggedArticles->newEntity($data);
		$data->isNew(false);
		$this->TaggedArticles->save($data);
		$result = $this->TaggedArticles->find('all', [
			'conditions' => [
				'id' => 'article-1'
			]
		])->first();
		$result = $this->TaggedArticles->tagArrayToString($result);
		$this->assertTrue(!empty($result));
		$this->assertInternalType('string', $result);
		$this->assertEquals($result, 'test, bar, foo');

		$result = $this->TaggedArticles->tagArrayToString();
		$this->assertTrue(empty($result));
		$this->assertInternalType('string', $result);

		$data['tags'] = 'cakephp:foo, cakephp:bar, foo, bar';
		$data = $this->TaggedArticles->newEntity($data);
		$this->TaggedArticles->save($data);
		$result = $this->TaggedArticles->find('first', [
			'conditions' => [
				'id' => 'article-1'
			]
		]);

		$result = $this->TaggedArticles->tagArrayToString($result);
		$this->assertTrue(!empty($result));
		$this->assertInternalType('string', $result);
		$this->assertEquals($result, 'cakephp:bar, cakephp:foo, bar, foo');
	}

				/**
				 * Testings Taggable::multibyteKey()
				 *
				 * @return void
				 */
	public function testMultibyteKey() {
		$result = $this->TaggedArticles->multibyteKey('this is _ a Nice ! - _ key!');
		$this->assertEquals('thisisanicekey', $result);

		$result = $this->TaggedArticles->multibyteKey('Äü-Ü_ß');
		$this->assertEquals('äüüß', $result);
	}

				/**
				 * testAfterFind callback method
				 *
				 * @return void
				 */
	public function testAfterFind() {
		$data['id'] = 'article-1';
		$data['tags'] = 'foo, bar, test';
		$data = $this->TaggedArticles->newEntity($data);
		$this->TaggedArticles->save($data);

		$result = $this->TaggedArticles->find('all', [
			'conditions' => [
				'id' => 'article-1'
			]
		])->first();
		$this->assertTrue(isset($result));

		$this->TaggedArticles->behaviors()->Taggable->setConfig('unsetInAfterFind', true);
		$result = $this->TaggedArticles->find('all', [
			'conditions' => [
				'id' => 'article-1'
			]
		])->first();
		$this->assertTrue(!isset($result));
	}

				/**
				 * testAfterFindFields
				 *
				 * @return void
				 */
	public function testAfterFindFields() {
		$this->TaggedArticles->removeBehavior('Taggable');
		$results = $this->TaggedArticles->find('all', [
			'recursive' => -1,
			'fields' => ['id']
		])->first();
		$expected = [$this->TaggedArticles->alias => ['id' => 'article-1']];
		$this->assertIdentical($results, $expected);
	}

				/**
				 * testGettingTagCloudThroughAssociation
				 *
				 * @link http://cakedc.lighthouseapp.com/projects/59622/tickets/6-tag-cloud-helper
				 * @return void
				 */
	public function testGettingTagCloudThroughAssociation() {
		$result = $this->TaggedArticles->Tagged->find('cloud');
		$this->assertTrue(is_array($result) && !empty($result));
	}

				/**
				 * testSavingEmptyTagsDeleteAssociatedTags
				 *
				 * @return void
				 */
	public function testSavingEmptyTagsDeleteAssociatedTags() {
		$this->TaggedArticles->behaviors()->Taggable->setConfig('deleteTagsOnEmptyField', true);
		$data = $this->TaggedArticles->findById('article-1');
		$data['tags'] = '';
		$this->TaggedArticles->save($data);
		$result = $this->TaggedArticles->find('first', [
			'conditions' => ['id' => 'article-1']
		]);

		$this->assertEmpty($result);
	}

				/**
				 * testSavingEmptyTagsDoNotDeleteAssociatedTags
				 *
				 * @return void
				 */
	public function testSavingEmptyTagsDoNotDeleteAssociatedTags() {
		$this->TaggedArticles->behaviors()->Taggable->setConfig('deleteTagsOnEmptyField', false);
		$data = $this->TaggedArticles->findById('article-1');
		$data['tags'] = '';
		$this->TaggedArticles->save($data);
		$result = $this->TaggedArticles->find('first', [
			'conditions' => ['id' => 'article-1']
		]);

		$this->assertNotEmpty($result);
	}

    /**
     * testSavingTagsDoesNotCreateEmptyRecords
     *
     * @return void
     */
	public function testSavingTagsDoesNotCreateEmptyRecords() {
		$count = $this->TaggedArticles->Tags->find('all', [
			'conditions' => [
				'Tags.name' => '',
				'Tags.keyname' => '',
			]
		])->count();
		$this->assertEquals($count, 0);

		/*
		// SELECT Tags.id AS "Tags__id", Tags.identifier AS "Tags__identifier", Tags.name AS "Tags__name", Tags.keyname AS "Tags__keyname", Tags.occurrence AS "Tags__occurrence", Tags.article_occurrence AS "Tags__article_occurrence", Tags.created AS "Tags__created", Tags.modified AS "Tags__modified" FROM tags Tags INNER JOIN tagged Tagged ON (Tags.id = (Tagged.tag_id) AND Tagged.model = :c0)'
		$tag = $this->TaggedArticles->Tags->newEntity([
		    'name' => 'Bar',
            'keyname' => 'bar',
        ]);
		$result = $this->TaggedArticles->Tags->save($tag);
		$this->assertTrue((bool)$result);
		*/

		$entity = $this->TaggedArticles->get(1);
		$entity->tag_list = 'foo, bar, test';

		$result = $this->TaggedArticles->save($entity);
		$this->assertTrue((bool)$result);

		$result = $this->TaggedArticles->find('all', [
			'conditions' => [
				'id' => '1'
            ],
            'contain' => ['Tags'],
		])->first();
		dd($result);

		$count = $this->TaggedArticles->Tags->find('all', [
			'conditions' => [
				'Tags.name' => '',
				'Tags.keyname' => '',
			]
		])->count();
		$this->assertEquals($count, 0);
	}

    /**
     * testSavingTagsWithDefferentIdentifier
     *
     * @return void
     */
	public function testSavingTagsWithDifferentIdentifier() {
		$data = $this->TaggedArticles->findById('article-1');
		$data['tags'] = 'foo:cakephp, bar:cakephp';
		$data = $this->TaggedArticles->newEntity($data);
		$this->TaggedArticles->save($data);
		$data = $this->TaggedArticles->findById('article-1');
		$this->assertEquals('bar:cakephp, foo:cakephp', $data['tags']);
	}

				/**
				 * testDeletingMoreThanOneTagAtATime
				 *
				 * @link https://github.com/CakeDC/tags/issues/86
				 * @return void
				 */
	public function testDeletingMoreThanOneTagAtATime() {
		// Adding five tags for testing
		$data = [
			'Article' => [
				'id' => 'article-test-delete-tags',
				'tags' => 'foo, bar, test, second, third',
			]
		];
		$entity = $this->TaggedArticles->newEntity($data);
		$this->TaggedArticles->save($entity, false);
		$result = $this->TaggedArticles->find('all', [
			'conditions' => [
				'id' => 'article-test-delete-tags'
			]
		])->first();
		$this->assertEquals($result['tags'], 'third, second, test, bar, foo');
		// Removing three of the five previously added tags
		$result['tags'] = 'third, second';
		$this->TaggedArticles->save($result, false);
		$result = $this->TaggedArticles->find('all', [
			'conditions' => [
				'id' => 'article-test-delete-tags'
			]
		])->first();
		$this->assertEquals($result['tags'], 'second, third');
		// Removing all tags, empty string - WON'T work as expected because of deleteTagsOnEmptyField
		$result['tags'] = '';
		$this->TaggedArticles->save($result, false);
		$result = $this->TaggedArticles->find('all', [
			'conditions' => [
				'id' => 'article-test-delete-tags'
			]
		])->first();
		$this->assertEquals($result['tags'], 'third, second');
		// Now with deleteTagsOnEmptyField
		$this->TaggedArticles->addBehavior('Tags.Taggable', [
			'deleteTagsOnEmptyField' => true
		]);
		$result['tags'] = '';
		$this->TaggedArticles->save($result, false);
		$result = $this->TaggedArticles->find('all', [
			'conditions' => [
				'id' => 'article-test-delete-tags'
			]
		])->first();
		$this->assertEquals($result['tags'], '');
	}

}

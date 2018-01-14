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

namespace Tags\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TagFixture
 */
class TagsFixture extends TestFixture {

				/**
				 * Table
				 *
				 * @var string
				 */
	public $table = 'tags';

				/**
				 * Fields
				 *
				 * @var array
				 */
	public $fields = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10],
		'identifier' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 30],
		'name' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 30],
		'keyname' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 30],
		'occurrence' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 8],
		'article_occurrence' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 8],
		'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'_constraints' => [
			'PRIMARY' => ['type' => 'primary', 'columns' => ['id']],
			'UNIQUE_TAG' => ['type' => 'unique', 'columns' => ['identifier', 'keyname']]
		]
	];

				/**
				 * Records
				 *
				 * @var array
				 */
	public $records = [
		[
			'id' => '1',
			'identifier' => null,
			'name' => 'CakePHP',
			'keyname' => 'cakephp',
			'occurrence' => 1,
			'article_occurrence' => 1,
			'created' => '2008-06-02 18:18:11',
			'modified' => '2008-06-02 18:18:37'
		],
		[
			'id' => '2',
			'identifier' => null,
			'name' => 'CakeDC',
			'keyname' => 'cakedc',
			'occurrence' => 1,
			'article_occurrence' => 1,
			'created' => '2008-06-01 18:18:15',
			'modified' => '2008-06-01 18:18:15'
		],
		[
			'id' => '3',
			'identifier' => null,
			'name' => 'CakeDC',
			'keyname' => 'cakedc',
			'occurrence' => 1,
			'article_occurrence' => 1,
			'created' => '2008-06-01 18:18:15',
			'modified' => '2008-06-01 18:18:15'
		]
	];

}

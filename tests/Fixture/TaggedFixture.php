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
 * TaggedFixture
 */
class TaggedFixture extends TestFixture {

				/**
				 * Table
				 *
				 * @var string name$table
				 */
	public $table = 'tagged';

				/**
				 * Fields
				 *
				 * @var array
				 */
	public $fields = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10],
		'foreign_key' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10],
		'tag_id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10],
		'model' => ['type' => 'string', 'null' => false, 'default' => null],
		'language' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 6],
		'times_tagged' => ['type' => 'integer', 'null' => false, 'default' => 1],
		'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'_constraints' => [
			'PRIMARY' => ['type' => 'primary', 'columns' => ['id']],
			'UNIQUE_TAGGING' => ['type' => 'unique', 'columns' => ['model', 'foreign_key', 'tag_id', 'language']],
			//'INDEX_TAGGED' => ['type' => 'index', 'columns' => ['model']],
			//'INDEX_LANGUAGE' => ['type' => 'index', 'columns' => ['language']]
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
			'foreign_key' => '1', //article 1
			'tag_id' => '1', //cakephp
			'model' => 'Article',
			'language' => 'eng',
			'created' => '2008-12-02 12:32:31 ',
			'modified' => '2008-12-02 12:32:31',
		],
		[
			'id' => '2',
			'foreign_key' => '1',
			'tag_id' => '2', //cakedc
			'model' => 'Article',
			'language' => 'eng',
			'created' => '2008-12-02 12:32:31 ',
			'modified' => '2008-12-02 12:32:31',
		],
		[
			'id' => '3',
			'foreign_key' => '3',
			'tag_id' => '3', //cakedc
			'model' => 'Article',
			'language' => 'eng',
			'created' => '2008-12-02 12:32:31 ',
			'modified' => '2008-12-02 12:32:31',
		],
	];

}

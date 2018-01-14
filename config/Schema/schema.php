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

class TagSchema extends CakeSchema {

/**
 * Before callback
 *
 * @param array Event
 * @return bool
 */
	public function before($event = []) {
		return true;
	}

/**
 * After callback
 *
 * @param array Event
 * @return bool
 */
	public function after($event = []) {
		return true;
	}

/**
 * Schema for taggeds table
 *
 * @var array
 * @access public
 */
	public $tagged = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'],
		'foreign_key' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10],
		'tag_id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10],
		'model' => ['type' => 'string', 'null' => false, 'default' => null, 'key' => 'index'],
		'language' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 6, 'key' => 'index'],
		'times_tagged' => ['type' => 'integer', 'null' => false, 'default' => '1'],
		'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'indexes' => [
			'PRIMARY' => ['column' => 'id', 'unique' => 1],
			'UNIQUE_TAGGING' => ['column' => ['model', 'foreign_key', 'tag_id', 'language'], 'unique' => 1],
			'INDEX_TAGGED' => ['column' => 'model', 'unique' => 0],
			'INDEX_LANGUAGE' => ['column' => 'language', 'unique' => 0]
		]
	];

/**
 * Schema for tags table
 *
 * @var array
 * @access public
 */
	public $tags = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'],
		'identifier' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 30, 'key' => 'index'],
		'name' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 30],
		'keyname' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 30],
		'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'indexes' => [
			'PRIMARY' => ['column' => 'id', 'unique' => 1],
			'UNIQUE_TAG' => ['column' => ['identifier', 'keyname'], 'unique' => 1]
		]
	];

}

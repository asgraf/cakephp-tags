<?php
/**
 * CakePHP Tags Plugin
 *
 * Copyright 2009 - 2010, Cake Development Corporation
 *                        1785 E. Sahara Avenue, Suite 490-423
 *                        Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009 - 2010, Cake Development Corporation (http://cakedc.com)
 * @link      http://github.com/CakeDC/Tags
 * @package   plugins.tags
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Short description for class.
 *
 * @package         plugins.tags
 * @subpackage  plugins.tags.config.migrations
 */

class M49ac311a54844a9d87o822502jedc423 extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 * @access public
 */
	public $description = 'Initialize Tags Schema';

/**
 * Actions to be performed
 *
 * @var array
 * @access public
 */
	public $migration = [
		'up' => [
			'create_table' => [
				'tagged' => [
					'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'],
					'foreign_key' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10],
					'tag_id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10],
					'model' => ['type' => 'string', 'null' => false, 'default' => null, 'key' => 'index'],
					'language' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 6],
					'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
					'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
					'indexes' => [
						'PRIMARY' => ['column' => 'id', 'unique' => 1],
						'UNIQUE_TAGGING' => ['column' => ['model', 'foreign_key', 'tag_id', 'language'], 'unique' => 1],
						'INDEX_TAGGED' => ['column' => 'model', 'unique' => 0],
						'INDEX_LANGUAGE' => ['column' => 'language', 'unique' => 0]
					]
				],
				'tags' => [
					'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'],
					'identifier' => ['type' => 'string', 'null' => true, 'default' => null, 'length' => 30, 'key' => 'index'],
					'name' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 30],
					'keyname' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 30],
					'weight' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 2],
					'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
					'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
					'indexes' => [
						'PRIMARY' => ['column' => 'id', 'unique' => 1],
						'UNIQUE_TAG' => ['column' => ['identifier', 'keyname'], 'unique' => 1]
					]
				]
			]
		],
		'down' => [
			'drop_table' => ['tagged', 'tags']
		]
	];

/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return bool Should process continue
 * @access public
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return bool Should process continue
 * @access public
 */
	public function after($direction) {
		return true;
	}

}

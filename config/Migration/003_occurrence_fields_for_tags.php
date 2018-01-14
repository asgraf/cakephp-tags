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
 */
class M8d01880f01c11e0be500800200c9a66 extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 * @access public
 */
	public $description = 'Adds the column times_tagged to track the number of times a record has been tagged';

/**
 * Actions to be performed
 *
 * @var array
 * @access public
 */
	public $migration = [
		'up' => [
			'create_field' => [
				'tags' => [
					'occurrence' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 8],
					]
				],
			'drop_field' => [
				'tags' => ['weight']
			],
		],
		'down' => [
			'drop_field' => [
				'tags' => ['occurrence']
			],
			'create_field' => [
				'tags' => [
					'weight' => ['type' => 'integer', 'null' => false, 'default' => 0, 'length' => 2],
				],
			],
		],
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

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
 * UserFixture
 */
class UsersFixture extends TestFixture {

	/**
	 * fields property
	 *
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10],
		'name' => ['type' => 'string', 'null' => false],
		'article_id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10]
	];

	/**
	 * records property
	 *
	 * @var array
	 */
	public $records = [
		[
			'id' => '1',
			'name' => 'CakePHP',
			'article_id' => '1'
		],
		[
			'id' => '2',
			'name' => 'Second User',
			'article_id' => '2'
		],
		[
			'id' => '3',
			'name' => 'Third User',
			'article_id' => '3'
		]
	];

}

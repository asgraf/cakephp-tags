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
 * ArticleFixture
 */
class TaggedArticlesFixture extends TestFixture {

	/**
	 * fields property
	 *
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10],
		'title' => ['type' => 'string', 'null' => false],
		'user_id' => ['type' => 'integer', 'null' => true, 'default' => null, 'length' => 10],
		'_constraints' => [
			'PRIMARY' => ['type' => 'primary', 'columns' => ['id']],
		]
	];

	/**
	 * records property
	 *
	 * @var array
	 */
	public $records = [
		[
			'id' => '1',
			'title' => 'First Article',
			'user_id' => '1'
		],
		[
			'id' => '2',
			'title' => 'Second Article',
			'user_id' => '2'
		],
		[
			'id' => '3',
			'title' => 'Third Article',
			'user_id' => '3'
		]
	];

}

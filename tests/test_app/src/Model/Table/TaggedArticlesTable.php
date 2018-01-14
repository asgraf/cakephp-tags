<?php

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * @mixin \Tags\Model\Behavior\TaggableBehavior
 */
class TaggedArticlesTable extends Table {

	/**
	 * @param array $config
	 * @return void
	 */
	public function initialize(array $config) {
		$this->addBehavior('Tags.Taggable');
	}

}

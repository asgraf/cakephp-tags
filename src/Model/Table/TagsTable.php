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

namespace Tags\Model\Table;

use Exception;
use Tags\Model\Table\TagsAppTable;

/**
 * Tag model
 */
class TagsTable extends TagsAppTable {

				/**
				 * Validation rules
				 *
				 * @var array
				 */
	public $validate = [
		'name' => ['rule' => 'notEmpty'],
		'keyname' => ['rule' => 'notEmpty']
	];

				/**
				 * initialize
				 *
				 * @param array $config Configuration array.
				 * @return void
				 */
	public function initialize(array $config) {
		$this->belongsTo('Tagged', [
			'propertyName' => 'tagged',
			'className' => 'Tags.Tagged',
			'foreignKey' => 'tag_id'
		]);
	}

				/**
				 * Returns the data for a single tag
				 *
				 * @param string|null $keyName Key of tag to return.
				 * @return array Tag array.
				 * @throws \Exception If no matching tag found.
				 */
	public function view($keyName = null) {
		$result = $this->find('first', [
			'conditions' => [
				$this->alias . '.keyname' => $keyName
			]
		]);
		if (empty($result)) {
			throw new Exception(__d('tags', 'Invalid Tag.'));
		}
		return $result;
	}

				/**
				 * Pre-populates the tag table with entered tags
				 *
				 * @param array|null $postData Controller POST data.
				 * @return bool
				 */
	public function add($postData = null) {
		if (isset($postData[$this->alias]['tags'])) {
			$this->addBehavior('Tags.Taggable', [
				'resetBinding' => true,
				'automaticTagging' => false
			]);
			$this->Tag = $this;
			$result = $this->saveTags($postData[$this->alias]['tags'], false, false);
			unset($this->Tag);
			$this->removeBehavior('Tags.Taggable');
			return $result;
		}
	}

				/**
				 * Edits an existing tag, allows only to modify upper/lowercased characters
				 *
				 * @param string|null $tagId Tag UUID.
				 * @param array|null $postData Controller POST data.
				 * @return mixed True on successful save, otherwise POST data as array.
				 * @throws \Exception If no matching tag found.
				 */
	public function edit($tagId = null, $postData = null) {
		$tag = $this->find('first', [
			'contain' => [],
			'conditions' => [
				$this->alias . '.' . $this->primaryKey => $tagId]
		]);

		$this->set($tag);
		if (empty($tag)) {
			throw new Exception(__d('tags', 'Invalid Tag.'));
		}

		if (!empty($postData[$this->alias]['name'])) {
			if (strcasecmp($tag['Tag']['name'], $postData[$this->alias]['name']) !== 0) {
				return false;
			}
			$this->set($postData);
			$result = $this->save(null, true);
			if ($result) {
				$this->data = $result;
				return true;
			}

			return $postData;
		}
	}

}

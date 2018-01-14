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

namespace Tags\Model\Behavior;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\ResultSetInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

/**
 * Taggable Behavior
 */
class TaggableBehavior extends Behavior {

				/**
				 * Default config
				 *
				 * separator                - separator used to enter a lot of tags, comma by default
				 * tagAlias                 - model alias for Tag model
				 * tagClass                 - class name of the table storing the tags
				 * taggedClass              - class name of the HABTM association table between tags and models
				 * field                    - the fieldname that contains the raw tags as string
				 * foreignKey               - foreignKey used in the HABTM association
				 * associationForeignKey    - associationForeignKey used in the HABTM association
				 * automaticTagging         - if set to true you don't need to use saveTags() manually
				 * language                 - only tags in a certain language, string or array
				 * taggedCounter            - true to update the number of times a particular tag was used for a specific record
				 * unsetInAfterFind         - unset 'Tag' results in afterFind
				 * deleteTagsOnEmptyField   - delete associated Tags if field is empty.
				 * resetBinding             - reset the bindModel() calls, default is false.
				 *
				 * @var array
				 */
	protected $_defaultConfig = [
		'separator' => ',',
		'field' => 'tag_list',
		'tagAlias' => 'Tags',
		'tagClass' => 'Tags.Tags',
		'taggedAlias' => 'Tagged',
		'taggedClass' => 'Tags.Tagged',
		'foreignKey' => 'foreign_key',
		'targetForeignKey' => 'tag_id',
		//'bindingKey' => 'id',
		'cacheOccurrence' => true,
		'automaticTagging' => true,
		'unsetInAfterFind' => false,
		'resetBinding' => false,
		'taggedCounter' => false,
		'deleteTagsOnEmptyField' => false,
		'fullClassName' => false,
	];

	/**
	 * Constructor
	 *
	 * @param \Cake\ORM\Table $table The table this behavior is attached to.
	 * @param array $config The settings for this behavior.
	 */
	public function __construct(Table $table, array $config = []) {
		parent::__construct($table, $config);
		$this->_table = $table;
		$this->_config['withModel'] = $this->_config['taggedClass'];
		$this->bindTagAssociations();
	}

				/**
				 * bindTagAssociations
				 *
				 * @return void
				 */
	public function bindTagAssociations() {
		extract($this->_config);

		$this->_table->hasMany($taggedAlias, [
			'propertyName' => 'tagged',
			'className' => $taggedClass
		]);

		$this->_table->belongsToMany($tagAlias, [
			'propertyName' => 'tags',
			'className' => $tagClass,
			'foreignKey' => $foreignKey,
			'targetForeignKey' => $targetForeignKey,
			'bindingKey' => 'xxx',
			'joinTable' => 'tagged',
			'unique' => true,
			'conditions' => [
				$taggedAlias . '.model' => $this->name()
			],
			'fields' => '',
			'strategy' => 'select', // subquery
			'dependent' => true,
			'through' => $this->_table->Tagged,
		]);
	}

	/**
	 * @param $query
	 * @return void
	 */
	public function beforeFind(Event $event, Query $query, ArrayObject $options) {
		$query->formatResults(function ($results) {
			return $results->map(function ($row) {
				if (!$row instanceOf Entity) {
					return $row;
				}

				$field = $this->_config['field'];
				$tagAlias = $this->_config['tagAlias'];
				$unsetInAfterFind = $this->_config['unsetInAfterFind'];

				$row[$field] = $row->tags ? $this->tagArrayToString($row->tags) : '';
				if ($unsetInAfterFind == true) {
					unset($row[$tagAlias]);
				}

				return $row;
			});
		});
	}

	/**
	 * Disassembles the incoming tag string by its separator and identifiers and trims the tags.
	 *
	 * @param string $string Incoming tag string.
	 * @param string $separator Separator character.
	 * @return array Array of 'tags' and 'identifiers', use extract to get both vars out of the array if needed.
	 */
	public function disassembleTags($string = '', $separator = ',') {
		$array = explode($separator, $string);

		$tags = $identifiers = [];
		foreach ($array as $tag) {
			$identifier = null;
			if (strpos($tag, ':') !== false) {
				$t = explode(':', $tag);
				$identifier = trim($t[0]);
				$tag = $t[1];
			}
			$tag = trim($tag);
			if (!empty($tag)) {
				$key = $this->multibyteKey($tag);
				if (empty($tags[$key]) && (empty($identifiers[$key]) || !in_array($identifier, $identifiers[$key]))) {
					$tags[] = ['name' => $tag, 'identifier' => $identifier, 'keyname' => $key];
					$identifiers[$key][] = $identifier;
				}
			}
		}

		return compact('tags', 'identifiers');
	}

	/**
	 * Saves a string of tags.
	 *
	 * @param string $string Comma separeted list of tags to be saved. Tags can contain special tokens called `identifiers´
	 *     to namespace tags or classify them into catageories. A valid string is "foo, bar, cakephp:special". The token
	 *     `cakephp´ will end up as the identifier or category for the tag `special´.
	 * @param mixed $foreignKey The identifier for the record to associate the tags with.
	 * @param bool $update True will remove tags that are not in the $string, false won't do this and just add new tags
	 *      without removing existing tags associated to the current set foreign key.
	 * @return bool
	 */
	public function saveTags($string, $foreignKey, $update = true) {
		if (empty($string) || empty($foreignKey) && $foreignKey !== false) {
			return true;
		}

		$tagAlias = $this->_config['tagAlias'];
		$taggedAlias = $this->_config['taggedAlias'];
		$tagModel = $this->_table->{$tagAlias};

		extract($this->disassembleTags($string, $this->_config['separator']));

		if (empty($tags)) {
			return true;
		}

		$conditions = [];
		foreach ($tags as $tag) {
			$conditions['OR'][] = [
				$tagModel->alias() . '.identifier' => $tag['identifier'],
				$tagModel->alias() . '.keyname' => $tag['keyname'],
			];
		}
		$existingTags = $tagModel->find('all', [
			'contain' => [],
			'conditions' => $conditions,
			'fields' => [
				$tagModel->alias() . '.identifier',
				$tagModel->alias() . '.keyname',
				$tagModel->alias() . '.name',
				$tagModel->alias() . '.id'
			]
		])->toArray();

		if (!empty($existingTags)) {
			$existingTagKeyNames = $existingTagIds = $existingTagIdentifiers = [];
			foreach ($existingTags as $existing) {
				$existingTagKeyNames[] = $existing[$tagAlias]['keyname'];
				$existingTagIds[] = $existing[$tagAlias]['id'];
				$existingTagIdentifiers[$existing[$tagAlias]['keyname']][] = $existing[$tagAlias]['identifier'];
			}
			$newTags = [];
			foreach ($tags as $possibleNewTag) {
				$key = $possibleNewTag['keyname'];
				if (!in_array($key, $existingTagKeyNames)) {
					array_push($newTags, $possibleNewTag);
				} elseif (!empty($identifiers[$key])) {
					$newIdentifiers = array_diff($identifiers[$key], $existingTagIdentifiers[$key]);
					foreach ($newIdentifiers as $identifier) {
						array_push($newTags, array_merge($possibleNewTag, compact('identifier')));
					}
					unset($identifiers[$key]);
				}
			}
		} else {
			$existingTagIds = $alreadyTagged = [];
			$newTags = $tags;
		}

		foreach ($newTags as $key => $newTag) {
			$entity = $tagModel->newEntity($newTag);
			if (!$tagModel->save($entity)) {
				throw new \RuntimeException('Could not save tag: ' . print_r($entity->getErrors()));
			}
			$newTagIds[] = $entity->id;
		}

		if ($foreignKey !== false) {
			if (!empty($newTagIds)) {
				$existingTagIds = array_merge($existingTagIds, $newTagIds);
			}

			$tagged = $tagModel->{$taggedAlias}->find('all', [
				'contain' => [
					//'Tagged'
				],
				'conditions' => [
					$taggedAlias . '.model' => $this->name(),
					$taggedAlias . '.foreign_key' => $foreignKey,
					$taggedAlias . '.language' => Configure::read('Config.language'),
					$taggedAlias . '.tag_id IN' => $existingTagIds],
				'fields' => $taggedAlias . '.tag_id'
			])->toArray();

			$deleteAll = [
				$taggedAlias . '.foreign_key' => $foreignKey,
				$taggedAlias . '.model' => $this->name()];

			if (!empty($tagged)) {
				$alreadyTagged = Hash::extract($tagged, "{n}.tag_id");
				$existingTagIds = array_diff($existingTagIds, $alreadyTagged);
				$deleteAll['NOT'] = [$taggedAlias . '.tag_id IN' => $alreadyTagged];
			}

			$oldTagIds = [];

			if ($update) {
				$oldTagIds = $tagModel->{$taggedAlias}->find('all', [
					'contain' => [
						//'Tagged'
					],
					'conditions' => [
						$taggedAlias . '.model' => $this->name(),
						$taggedAlias . '.foreign_key' => $foreignKey,
						$taggedAlias . '.language' => Configure::read('Config.language')],
					'fields' => 'Tagged.tag_id'
				])->hydrate(false)->toArray();

				$oldTagIds = Hash::extract($oldTagIds, '{n}.tag_id');
				$tagModel->{$taggedAlias}->deleteAll($deleteAll);
			} elseif ($this->_config['taggedCounter'] && !empty($alreadyTagged)) {
				$tagModel->{$taggedAlias}->updateAll(
					['times_tagged' => 'times_tagged + 1'],
					['Tagged.tag_id' => $alreadyTagged]
				);
			}

			foreach ($existingTagIds as $tagId) {
				$data['tag_id'] = $tagId;
				$data['model'] = $this->name();
				$data['foreign_key'] = $foreignKey;
				$data['language'] = Configure::read('Config.language');
				$entity = $tagModel->{$taggedAlias}->newEntity($data);
				if (!$tagModel->{$taggedAlias}->save($entity)) {
					throw new \RuntimeException('Could not save tagged records: ' . print_r($entity->getErrors()));
				}
			}

			//To update occurrence
			if ($this->_config['cacheOccurrence']) {
				$newTagIds = $tagModel->{$taggedAlias}->find('all', [
					'contain' => [
						//'Tagged'
					],
					'conditions' => [
						$taggedAlias . '.model' => $this->name(),
						$taggedAlias . '.foreign_key' => $foreignKey,
						$taggedAlias . '.language' => Configure::read('Config.language')],
					'fields' => 'Tagged.tag_id'
				])->hydrate(false)->toArray();

				if (!empty($newTagIds)) {
					$newTagIds = Hash::extract($newTagIds, '{n}.tag_id');
				}

				$this->cacheOccurrence(array_merge($oldTagIds, $newTagIds));
			}
		}

		return true;
	}

				/**
				 * Cache the weight or occurence of a tag in the tags table
				 *
				 * @param int|string|array $tagIds List of tag UUIDs.
				 * @return void
				 */
	public function cacheOccurrence($tagIds) {
		if (!is_array($tagIds)) {
			$tagIds = [$tagIds];
		}

		foreach ($tagIds as $tagId) {
			$fieldName = Inflector::underscore($this->name()) . '_occurrence';
			$tagModel = $this->_table->{$this->_config['tagAlias']};
			$taggedModel = $tagModel->{$this->_config['taggedAlias']};
			$primaryKey = $tagModel->primaryKey();
			if (is_array($primaryKey)) {
				$primaryKey = array_shift($primaryKey);
			}
			$tag = $taggedModel->get($tagId);

			if ($tagModel->hasField($fieldName)) {
				$tag[$fieldName] = $taggedModel->find('all', [
					'conditions' => [
						'Tagged.tag_id' => $tagId,
						'Tagged.model' => $this->name()
					]
				])->count();
			}

			$tag['occurrence'] = $taggedModel->find('all', [
				'conditions' => [
					'Tagged.tag_id' => $tagId
				]
			])->count();

			$tagModel->saveOrFail($tag, [
				'validate' => false,
				'callbacks' => false
			]);
		}
	}

	/**
	 * Creates a multibyte safe unique key.
	 *
	 * @param string|null $string Tag name string.
	 * @return string Multibyte safe key string.
	 */
	public function multibyteKey($string = null) {
		$str = mb_strtolower($string);
		$str = preg_replace('/\xE3\x80\x80/', ' ', $str);
		$str = str_replace(['_', '-'], '', $str);
		$str = preg_replace('#[:\#\*"()~$^{}`@+=;,<>!&%\.\]\/\'\\\\|\[]#', "\x20", $str);
		$str = str_replace('?', '', $str);
		$str = trim($str);
		$str = preg_replace('#\x20+#', '', $str);
		return $str;
	}

	/**
	 * Generates comma-delimited string of tag names from tag array(), needed for
	 * initialization of data for text input
	 *
	 * Example usage (only 'Tag.name' field is needed inside of method):
	 * <code>
	 * $this->Blog->hasAndBelongsToMany['Tag']['fields'] = array('name', 'keyname');
	 * $blog = $this->Blog->read(null, 123);
	 * $blog['Blog']['tags'] = $this->Blog->Tags->tagArrayToString($blog['Tag']);
	 * </code>
	 *
	 * @param array|null $data Tag data array to convert to string.
	 * @return string
	 */
	public function tagArrayToString(array $data) {
		if ($data) {
			$tags = [];
			foreach ($data as $tag) {
				if (!empty($tag['identifier'])) {
					$tags[] = $tag['identifier'] . ':' . $tag['name'];
				} else {
					$tags[] = $tag['name'];
				}
			}
			return implode($this->_config['separator'] . ' ', $tags);
		}
		return '';
	}

	/**
	 * afterSave callback.
	 *
	 * @param array $created True if new record, false otherwise.
	 * @param array $options Options array.
	 * @return void
	 */
	public function afterSave(Event $event, \Cake\Datasource\EntityInterface $entity, ArrayObject $options) {
		if (!isset($entity[$this->_config['field']])) {
			return;
		}
		$field = $entity[$this->_config['field']];
		$hasTags = !empty($field);

		if ($this->_config['automaticTagging'] === true && $hasTags) {
			$this->saveTags($field, $entity->id);
		} elseif (!$hasTags && $this->_config['deleteTagsOnEmptyField']) {
			$this->deleteTagged($entity->id);
		}
	}

	/**
	 * Delete associated Tags if record has no tags and deleteTagsOnEmptyField is true.
	 *
	 * @param mixed $id Foreign key of the model, string for UUID or integer.
	 * @return void
	 */
	public function deleteTagged($id) {
		extract($this->_config);
		$tagModel = $this->_table->{$tagAlias};

		$tagModel->{$taggedAlias}->deleteAll(
			[
				$taggedAlias . '.model' => $this->name(),
				$taggedAlias . '.foreign_key' => $id,
			]
		);
	}

	/**
	 * Get name of table.
	 *
	 * @return string Name of table.
	 */
	public function name() {
		$name = $this->config('fullClassName') ? get_class($this->_table) : $this->_table->alias();
		if ($name === 'BoardTopics') {
			return 'BoardTopic';
		}
		if ($name === 'Videos') {
			return 'Video';
		}

		return $name;
	}

}

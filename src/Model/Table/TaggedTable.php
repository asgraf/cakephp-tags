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

use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Tags\Model\Table\TagsAppTable;

/**
 * Tagged model
 */
class TaggedTable extends TagsAppTable {

				/**
				 * Table that is used
				 *
				 * @var string
				 */
	public $useTable = 'tagged';

				/**
				 * initialize
				 *
				 * @param array $config Configuration array.
				 * @return void
				 */
	public function initialize(array $config) {
	    $this->belongsTo('Tags', [
			'propertyName' => 'tags',
			'className' => 'Tags.Tags'
		]);
	}

    /**
     * Returns a tag cloud
     *
     * The result contains a "weight" field which has a normalized size of the tag
     * occurrence set. The min and max size can be set by passing 'minSize" and
     * 'maxSize' to the query. This value can be used in the view to controll the
     * size of the tag font.
     *
     * @todo Ideas to improve this are welcome
     * @param string $state Find state (before or after).
     * @param \Cake\ORM\Query $query Query array.
     * @return array
     * @link https://github.com/CakeDC/tags/issues/10
     */
	public function findCloud($query) {
		// Support old code without the occurrence cache
		if (!$this->Tags->hasField('occurrence')) { // Hash::get($query, 'occurrenceCache') === false
			$groupBy = ['Tagged.tag_id', 'Tags.id', 'Tags.identifier', 'Tags.name', 'Tags.keyname', 'Tags.weight'];
			$fields = $groupBy;
			$fields['occurrence'] = $query->func()->count('*');
		} else {
			// This is related to https://github.com/CakeDC/tags/issues/10 to work around a limitation of postgres
			$field = $this->getDataSource()->fields($this->Tag);
			$field = array_merge($field, $this->getDataSource()->fields($this, null, 'Tagged.tag_id'));
			$fields = 'DISTINCT ' . implode(',', $field);
			$groupBy = null;
		}
		$options = [
			'minSize' => 10,
			'maxSize' => 20,
			//'page' => '',
			//'limit' => '',
			//'order' => '',
			//'joins' => array(),
			//'offset' => '',
			'contain' => 'Tags',
			//'conditions' => array(),
			'fields' => $fields,
			'group' => $groupBy
		];

		//$query['conditions'] = Hash::merge($query['conditions'], array('Tagged.model' => $query['model']));

		if (false) {
			//FIXME
		$query->formatResults(function (\Cake\Collection\CollectionInterface $results) {
			return $results->map(function ($row) {
				/** @var \Tags\Model\Entity\Tagged $row */
				$row['weight'] = '';
				return $row;
			});
		});
		}
dd($query->find('all', $options)->toArray());
		return $query->find('all', $options);

		if ($state == 'after') {
			if (!empty($results) && isset($results[0][0]['occurrence']) || isset($results[0]['Tag']['occurrence'])) {
				// Support old code without the occurrence cache
				if (!$this->Tags->hasField('occurrence')) {
					foreach ($results as $key => $result) {
						$results[$key]['Tag']['occurrence'] = $results[$key][0]['occurrence'];
					}
				} else {
					foreach ($results as $key => $result) {
						$results[$key][0]['occurrence'] = $results[$key]['Tag']['occurrence'];
					}
				}

				//static::calculateWeights();
			}
			return $results;
		}
	}

    /**
     * @param array $entities
     * @param array $config
     *
     * @return array
     */
	public static function calculateWeights(array $entities, array $config = []) {
		$config += [
			'minSize' => 10,
			'maxSize' => 20,
		];

		$weights = Hash::extract($entities, '{n}.occurrence');
		$maxWeight = max($weights);
		$minWeight = min($weights);

		$spread = $maxWeight - $minWeight;
		if ($spread == 0) {
			$spread = 1;
		}

		foreach ($entities as $key => $result) {
			$size = $config['minSize'] + (
					($result['occurrence'] - $minWeight) * (
						($config['maxSize'] - $config['minSize']) / ($spread)
					)
				);
			$entities[$key]['weight'] = ceil($size);
		}

		return $entities;
	}

				/**
				 * Find all the Model entries tagged with a given tag
				 *
				 * The query must contain a Model name, and can contain a 'by' key with the Tag keyname to filter the results
				 * <code>
				 * $this->Article->Tagged->find('tagged', array(
				 *      'by' => 'cakephp',
				 *      'model' => 'Article'));
				 * </code
				 *
				 * @todo Find a way to populate the "magic" field Article.tags
				 * @param string $state Find state (before or after).
				 * @param array $query Query array.
				 * @param array $results Results array.
				 * @return mixed Query array if state is before, array of results or integer (count) if state is after
				 */
	public function findTagged($query, $results = []) {
	    return $query;

		if (isset($query['model']) && $Model = TableRegistry::get($query['model'])) {
			$this->addAssociations([
				'belongsTo' => [
					$Model->alias() => [
						'className' => $Model->name,
						'foreignKey' => 'foreign_key',
						'type' => 'INNER',
						'conditions' => [
							$this->alias() . '.model' => $Model->alias()]]]]);

			if (isset($query['operation']) && $query['operation'] == 'count') {
				$query['fields'] = "COUNT(DISTINCT $Model->alias.$Model->primaryKey)";
				$this->Behaviors->Containable->setup($this, ['autoFields' => false]);
			} else {
				if ($query['fields'] === null) {
					$query['fields'][] = 'DISTINCT ' . implode(',', $this->getDataSource()->fields($Model));
				} else {
					$distinctFields = implode(',', $this->getDataSource()->fields($Model));
					array_unshift($query['fields'], 'DISTINCT ' . $distinctFields);
				}
			}

			if (!empty($query['by'])) {
				$query['conditions'][] = [
					$this->Tags->alias . '.keyname' => $query['by']];
			}
		}

		return $query;
	}

}

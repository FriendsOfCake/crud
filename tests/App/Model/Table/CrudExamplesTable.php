<?php
namespace Crud\Test\App\Model\Table;

use Cake\ORM\Query;

/**
 * Crud Example Model
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class CrudExamplesTable extends \Cake\ORM\Table {

	public $alias = 'CrudExamples';

	public $findMethods = array(
		'published' => true,
		'unpublished' => true,
		'firstPublished' => true,
		'firstUnpublished' => true,
	);

	public function initialize(array $config) {
		$this->table('posts');
	}

	protected function _findPublished(Query $query, array $options) {
		$query->where(['published' => 'Y']);
		return $query;
	}

	protected function _findUnpublished(Query $query, array $options) {
		$query->where(['published' => 'N']);
	}

}

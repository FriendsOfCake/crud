<?php
namespace Crud\Traits;

use Crud\Event\Subject;

trait FindMethodTrait {

/**
 * Change the find() method
 *
 * If `$method` is NULL the current value is returned
 * else the `findMethod` is changed
 *
 * @param mixed $method
 * @return mixed
 */
	public function findMethod($method = null) {
		if ($method === null) {
			return $this->config('findMethod');
		}

		return $this->config('findMethod', $method);
	}

/**
 * Find a record from the ID
 *
 * @param string $id
 * @param string $findMethod
 * @return array
 */
	protected function _findRecord($id, Subject $subject) {
		$repository = $this->_repository();

		$query = $repository->find($this->findMethod());
		$query->where([current($query->aliasField($repository->primaryKey())) => $id]);

		$subject->set([
			'repository' => $repository,
			'query' => $query
		]);

		$this->_trigger('beforeFind', $subject);
		$item = $query->first();

		if (!$item) {
			return $this->_notFound($id, $subject);
		}

		$subject->set(['item' => $item, 'success' => true]);
		$this->_trigger('afterFind', $subject);
		return $item;
	}

/**
 * Throw exception if a record is not found
 *
 * @throws Exception
 * @param string $id
 * @return void
 */
	protected function _notFound($id, Subject $subject) {
		$subject->set(['success' => false]);
		$this->_trigger('recordNotFound', $subject);

		$message = $this->message('recordNotFound', compact('id'));
		$exceptionClass = $message['class'];
		throw new $exceptionClass($message['text'], $message['code']);
	}

}

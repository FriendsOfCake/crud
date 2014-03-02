<?php
namespace Crud\Test\TestCase\Action;

use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class DeleteTest extends TestCase {

/**
 * test delete
 *
 * test the best-case flow
 *
 * @covers Crud\Action\Delete::_handle
 * @return void
 */
	public function testHandleSuccess() {
		$Action = $this
			->getMockBuilder('\Crud\Action\Delete')
			->disableOriginalConstructor()
			->setMethods([
					'_subject', '_findRecord', '_trigger',
					'_repository', '_success', '_error', '_redirect'
				])
			->getMock();

		$Subject = $this
			->getMockBuilder('\Crud\Event\Subject')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$Entity = $this
			->getMockBuilder('\Cake\ORM\Entity')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$Event = $this
			->getMockBuilder('\Cake\Event\Event')
			->disableOriginalConstructor()
			->setMethods(['isStopped'])
			->getMock();

		$Repository = $this
			->getMockBuilder('\Cake\ORM\Table')
			->disableOriginalConstructor()
			->setMethods(['delete'])
			->getMock();

		$Action
			->expects($this->next($Action))
			->method('_subject')
			->will($this->returnValue($Subject));
		$Action
			->expects($this->next($Action))
			->method('_findRecord')
			->with(1337, $Subject)
			->will($this->returnValue($Entity));
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeDelete', $Subject)
			->will($this->returnValue($Event));
		$Event
			->expects($this->next($Event))
			->method('isStopped')
			->will($this->returnValue(false));
		$Action
			->expects($this->next($Action))
			->method('_repository')
			->will($this->returnValue($Repository));
		$Repository
			->expects($this->next($Repository))
			->method('delete')
			->with($Entity)
			->will($this->returnValue(true));
		$Action
			->expects($this->next($Action))
			->method('_success')
			->with($Subject);
		$Action
			->expects($this->next($Action))
			->method('_redirect')
			->with($Subject, ['action' => 'index']);

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', [1337], $Action);
	}

/**
 * test delete error
 *
 * test the best-case flow
 *
 * @covers Crud\Action\Delete::_handle
 * @return void
 */
	public function testHandleError() {
		$Action = $this
			->getMockBuilder('\Crud\Action\Delete')
			->disableOriginalConstructor()
			->setMethods([
					'_subject', '_findRecord', '_trigger',
					'_repository', '_success', '_error', '_redirect'
				])
			->getMock();

		$Subject = $this
			->getMockBuilder('\Crud\Event\Subject')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$Entity = $this
			->getMockBuilder('\Cake\ORM\Entity')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$Event = $this
			->getMockBuilder('\Cake\Event\Event')
			->disableOriginalConstructor()
			->setMethods(['isStopped'])
			->getMock();

		$Repository = $this
			->getMockBuilder('\Cake\ORM\Table')
			->disableOriginalConstructor()
			->setMethods(['delete'])
			->getMock();

		$Action
			->expects($this->next($Action))
			->method('_subject')
			->will($this->returnValue($Subject));
		$Action
			->expects($this->next($Action))
			->method('_findRecord')
			->with(1337, $Subject)
			->will($this->returnValue($Entity));
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeDelete', $Subject)
			->will($this->returnValue($Event));
		$Event
			->expects($this->next($Event))
			->method('isStopped')
			->will($this->returnValue(false));
		$Action
			->expects($this->next($Action))
			->method('_repository')
			->will($this->returnValue($Repository));
		$Repository
			->expects($this->next($Repository))
			->method('delete')
			->with($Entity)
			->will($this->returnValue(false));
		$Action
			->expects($this->next($Action))
			->method('_error')
			->with($Subject);
		$Action
			->expects($this->next($Action))
			->method('_redirect')
			->with($Subject, ['action' => 'index']);

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', [1337], $Action);
	}

/**
 * testHandleStoppedByEvent
 *
 * test the behavior when the beforeDelete callback
 * stops the event
 *
 * @covers Crud\Action\Delete::_handle
 * @return void
 */
	public function testHandleStoppedByEvent() {
		$Action = $this
			->getMockBuilder('\Crud\Action\Delete')
			->disableOriginalConstructor()
			->setMethods([
					'_subject', '_findRecord', '_trigger',
					'_repository', '_success', '_error', '_redirect', '_stopped'
				])
			->getMock();

		$Subject = $this
			->getMockBuilder('\Crud\Event\Subject')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$Entity = $this
			->getMockBuilder('\Cake\ORM\Entity')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$Event = $this
			->getMockBuilder('\Cake\Event\Event')
			->disableOriginalConstructor()
			->setMethods(['isStopped'])
			->getMock();

		$Repository = $this
			->getMockBuilder('\Cake\ORM\Table')
			->disableOriginalConstructor()
			->setMethods(['delete'])
			->getMock();

		$Action
			->expects($this->next($Action))
			->method('_subject')
			->will($this->returnValue($Subject));
		$Action
			->expects($this->next($Action))
			->method('_findRecord')
			->with(1337, $Subject)
			->will($this->returnValue($Entity));
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeDelete', $Subject)
			->will($this->returnValue($Event));
		$Event
			->expects($this->next($Event))
			->method('isStopped')
			->will($this->returnValue(true));
		$Action
			->expects($this->never())
			->method('_repository');
		$Action
			->expects($this->next($Action))
			->method('_stopped')
			->with($Subject);

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', [1337], $Action);
	}

}

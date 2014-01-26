<?php
namespace Crud\Test\TestCase\Action;

use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ViewTest extends TestCase {

/**
 * test_getGet
 *
 * Test that calling HTTP GET on an view action
 * will trigger the appropriate events
 *
 * This test assumes the best possible case
 *
 * The id provided, it's correct and it's in the db
 *
 * @covers \Crud\Action\View::_get
 * @return void
 */
	public function test_getGet() {
		$Action = $this
			->getMockBuilder('\Crud\Action\View')
			->disableOriginalConstructor()
			->setMethods([
				'_validateId', '_subject', '_findRecord',
				'_controller', '_trigger', 'viewVar'
			])
			->getMock();
		$Subject = $this
			->getMockBuilder('\Crud\Event\Subject')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();
		$Entity = $this
			->getMockBuilder('\Cake\ORM\Table')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();
		$Controller = $this
			->getMockBuilder('\Cake\Controller\Controller')
			->disableOriginalConstructor()
			->setMethods(['set'])
			->getMock();

		$Action
			->expects($this->next($Action))
			->method('_validateId')
			->will($this->returnValue(true));
		$Action
			->expects($this->next($Action))
			->method('_subject')
			->will($this->returnValue($Subject));
		$Action
			->expects($this->next($Action))
			->method('viewVar')
			->will($this->returnValue('Post'));
		$Action
			->expects($this->next($Action))
			->method('_findRecord')
			->will($this->returnCallback(function() use ($Entity, $Subject) {
				$Subject->set(['success' => true, 'item' => $Entity]);
				return $Entity;
			}));
		$Action
			->expects($this->next($Action))
			->method('_controller')
			->will($this->returnValue($Controller));
		$Controller
			->expects($this->next($Controller))
			->method('set')
			->with(['success' => true, 'Post' => $Entity]);
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeRender', $Subject);

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_get', [1337], $Action);
	}

/**
 * test_getGetInvalidId
 *
 * Test that calling HTTP GET on an view action
 * will trigger the appropriate events
 *
 * This test assumes that the id for the view
 * action does not exist in the database
 *
 * @covers \Crud\Action\View::_get
 * @return void
 */
	public function test_getGetInvalidId() {
		$Action = $this
			->getMockBuilder('\Crud\Action\View')
			->disableOriginalConstructor()
			->setMethods(['_validateId', '_subject'])
			->getMock();
		$Action
			->expects($this->once())
			->method('_validateId')
			->with(1337)
			->will($this->returnValue(false));
		$Action
			->expects($this->never())
			->method('_subject');

		$this->setReflectionClassInstance($Action);
		$result = $this->callProtectedMethod('_get', [1337], $Action);
		$this->assertFalse($result);
	}

}

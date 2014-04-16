<?php
namespace Crud\Test\TestCase\Action;

use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class IndexTest extends TestCase {

/**
 * Tests that calling index action will paginate the main model
 *
 * @covers \Crud\Action\Index::_handle
 * @return void
 */
	public function test_get() {
		$Controller = $this
			->getMockBuilder('\Cake\Controller\Controller')
			->disableOriginalConstructor()
			->setMethods(['paginate', 'set'])
			->getMock();
		$Subject = $this
			->getMockBuilder('\Crud\Event\Subject')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();
		$Action = $this
			->getMockBuilder('\Crud\Action\Index')
			->disableOriginalConstructor()
			->setMethods(['_subject', '_controller', '_trigger'])
			->getMock();

		$Action
			->expects($this->next($Action))
			->method('_subject')
			->will($this->returnValue($Subject));
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforePaginate', $Subject);
		$Action
			->expects($this->next($Action))
			->method('_controller')
			->will($this->returnValue($Controller));
		$Controller
			->expects($this->next($Controller))
			->method('paginate')
			->will($this->returnValue([1, 2, 3]));
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('afterPaginate', $Subject);
		$Controller
			->expects($this->next($Controller))
			->method('set')
			->with(['success' => true, 'posts' => [1,2,3]]);
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeRender', $Subject);

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_handle', array(), $Action);
	}

}

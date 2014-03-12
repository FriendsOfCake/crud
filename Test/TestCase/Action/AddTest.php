<?php
namespace Crud\Test\TestCase\Action;

use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class AddTest extends TestCase {

/**
 * Test the normal HTTP GET flow of _get
 *
 * @covers \Crud\Action\Add::_get
 * @return void
 */
	public function testActionGet() {
		$Request = $this->getMock('\Cake\Network\Request');
		$Entity = $this->getMock('\Cake\ORM\Entity');

		$Action = $this
			->getMockBuilder('\Crud\Action\Add')
			->disableOriginalConstructor()
			->setMethods(['_request', '_entity', '_trigger'])
			->getMock();

		$Action
			->expects($this->next($Action))
			->method('_request')
			->will($this->returnValue($Request));

		$Action
			->expects($this->next($Action))
			->method('_entity')
			->will($this->returnValue($Entity));

		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeRender', ['success' => true]);

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_get', [], $Action);

		$this->assertSame($Entity, $Request->data);
	}

/**
 * Test that calling HTTP POST on an add action
 * will trigger multiple events on success
 *
 * @covers \Crud\Action\Add::_post
 * @covers \Crud\Action\Add::_getEntity
 * @covers \Crud\Action\Add::_success
 * @return void
 */
	public function testActionPostSuccess() {
		$Request = $this->getMock('\Cake\Network\Request');
		$Request->data = ['name' => 'Hello World'];

		$Subject = $this->getMock('\Crud\Event\Subject', null);

		$Repository = $this
			->getMockBuilder('\Cake\ORM\Table')
			->disableOriginalConstructor()
			->setMethods(['save'])
			->getMock();

		$Entity = $this
			->getMockBuilder('\Cake\ORM\Entity')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$Action = $this
			->getMockBuilder('\Crud\Action\Add')
			->disableOriginalConstructor()
			->setMethods([
				'_request', '_entity', '_trigger',
				'_subject', '_repository', 'setFlash',
				'_redirect', '_getResourceName'
			])
			->getMock();

		$Action
			->expects($this->next($Action))
			->method('_entity')
			->will($this->returnValue($Entity));
		$Action
			->expects($this->next($Action))
			->method('_request')
			->will($this->returnValue($Request));
		$Action
			->expects($this->next($Action))
			->method('_subject')
			->will($this->returnValue($Subject));
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeSave', $Subject);
		$Action
			->expects($this->next($Action))
			->method('_repository')
			->will($this->returnValue($Repository));
		$Repository
			->expects($this->next($Repository))
			->method('save')
			->with($Entity, ['validate' => true, 'atomic' => true])
			->will($this->returnValue(true));
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('afterSave', $Subject);
		$Action
			->expects($this->next($Action))
			->method('setFlash')
			->with('success', $Subject);
		$Action
			->expects($this->next($Action))
			->method('_redirect')
			->with($Subject, ['action' => 'index']);

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_post', [], $Action);

		$this->assertTrue($Subject->success);
		$this->assertTrue($Subject->created);
		$this->assertSame($Entity, $Subject->item);
	}

/**
 * Test that calling HTTP POST on an add action
 * will trigger multiple events on error
 *
 * @covers \Crud\Action\Add::_post
 * @covers \Crud\Action\Add::_getEntity
 * @covers \Crud\Action\Add::_error
 * @return void
 */
	public function testActionPostError() {
		$Request = $this->getMock('\Cake\Network\Request');
		$Request->data = ['name' => 'Hello World'];

		$Subject = $this->getMock('\Crud\Event\Subject', null);

		$Repository = $this
			->getMockBuilder('\Cake\ORM\Table')
			->disableOriginalConstructor()
			->setMethods(['save'])
			->getMock();

		$Entity = $this
			->getMockBuilder('\Cake\ORM\Entity')
			->disableOriginalConstructor()
			->setMethods(['set'])
			->getMock();

		$Action = $this
			->getMockBuilder('\Crud\Action\Add')
			->disableOriginalConstructor()
			->setMethods([
				'_request', '_entity', '_trigger',
				'_subject', '_repository', 'setFlash',
				'_redirect'
			])
			->getMock();

		$Action
			->expects($this->next($Action))
			->method('_entity')
			->will($this->returnValue($Entity));
		$Action
			->expects($this->next($Action))
			->method('_request')
			->will($this->returnValue($Request));
		$Entity
			->expects($this->next($Entity))
			->method('set')
			->with($Request->data);
		$Action
			->expects($this->next($Action))
			->method('_subject')
			->will($this->returnValue($Subject));
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeSave', $Subject);
		$Action
			->expects($this->next($Action))
			->method('_repository')
			->will($this->returnValue($Repository));
		$Repository
			->expects($this->next($Repository))
			->method('save')
			->with($Entity, ['validate' => true, 'atomic' => true])
			->will($this->returnValue(false));
		$Action
			->expects($this->next($Action))
			->method('setFlash')
			->with('error', $Subject);
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('afterSave', $Subject);
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeRender', $Subject);


		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_post', [], $Action);

		$this->assertFalse($Subject->success);
		$this->assertFalse($Subject->created);
		$this->assertSame($Entity, $Subject->item);
	}

/**
 * Check that _PUT maps to _POST
 *
 * @covers \Crud\Action\Add::_put
 * @return void
 */
	public function testPutMapsToPost() {
		$Action = $this
			->getMockBuilder('\Crud\Action\Add')
			->disableOriginalConstructor()
			->setMethods(['_post'])
			->getMock();

		$Action
			->expects($this->next($Action))
			->method('_post');

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_put', [], $Action);
	}

}

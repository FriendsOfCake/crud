<?php
namespace Crud\Test\TestCase\Action;

use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class EditTest extends TestCase {

/**
 * Test the normal HTTP GET flow of _get
 *
 * @return void
 */
	public function testActionGet() {
		$Action = $this
			->getMockBuilder('\Crud\Action\Edit')
			->disableOriginalConstructor()
			->setMethods(['_subject', '_request', '_findRecord', '_trigger'])
			->getMock();
		$Subject = $this
			->getMockBuilder('\Crud\Event\Subject')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();
		$Request = $this
			->getMockBuilder('\Cake\Network\Request')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();
		$Entity = $this
			->getMockBuilder('\Cake\ORM\Entity')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$Action
			->expects($this->next($Action))
			->method('_subject')
			->will($this->returnValue($Subject));
		$Action
			->expects($this->next($Action))
			->method('_request')
			->will($this->returnValue($Request));
		$Action
			->expects($this->next($Action))
			->method('_findRecord')
			->with(1337, $Subject)
			->will($this->returnValue($Entity));
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeRender', $Subject);

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_get', [1337], $Action);

		$this->assertSame($Entity, $Request->data);
	}

/**
 * Test that calling HTTP PUT on an edit action
 * will trigger the appropriate events and try to
 * update a record in the database
 *
 * This test assumes the best possible case
 * The id provided, it's correct and it's in the db
 *
 * @return void
 */
	public function testActionPutSuccess() {
		$Action = $this
			->getMockBuilder('\Crud\Action\Edit')
			->disableOriginalConstructor()
			->setMethods([
				'_subject', '_request', '_findRecord',
				'_trigger', '_success', '_error', '_repository'
			])
			->getMock();
		$Subject = $this
			->getMockBuilder('\Crud\Event\Subject')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();
		$Request = $this
			->getMockBuilder('\Cake\Network\Request')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();
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
			->method('_request')
			->will($this->returnValue($Request));
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeSave', $Subject);
		$Action
			->expects($this->next($Action))
			->method('_repository')
			->will($this->returnValue($Repository));
		$Repository
			->expects($this->once())
			->method('save')
			->with($Entity, ['atomic' => true, 'validate' => true])
			->will($this->returnValue(true));
		$Action
			->expects($this->next($Action))
			->method('_success')
			->with($Subject);
		$Action
			->expects($this->never())
			->method('_error');

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_put', [1337], $Action);

		$this->assertSame($Entity, $Request->data);
	}

/**
 * Test that calling HTTP PUT on an edit action
 * will trigger the appropriate events and try to
 * update a record in the database
 *
 * This test assumes the saveAssociated() call fails
 * The id provided, it's correct and it's in the db
 *
 * @return void
 */
	public function testActionPutSaveError() {
		$Action = $this
			->getMockBuilder('\Crud\Action\Edit')
			->disableOriginalConstructor()
			->setMethods([
				'_subject', '_request', '_findRecord',
				'_trigger', '_success', '_error', '_repository'
			])
			->getMock();
		$Subject = $this
			->getMockBuilder('\Crud\Event\Subject')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();
		$Request = $this
			->getMockBuilder('\Cake\Network\Request')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();
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
			->method('_request')
			->will($this->returnValue($Request));
		$Action
			->expects($this->next($Action))
			->method('_trigger')
			->with('beforeSave', $Subject);
		$Action
			->expects($this->next($Action))
			->method('_repository')
			->will($this->returnValue($Repository));
		$Repository
			->expects($this->once())
			->method('save')
			->with($Entity, ['atomic' => true, 'validate' => true])
			->will($this->returnValue(false));
		$Action
			->expects($this->next($Action))
			->method('_error')
			->with($Subject);
		$Action
			->expects($this->never())
			->method('_success');

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_put', [1337], $Action);

		$this->assertSame($Entity, $Request->data);
	}

/**
 * Test that calling HTTP POST on an edit action
 * will trigger the appropriate events and try to
 * update a record in the database
 *
 * This test assumes the best possible case
 * The id provided, it's correct and it's in the db
 *
 * @return void
 */
	public function testActionPost() {
		$Action = $this
			->getMockBuilder('\Crud\Action\Edit')
			->disableOriginalConstructor()
			->setMethods(['_put'])
			->getMock();

		$Action
			->expects($this->next($Action))
			->method('_put')
			->with(1337);

		$this->setReflectionClassInstance($Action);
		$this->callProtectedMethod('_post', [1337], $Action);
	}

}

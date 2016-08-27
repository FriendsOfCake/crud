<?php
namespace Crud\TestCase\Action;

use Cake\ORM\TableRegistry;
use Crud\Event\Subject;
use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class BaseActionTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->Request = $this->getMockBuilder('Cake\Network\Request')
            ->getMock();
        $this->Controller = $this->getMockBuilder('Cake\Controller\Controller')
            ->setMethods(['set'])
            ->setConstructorArgs([
                $this->Request,
                new \Cake\Network\Response,
                'CrudExamples',
                \Cake\Event\EventManager::instance()
            ])
            ->getMock();
        $this->Registry = $this->Controller->components();
        $this->Crud = $this->getMockBuilder('Crud\Controller\Component\CrudComponent')
            ->setConstructorArgs([$this->Registry])
            ->setMethods(['foobar'])
            ->getMock();
        $this->Controller->Crud = $this->Crud;
        $this->Controller->modelClass = 'CrudExamples';
        $this->Controller->CrudExamples = \Cake\ORM\TableRegistry::get('Crud.CrudExamples');
        $this->Controller->CrudExamples->alias('MyModel');

        $this->actionClassName = $this->getMockClass('Crud\Action\BaseAction', ['_handle']);
        $this->ActionClass = new $this->actionClassName($this->Controller);
        $this->_configureAction($this->ActionClass);
    }

    public function tearDown()
    {
        parent::tearDown();
        TableRegistry::clear();
        unset(
            $this->Crud,
            $this->Request,
            $this->Registry,
            $this->Controller,
            $this->ActionClass
        );
    }

    protected function _configureAction($action)
    {
        $action->config([
            'action' => 'add',
            'enabled' => true,
            'findMethod' => 'first',
            'view' => null,
            'relatedModels' => true,
            'validateId' => null,
            'saveOptions' => [
                'validate' => 'first',
                'atomic' => true
            ],
            'serialize' => [
                'success',
                'data'
            ]
        ]);
    }

    /**
     * Test that it's possible to override all
     * configuration settings through the __constructor()
     *
     * @return void
     */
    public function testOverrideAllDefaults()
    {
        $expected = [
            'enabled' => false,
            'findMethod' => 'any',
            'view' => 'my_view',
            'relatedModels' => ['Tag'],
            'validateId' => 'id',
            'saveOptions' => [
                'validate' => 'never',
                'atomic' => false
            ],
            'serialize' => [
                'yay',
                'ney'
            ],
            'action' => 'add'
        ];

        $ActionClass = new $this->actionClassName($this->Controller, $expected);
        // This is injected by the CrudAction, not technically a setting
        $expected['action'] = 'add';
        $actual = $ActionClass->config();
        $this->assertEquals($expected, $actual, 'It was not possible to override all default settings.');
    }

    /**
     * Test that we get the expected events
     *
     * @return void
     */
    public function testImplementedEvents()
    {
        $expected = ['Crud.beforeRender' => [['callable' => [$this->ActionClass, 'publishSuccess']]]];
        $actual = $this->ActionClass->implementedEvents();
        $this->assertEquals($expected, $actual, 'The CrudAction implements events');
    }

    /**
     * Test that an enabled action will call _handle
     *
     * @return void
     */
    public function testEnabledActionWorks()
    {
        $Request = $this->getMockBuilder('Cake\Network\Request')
            ->setMethods(['method'])
            ->getMock();
        $Request->action = 'add';
        $Request
            ->expects($this->once())
            ->method('method')
            ->will($this->returnValue('GET'));

        $Action = $this->getMockBuilder('Crud\Action\BaseAction')
            ->setMethods(['_request', '_get'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();
        $Action
            ->expects($this->any())
            ->method('_request')
            ->with()
            ->will($this->returnValue($Request));
        $Action
            ->expects($this->once())
            ->method('_get', '_handle was never called on a enabled action')
            ->will($this->returnValue(true));

        $this->_configureAction($Action);
        $Action->config('action', 'add');

        $expected = true;
        $actual = $Action->config('enabled');
        $this->assertSame($expected, $actual, 'The action is not enabled by default');

        $expected = true;
        $actual = $Action->handle();
        $this->assertSame($expected, $actual, 'Calling handle on a disabled action did not return null');
    }

    /**
     * testDisable
     *
     * Test that calling disable() on the action object
     * disables the action
     *
     * @return void
     */
    public function testDisable()
    {
        $i = 0;

        $Action = $this->getMockBuilder('Crud\Action\BaseAction')
            ->setMethods(['config'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();
        $Action
            ->expects($this->at($i++))
            ->method('config', 'enabled was not changed to false by config()')
            ->with('enabled', false);

        $Action->disable();
    }

    /**
     * testEnable
     *
     * Test that calling enable() on the action object
     * enables the action
     *
     * @return void
     */
    public function testEnable()
    {
        $i = 0;

        $Action = $this->getMockBuilder('Crud\Action\BaseAction')
            ->setMethods(['config'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();
        $Action
            ->expects($this->at($i++))
            ->method('config', 'enabled was not changed to false by config()')
            ->with('enabled', true);

        $Action->enable();
    }

    /**
     * Test that setFlash triggers the correct methods
     *
     * @return void
     */
    public function testSetFlash()
    {
        $data = [
            'element' => 'default',
            'params' => [
                'class' => 'message success',
                'original' => 'Ahoy'
            ],
            'key' => 'custom',
            'type' => 'add.success',
            'name' => 'test',
            'text' => 'Ahoy',
        ];

        $Subject = new Subject();

        $this->Controller->Crud = $this->getMockBuilder('Crud\Controller\Component\CrudComponent')
            ->setMethods(['trigger'])
            ->setConstructorArgs([$this->Registry])
            ->getMock();
        $this->Controller->Crud
            ->expects($this->once())
            ->method('trigger')
            ->with('setFlash', $Subject)
            ->will($this->returnValue(new \Cake\Event\Event('Crud.setFlash')));

        $this->Controller->Flash = $this->getMockBuilder('Cake\Controller\Component\FlashComponent')
            ->setMethods(['set'])
            ->setConstructorArgs([$this->Registry])
            ->getMock();
        $this->Controller->Flash
            ->expects($this->once())
            ->method('set')
            ->with(
                $data['text'],
                [
                    'element' => $data['element'],
                    'params' => $data['params'],
                    'key' => $data['key'],
                ]
            );

        $this->ActionClass->config('name', 'test');
        $this->ActionClass->config('messages', [
            'success' => ['text' => 'Ahoy', 'key' => 'custom']
        ]);
        $this->ActionClass->setFlash('success', $Subject);
    }

    /**
     * Test default saveAll options works when modified
     *
     * @return void
     */
    public function testGetSaveAllOptionsDefaults()
    {
        $CrudAction = $this->ActionClass;

        $expected = [
            'validate' => 'first',
            'atomic' => true
        ];
        $actual = $CrudAction->config('saveOptions');
        $this->assertEquals($expected, $actual);

        $CrudAction->config('saveOptions.atomic', true);
        $expected = [
            'validate' => 'first',
            'atomic' => true
        ];
        $actual = $CrudAction->config('saveOptions');
        $this->assertEquals($expected, $actual);

        $CrudAction->config('saveOptions', [
            'fieldList' => ['hello']
        ]);
        $expected = [
            'validate' => 'first',
            'atomic' => true,
            'fieldList' => ['hello']
        ];
        $actual = $CrudAction->config('saveOptions');
        $this->assertEquals($expected, $actual);
    }

    /**
     * testEmptyMessage
     *
     * @expectedException Exception
     * @expectedExceptionMessage Missing message type
     */
    public function testEmptyMessage()
    {
        $this->ActionClass->message(null);
    }

    /**
     * testUndefinedMessage
     *
     * @expectedException Exception
     * @expectedExceptionMessage Invalid message type "not defined"
     */
    public function testUndefinedMessage()
    {
        $this->ActionClass->message('not defined');
    }

    /**
     * testBadMessageConfig
     *
     * @expectedException Exception
     * @expectedExceptionMessage Invalid message config for "badConfig" no text key found
     */
    public function testBadMessageConfig()
    {
        $this->Crud->config('messages.badConfig', ['foo' => 'bar']);
        $this->ActionClass->message('badConfig');
    }

    /**
     * testInheritedSimpleMessage
     *
     * @return void
     */
    public function testInheritedSimpleMessage()
    {
        $this->Crud->config('messages.simple', 'Simple message');

        $expected = [
            'element' => 'default',
            'params' => [
                'class' => 'message simple',
                'original' => 'Simple message'
            ],
            'key' => 'flash',
            'type' => 'add.simple',
            'name' => 'my model',
            'text' => 'Simple message'
        ];
        $actual = $this->ActionClass->message('simple');
        $this->assertEquals($expected, $actual);
    }

    /**
     * testOverridenSimpleMessage
     *
     * @return void
     */
    public function testOverridenSimpleMessage()
    {
        $this->Crud->config('messages.simple', 'Simple message');
        $this->ActionClass->config('messages.simple', 'Overridden message');

        $expected = [
            'element' => 'default',
            'params' => [
                'class' => 'message simple',
                'original' => 'Overridden message'
            ],
            'key' => 'flash',
            'type' => 'add.simple',
            'name' => 'my model',
            'text' => 'Overridden message'
        ];
        $actual = $this->ActionClass->message('simple');
        $this->assertEquals($expected, $actual);
    }

    /**
     * testSimpleMessage
     *
     * @return void
     */
    public function testSimpleMessage()
    {
        $this->ActionClass->config('messages.simple', 'Simple message');

        $expected = [
            'element' => 'default',
            'params' => [
                'class' => 'message simple',
                'original' => 'Simple message'
            ],
            'key' => 'flash',
            'type' => 'add.simple',
            'name' => 'my model',
            'text' => 'Simple message'
        ];
        $actual = $this->ActionClass->message('simple');
        $this->assertEquals($expected, $actual);
    }

    /**
     * testSimpleMessageWithPlaceholders
     *
     * @return void
     */
    public function testSimpleMessageWithPlaceholders()
    {
        $this->Crud->config('messages.simple', 'Simple message with id "{id}"');

        $expected = [
            'element' => 'default',
            'params' => [
                'class' => 'message simple',
                'original' => 'Simple message with id "{id}"'
            ],
            'key' => 'flash',
            'type' => 'add.simple',
            'name' => 'my model',
            'text' => 'Simple message with id "123"'
        ];
        $actual = $this->ActionClass->message('simple', ['id' => 123]);
        $this->assertEquals($expected, $actual);
    }

    /**
     * testInvalidIdMessage
     *
     * @return void
     */
    public function testInvalidIdMessage()
    {
        $expected = [
            'code' => 400,
            'class' => 'Cake\Network\Exception\BadRequestException',
            'element' => 'default',
            'params' => [
                'class' => 'message invalidId',
                'original' => 'Invalid id'
            ],
            'key' => 'flash',
            'type' => 'add.invalidId',
            'name' => 'my model',
            'text' => 'Invalid id'
        ];
        $actual = $this->ActionClass->message('invalidId');
        $this->assertEquals($expected, $actual);
    }

    /**
     * testMessageNotFound
     *
     * @return void
     */
    public function testRecordNotFoundMessage()
    {
        $expected = [
            'code' => 404,
            'class' => 'Cake\Network\Exception\NotFoundException',
            'element' => 'default',
            'params' => [
                'class' => 'message recordNotFound',
                'original' => 'Not found'
            ],
            'key' => 'flash',
            'type' => 'add.recordNotFound',
            'name' => 'my model',
            'text' => 'Not found'
        ];
        $actual = $this->ActionClass->message('recordNotFound');
        $this->assertEquals($expected, $actual);
    }

    /**
     * testBadRequestMethodMessage
     *
     * @return void
     */
    public function testBadRequestMethodMessage()
    {
        $expected = [
            'code' => 405,
            'class' => 'Cake\Network\Exception\MethodNotAllowedException',
            'element' => 'default',
            'params' => [
                'class' => 'message badRequestMethod',
                'original' => 'Method not allowed. This action permits only {methods}'
            ],
            'key' => 'flash',
            'type' => 'add.badRequestMethod',
            'name' => 'my model',
            'text' => 'Method not allowed. This action permits only THESE ONES'
        ];
        $actual = $this->ActionClass->message('badRequestMethod', ['methods' => 'THESE ONES']);
        $this->assertEquals($expected, $actual);
    }

    /**
     * testHandle
     *
     * Test that calling handle will invoke _handle
     * when the action is enabbled
     *
     * @return void
     */
    public function testHandle()
    {
        $Action = $this->getMockBuilder('Crud\Action\BaseAction')
            ->setMethods(['_request', '_get', 'config'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();

        $Request = $this->getMockBuilder('Cake\Network\Request')
            ->setMethods(['method'])
            ->getMock();
        $Request
            ->expects($this->once())
            ->method('method')
            ->will($this->returnValue('GET'));

        $i = 0;
        $Action
            ->expects($this->at($i++))
            ->method('config')
            ->with('enabled')
            ->will($this->returnValue(true));
        $Action
            ->expects($this->at($i++))
            ->method('_request')
            ->will($this->returnValue($Request));
        $Action
            ->expects($this->at($i++))
            ->method('_get');

        $Action->handle();
    }

    /**
     * testHandleDisabled
     *
     * Test that calling handle will not invoke _handle
     * when the action is disabled
     *
     * @return void
     */
    public function testHandleDisabled()
    {
        $Action = $this->getMockBuilder('Crud\Action\BaseAction')
            ->setMethods(['_handle', '_get', 'config'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();

        $i = 0;
        $Action
            ->expects($this->at($i++))
            ->method('config')
            ->with('enabled')
            ->will($this->returnValue(false));
        $Action
            ->expects($this->never())
            ->method('_handle');

        $Action->handle();
    }

    /**
     * testGenericHandle
     *
     * Test that calling handle will invoke _handle
     * when the requestType handler is not available
     *
     * @return void
     */
    public function testGenericHandle()
    {
        $Action = $this->getMockBuilder('Crud\Action\BaseAction')
            ->setMethods(['_handle', '_request', 'config'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();

        $Request = $this->getMockBuilder('Cake\Network\Request')
            ->setMethods(['method'])
            ->getMock();
        $Request
            ->expects($this->once())
            ->method('method')
            ->will($this->returnValue('GET'));

        $i = 0;
        $Action
            ->expects($this->at($i++))
            ->method('config')
            ->with('enabled')
            ->will($this->returnValue(true));
        $Action
            ->expects($this->at($i++))
            ->method('_request')
            ->will($this->returnValue($Request));
        $Action
            ->expects($this->once())
            ->method('_handle');

        $Action->handle();
    }

    /**
     * testHandleException
     *
     * Test that calling handle will not invoke _handle
     * when the action is disabled
     *
     * @expectedException Cake\Network\Exception\NotImplementedException
     * @return void
     */
    public function testHandleException()
    {
        $Action = $this->getMockBuilder('Crud\Action\BaseAction')
            ->setMethods(['_request', 'config'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();

        $Request = $this->getMockBuilder('Cake\Network\Request')
            ->setMethods(['method'])
            ->getMock();
        $Request
            ->expects($this->once())
            ->method('method')
            ->will($this->returnValue('GET'));

        $i = 0;
        $Action
            ->expects($this->at($i++))
            ->method('config')
            ->with('enabled')
            ->will($this->returnValue(true));
        $Action
            ->expects($this->at($i++))
            ->method('_request')
            ->will($this->returnValue($Request));

        $Action->handle();
    }
}

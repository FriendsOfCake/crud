<?php
declare(strict_types=1);

namespace Crud\TestCase\Action;

use Cake\Controller\Component\FlashComponent;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\NotImplementedException;
use Cake\Http\ServerRequest;
use Crud\Action\BaseAction;
use Crud\Controller\Component\CrudComponent;
use Crud\Event\Subject;
use Crud\TestSuite\TestCase;
use Exception;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class BaseActionTest extends TestCase
{
    protected ServerRequest $Request;

    public function setUp(): void
    {
        parent::setUp();

        $this->Request = (new ServerRequest())
            ->withParam('action', 'index');
        $this->Controller = $this->getMockBuilder(Controller::class)
            ->onlyMethods(['set'])
            ->setConstructorArgs([
                $this->Request,
                'CrudExamples',
                EventManager::instance(),
            ])
            ->getMock();
        $this->Registry = $this->Controller->components();
        $this->Crud = $this->getMockBuilder(CrudComponent::class)
            ->setConstructorArgs([$this->Registry])
            ->addMethods(['foobar'])
            ->getMock();
        $this->Controller->Crud = $this->Crud;

        $this->getTableLocator()->get('CrudExamples')->setAlias('MyModel');

        $this->actionClassName = $this->getMockBuilder(BaseAction::class)
            ->addMethods(['_handle'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->ActionClass = new $this->actionClassName($this->Controller);
        $this->_configureAction($this->ActionClass);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->getTableLocator()->clear();
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
        $action->setConfig([
            'action' => 'add',
            'enabled' => true,
            'findMethod' => 'first',
            'view' => null,
            'relatedModels' => true,
            'validateId' => null,
            'saveOptions' => [
                'validate' => 'first',
                'atomic' => true,
            ],
            'serialize' => [
                'success',
                'data',
            ],
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
                'atomic' => false,
            ],
            'serialize' => [
                'yay',
                'ney',
            ],
            'action' => 'add',
        ];

        $ActionClass = new $this->actionClassName($this->Controller, $expected);
        // This is injected by the CrudAction, not technically a setting
        $expected['action'] = 'add';
        $actual = $ActionClass->getConfig();
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
        $Request = $this->getMockBuilder(ServerRequest::class)
            ->onlyMethods(['getMethod'])
            ->getMock();
        $Request = $Request->withParam('action', 'add');
        $Request
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $Action = $this->getMockBuilder(BaseAction::class)
            ->onlyMethods(['_request'])
            ->addMethods(['_get'])
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
        $Action->setConfig('action', 'add');

        $expected = true;
        $actual = $Action->getConfig('enabled');
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
        $Action = $this->getMockBuilder(BaseAction::class)
            ->onlyMethods(['setConfig'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();
        $Action
            ->expects($this->once())
            ->method('setConfig', 'enabled was not changed to false by config()')
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

        $Action = $this->getMockBuilder(BaseAction::class)
            ->onlyMethods(['setConfig'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();
        $Action
            ->expects($this->once())
            ->method('setConfig', 'enabled was not changed to false by config()')
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
                'original' => 'Ahoy',
            ],
            'key' => 'custom',
            'type' => 'add.success',
            'name' => 'test',
            'text' => 'Ahoy',
        ];

        $Subject = new Subject();

        $this->Controller->Crud = $this->getMockBuilder(CrudComponent::class)
            ->onlyMethods(['trigger'])
            ->setConstructorArgs([$this->Registry])
            ->getMock();
        $this->Controller->Crud
            ->expects($this->once())
            ->method('trigger')
            ->with('setFlash', $Subject)
            ->will($this->returnValue(new Event('Crud.setFlash')));

        $this->Controller->Flash = $this->getMockBuilder(FlashComponent::class)
            ->onlyMethods(['set'])
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

        $this->ActionClass->setConfig('name', 'test');
        $this->ActionClass->setConfig('messages', [
            'success' => ['text' => 'Ahoy', 'key' => 'custom'],
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
            'atomic' => true,
        ];
        $actual = $CrudAction->getConfig('saveOptions');
        $this->assertEquals($expected, $actual);

        $CrudAction->setConfig('saveOptions.atomic', true);
        $expected = [
            'validate' => 'first',
            'atomic' => true,
        ];
        $actual = $CrudAction->getConfig('saveOptions');
        $this->assertEquals($expected, $actual);

        $CrudAction->setConfig('saveOptions', [
            'fieldList' => ['hello'],
        ]);
        $expected = [
            'validate' => 'first',
            'atomic' => true,
            'fieldList' => ['hello'],
        ];
        $actual = $CrudAction->getConfig('saveOptions');
        $this->assertEquals($expected, $actual);
    }

    /**
     * testUndefinedMessage
     */
    public function testUndefinedMessage()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid message type "not defined"');

        $this->ActionClass->message('not defined');
    }

    /**
     * testBadMessageConfig
     */
    public function testBadMessageConfig()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid message config for "badConfig" no text key found');

        $this->Crud->setConfig('messages.badConfig', ['foo' => 'bar']);
        $this->ActionClass->message('badConfig');
    }

    /**
     * testInheritedSimpleMessage
     *
     * @return void
     */
    public function testInheritedSimpleMessage()
    {
        $this->Crud->setConfig('messages.simple', 'Simple message');

        $expected = [
            'element' => 'default',
            'params' => [
                'class' => 'message simple',
                'original' => 'Simple message',
            ],
            'key' => 'flash',
            'type' => 'add.simple',
            'name' => 'my model',
            'text' => 'Simple message',
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
        $this->Crud->setConfig('messages.simple', 'Simple message');
        $this->ActionClass->setConfig('messages.simple', 'Overridden message');

        $expected = [
            'element' => 'default',
            'params' => [
                'class' => 'message simple',
                'original' => 'Overridden message',
            ],
            'key' => 'flash',
            'type' => 'add.simple',
            'name' => 'my model',
            'text' => 'Overridden message',
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
        $this->ActionClass->setConfig('messages.simple', 'Simple message');

        $expected = [
            'element' => 'default',
            'params' => [
                'class' => 'message simple',
                'original' => 'Simple message',
            ],
            'key' => 'flash',
            'type' => 'add.simple',
            'name' => 'my model',
            'text' => 'Simple message',
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
        $this->Crud->setConfig('messages.simple', 'Simple message with id "{id}"');

        $expected = [
            'element' => 'default',
            'params' => [
                'class' => 'message simple',
                'original' => 'Simple message with id "{id}"',
            ],
            'key' => 'flash',
            'type' => 'add.simple',
            'name' => 'my model',
            'text' => 'Simple message with id "123"',
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
            'class' => BadRequestException::class,
            'element' => 'default',
            'params' => [
                'class' => 'message invalidId',
                'original' => 'Invalid id',
            ],
            'key' => 'flash',
            'type' => 'add.invalidId',
            'name' => 'my model',
            'text' => 'Invalid id',
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
            'class' => NotFoundException::class,
            'element' => 'default',
            'params' => [
                'class' => 'message recordNotFound',
                'original' => 'Not found',
            ],
            'key' => 'flash',
            'type' => 'add.recordNotFound',
            'name' => 'my model',
            'text' => 'Not found',
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
            'class' => MethodNotAllowedException::class,
            'element' => 'default',
            'params' => [
                'class' => 'message badRequestMethod',
                'original' => 'Method not allowed. This action permits only {methods}',
            ],
            'key' => 'flash',
            'type' => 'add.badRequestMethod',
            'name' => 'my model',
            'text' => 'Method not allowed. This action permits only THESE ONES',
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
        $Action = $this->getMockBuilder(BaseAction::class)
            ->onlyMethods(['_request', 'getConfig'])
            ->addMethods(['_get'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();

        $Request = $this->getMockBuilder(ServerRequest::class)
            ->onlyMethods(['getMethod'])
            ->getMock();
        $Request
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $Action
            ->expects($this->once())
            ->method('getConfig')
            ->with('enabled')
            ->will($this->returnValue(true));
        $Action
            ->expects($this->once())
            ->method('_request')
            ->will($this->returnValue($Request));
        $Action
            ->expects($this->once())
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
        $Action = $this->getMockBuilder(BaseAction::class)
            ->onlyMethods(['getConfig'])
            ->addMethods(['_handle', '_get'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();

        $Action
            ->expects($this->once())
            ->method('getConfig')
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
        $Action = $this->getMockBuilder(BaseAction::class)
            ->onlyMethods(['_request', 'getConfig'])
            ->addMethods(['_handle'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();

        $Request = $this->getMockBuilder(ServerRequest::class)
            ->onlyMethods(['getMethod'])
            ->getMock();
        $Request
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $Action
            ->expects($this->once())
            ->method('getConfig')
            ->with('enabled')
            ->will($this->returnValue(true));
        $Action
            ->expects($this->once())
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
     * @return void
     */
    public function testHandleException()
    {
        $this->expectException(NotImplementedException::class);

        $Action = $this->getMockBuilder(BaseAction::class)
            ->onlyMethods(['_request', 'getConfig'])
            ->setConstructorArgs([$this->Controller])
            ->getMock();

        $Request = $this->getMockBuilder(ServerRequest::class)
            ->onlyMethods(['getMethod'])
            ->getMock();
        $Request
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $Action
            ->expects($this->once())
            ->method('getConfig')
            ->with('enabled')
            ->will($this->returnValue(true));
        $Action
            ->expects($this->once())
            ->method('_request')
            ->will($this->returnValue($Request));

        $Action->handle();
    }
}

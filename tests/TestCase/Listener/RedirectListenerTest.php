<?php
namespace Crud\Test\TestCase\Listener;

use Cake\Utility\Hash;
use Crud\TestSuite\TestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class RedirectListenerTest extends TestCase
{

    /**
     * Test the correct events is bound
     *
     * @return void
     */
    public function testImplementedEvents()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $result = $listener->implementedEvents();
        $expected = [
            'Crud.beforeRedirect' => ['callable' => 'beforeRedirect', 'priority' => 90]
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that we got the default readers bound on setup
     *
     * @return void
     */
    public function testSetup()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $result = $listener->config('readers');

        $result['request'] = array_keys($result['request']);
        $result['entity'] = array_keys($result['entity']);
        $result['subject'] = array_keys($result['subject']);

        $expected = [
            'request' => [
                'key',
                'data',
                'query'
            ],
            'entity' => [
                'field'
            ],
            'subject' => [
                'key'
            ]
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test getting an existing reader by name works
     *
     * @return void
     */
    public function testReaderGetWorks()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $closure = $listener->reader('request.key');

        $this->assertNotNull($closure);
        $this->assertInstanceOf('Closure', $closure);
    }

    /**
     * Test getting a non-existing reader by name fails
     *
     * @return void
     */
    public function testReaderGetFails()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $closure = $listener->reader('something_invalid');

        $this->assertNull($closure);
    }

    /**
     * Test setting a reader by name works
     *
     * @return void
     */
    public function testReaderSetWorks()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $closure = function () {
        };

        $actual = $listener->reader('my.reader', $closure);
        $this->assertSame($listener, $actual);

        $actual = $listener->reader('my.reader');
        $this->assertSame($closure, $actual);
    }

    /**
     * Test the reader "request.key"
     *
     * @return void
     */
    public function testReaderRequestKey()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(['_request'])
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $subject = new \Crud\Event\Subject();
        $request = new \Cake\Network\Request();
        $request->params['action'] = 'index';

        $listener->expects($this->any())->method('_request')->will($this->returnValue($request));

        $reader = $listener->reader('request.key');
        $result = $reader($subject, 'action');
        $this->assertEquals('index', $result);

        $result = $reader($subject, 'something_wrong');
        $this->assertNull($result);
    }

    /**
     * Test the reader "request.data"
     *
     * @return void
     */
    public function testReaderRequestData()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(['_request'])
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $subject = new \Crud\Event\Subject();
        $request = new \Cake\Network\Request();
        $request->data = ['hello' => 'world'];

        $listener->expects($this->any())->method('_request')->will($this->returnValue($request));

        $reader = $listener->reader('request.data');
        $result = $reader($subject, 'hello');
        $this->assertEquals('world', $result);

        $result = $reader($subject, 'something_wrong');
        $this->assertNull($result);
    }

    /**
     * Test the reader "request.query"
     *
     * @return void
     */
    public function testReaderRequestQuery()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(['_request'])
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $subject = new \Crud\Event\Subject();
        $request = new \Cake\Network\Request();
        $request->query = ['hello' => 'world'];

        $listener->expects($this->any())->method('_request')->will($this->returnValue($request));

        $reader = $listener->reader('request.query');
        $result = $reader($subject, 'hello');
        $this->assertEquals('world', $result);

        $result = $reader($subject, 'something_wrong');
        $this->assertNull($result);
    }

    /**
     * Test the reader "entity.field"
     *
     * @return void
     */
    public function testReaderEntityField()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $subject = new \Crud\Event\Subject();
        $subject->entity = $this
            ->getMockBuilder('\Cake\ORM\Entity')
            ->setMethods(['get'])
            ->disableoriginalConstructor()
            ->getMock();
        $subject->entity
            ->expects($this->once())
            ->method('get')
            ->with('slug')
            ->will($this->returnValue('ok-slug-is-ok'));

        $reader = $listener->reader('entity.field');
        $result = $reader($subject, 'slug');
        $this->assertEquals('ok-slug-is-ok', $result);
    }

    /**
     * Test the reader "subject.key"
     *
     * @return void
     */
    public function testReaderSubjectKey()
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $subject = new \Crud\Event\Subject();
        $subject->welcome = 'hello world';

        $reader = $listener->reader('subject.key');
        $result = $reader($subject, 'welcome');
        $this->assertEquals('hello world', $result);

        $result = $reader($subject, 'something_invalid');
        $this->assertNull($result);
    }

    /**
     * Test how `redirect` handles an action without any
     * redirect configuration
     *
     * @return void
     */
    public function testRedirectWithNoConfig()
    {
        $action = $this
            ->getMockBuilder('\Crud\Action\BaseAction')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(['_action', '_getKey'])
            ->disableoriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->once())
            ->method('_action')
            ->will($this->returnValue($action));
        $listener
            ->expects($this->never())
            ->method('_getKey');

        $subject = new \Crud\Event\Subject();

        $listener->beforeRedirect(new \Cake\Event\Event('Crud.beforeRedirect', $subject));
    }

    /**
     * Test how `redirect` handles an action with action redirect
     * configuration
     *
     * @return void
     */
    public function testRedirectWithConfigButNoValidKey()
    {
        $action = $this
            ->getMockBuilder('\Crud\Action\BaseAction')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $action->redirectConfig('add', ['reader' => 'request.key', 'key' => 'hello']);

        $subject = new \Crud\Event\Subject();

        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(['_action', '_getKey', '_getUrl'])
            ->disableoriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->once())
            ->method('_action')
            ->will($this->returnValue($action));
        $listener
            ->expects($this->once())
            ->method('_getKey')
            ->with($subject, 'request.key', 'hello')
            ->will($this->returnValue(false));
        $listener
            ->expects($this->never())
            ->method('_getUrl');

        $listener->beforeRedirect(new \Cake\Event\Event('Crud.beforeRedirect', $subject));
    }

    /**
     * Test how `redirect` handles an action with action redirect
     * configuration
     *
     * @return void
     */
    public function testRedirectWithConfigAndValidKey()
    {
        $action = $this
            ->getMockBuilder('\Crud\Action\BaseAction')
            ->setMethods(null)
            ->disableoriginalConstructor()
            ->getMock();

        $action->redirectConfig('add', [
            'reader' => 'request.key',
            'key' => 'hello',
            'url' => ['action' => 'index']
        ]);

        $subject = new \Crud\Event\Subject();

        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(['_action', '_getKey', '_getUrl'])
            ->disableoriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->once())
            ->method('_action')
            ->will($this->returnValue($action));
        $listener
            ->expects($this->once())
            ->method('_getKey')
            ->with($subject, 'request.key', 'hello')
            ->will($this->returnValue(true));
        $listener
            ->expects($this->once())
            ->method('_getUrl')
            ->with($subject, ['action' => 'index'])
            ->will($this->returnValue(['action' => 'index']));

        $listener->beforeRedirect(new \Cake\Event\Event('Crud.beforeRedirect', $subject));

        $this->assertSame(['action' => 'index'], $subject->url);
    }

    /**
     * [dataProviderGetUrl description]
     *
     * @return array
     */
    public function dataProviderGetUrl()
    {
        $Request = new \Cake\Network\Request;
        $Request->params['action'] = 'index';
        $Request->query['parent_id'] = 10;
        $Request->data['epic'] = 'jippi';

        $Model = new \Cake\ORM\Entity();
        $Model->id = 69;
        $Model->slug = 'jippi-is-awesome';
        $Model->data = ['name' => 'epic', 'slug' => 'epic'];

        return [
            [
                new \Crud\Event\Subject(),
                ['action' => 'index'],
                ['action' => 'index']
            ],
            [
                new \Crud\Event\Subject(),
                ['controller' => 'posts', 'action' => 'index'],
                ['controller' => 'posts', 'action' => 'index']
            ],
            [
                new \Crud\Event\Subject(['request' => $Request]),
                ['action' => ['request.key', 'action']],
                ['action' => 'index']
            ],
            [
                new \Crud\Event\Subject(['request' => $Request]),
                ['action' => ['request.data', 'epic']],
                ['action' => 'jippi']
            ],
            [
                new \Crud\Event\Subject(['request' => $Request]),
                ['action' => ['request.query', 'parent_id']],
                ['action' => 10]
            ],
            [
                new \Crud\Event\Subject(['id' => 69]),
                ['action' => 'edit', ['subject.key', 'id']],
                ['action' => 'edit', 69]
            ]
        ];
    }

    /**
     * Test _getUrl
     *
     * @dataProvider dataProviderGetUrl
     * @return void
     */
    public function testGetUrl(\Crud\Event\Subject $subject, $url, $expected)
    {
        $listener = $this
            ->getMockBuilder('\Crud\Listener\RedirectListener')
            ->setMethods(['_request'])
            ->disableoriginalConstructor()
            ->getMock();

        if (empty($subject->request)) {
            $request = new \Cake\Network\Request();
        } else {
            $request = $subject->request;
        }

        $listener
            ->expects($this->any())
            ->method('_request')
            ->with()
            ->will($this->returnValue($request));
        $listener->setup();

        $this->setReflectionClassInstance($listener);

        $result = $this->callProtectedMethod('_getUrl', [$subject, $url], $listener);
        $this->assertEquals($expected, $result);
    }
}

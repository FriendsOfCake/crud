<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Listener;

use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\ORM\Entity;
use Crud\Action\BaseAction;
use Crud\Event\Subject;
use Crud\Listener\RedirectListener;
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
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods([])
            ->disableoriginalConstructor()
            ->getMock();

        $result = $listener->implementedEvents();
        $expected = [
            'Crud.beforeRedirect' => ['callable' => 'beforeRedirect', 'priority' => 90],
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
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods([])
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $result = $listener->getConfig('readers');

        $result['request'] = array_keys($result['request']);
        $result['entity'] = array_keys($result['entity']);
        $result['subject'] = array_keys($result['subject']);

        $expected = [
            'request' => [
                'key',
                'data',
                'query',
            ],
            'entity' => [
                'field',
            ],
            'subject' => [
                'key',
            ],
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
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods([])
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
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods([])
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
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods([])
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
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods(['_request'])
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $subject = new Subject();
        $request = (new ServerRequest())->withParam('action', 'index');

        $listener->expects($this->any())->method('_request')->willReturn($request);

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
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods(['_request'])
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $subject = new Subject();
        $request = (new ServerRequest())->withData('hello', 'world');

        $listener->expects($this->any())->method('_request')->willReturn($request);

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
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods(['_request'])
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $subject = new Subject();
        $request = (new ServerRequest())->withQueryParams(['hello' => 'world']);

        $listener->expects($this->any())->method('_request')->willReturn($request);

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
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods([])
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $subject = new Subject();
        $subject->entity = $this
            ->getMockBuilder(Entity::class)
            ->onlyMethods(['get'])
            ->disableoriginalConstructor()
            ->getMock();
        $subject->entity
            ->expects($this->once())
            ->method('get')
            ->with('slug')
            ->willReturn('ok-slug-is-ok');

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
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods([])
            ->disableoriginalConstructor()
            ->getMock();

        $listener->setup();

        $subject = new Subject();
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
            ->getMockBuilder(BaseAction::class)
            ->onlyMethods([])
            ->disableoriginalConstructor()
            ->getMock();

        $listener = $this
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods(['_action', '_getKey'])
            ->disableoriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->once())
            ->method('_action')
            ->willReturn($action);
        $listener
            ->expects($this->never())
            ->method('_getKey');

        $subject = new Subject();

        $listener->beforeRedirect(new Event('Crud.beforeRedirect', $subject));
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
            ->getMockBuilder(BaseAction::class)
            ->onlyMethods([])
            ->disableoriginalConstructor()
            ->getMock();

        $action->redirectConfig('add', ['reader' => 'request.key', 'key' => 'hello']);

        $subject = new Subject();

        $listener = $this
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods(['_action', '_getKey', '_getUrl'])
            ->disableoriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->once())
            ->method('_action')
            ->willReturn($action);
        $listener
            ->expects($this->once())
            ->method('_getKey')
            ->with($subject, 'request.key', 'hello')
            ->willReturn(false);
        $listener
            ->expects($this->never())
            ->method('_getUrl');

        $listener->beforeRedirect(new Event('Crud.beforeRedirect', $subject));
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
            ->getMockBuilder(BaseAction::class)
            ->onlyMethods([])
            ->disableoriginalConstructor()
            ->getMock();

        $action->redirectConfig('add', [
            'reader' => 'request.key',
            'key' => 'hello',
            'url' => ['action' => 'index'],
        ]);

        $subject = new Subject();

        $listener = $this
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods(['_action', '_getKey', '_getUrl'])
            ->disableoriginalConstructor()
            ->getMock();
        $listener
            ->expects($this->once())
            ->method('_action')
            ->willReturn($action);
        $listener
            ->expects($this->once())
            ->method('_getKey')
            ->with($subject, 'request.key', 'hello')
            ->willReturn(true);
        $listener
            ->expects($this->once())
            ->method('_getUrl')
            ->with($subject, ['action' => 'index'])
            ->willReturn(['action' => 'index']);

        $listener->beforeRedirect(new Event('Crud.beforeRedirect', $subject));

        $this->assertSame(['action' => 'index'], $subject->url);
    }

    /**
     * [dataProviderGetUrl description]
     *
     * @return array
     */
    public static function dataProviderGetUrl()
    {
        $Request = new ServerRequest();
        $Request = $Request->withParam('action', 'index')
            ->withQueryParams(['parent_id' => 10])
            ->withData('epic', 'jippi');

        $Model = new Entity();
        $Model->id = 69;
        $Model->slug = 'jippi-is-awesome';
        $Model->data = ['name' => 'epic', 'slug' => 'epic'];

        return [
            [
                new Subject(),
                ['action' => 'index'],
                ['action' => 'index'],
            ],
            [
                new Subject(),
                ['controller' => 'posts', 'action' => 'index'],
                ['controller' => 'posts', 'action' => 'index'],
            ],
            [
                new Subject(['request' => $Request]),
                ['action' => ['request.key', 'action']],
                ['action' => 'index'],
            ],
            [
                new Subject(['request' => $Request]),
                ['action' => ['request.data', 'epic']],
                ['action' => 'jippi'],
            ],
            [
                new Subject(['request' => $Request]),
                ['action' => ['request.query', 'parent_id']],
                ['action' => 10],
            ],
            [
                new Subject(['id' => 69]),
                ['action' => 'edit', ['subject.key', 'id']],
                ['action' => 'edit', 69],
            ],
        ];
    }

    /**
     * Test _getUrl
     *
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderGetUrl')]
    public function testGetUrl(Subject $subject, $url, $expected)
    {
        $listener = $this
            ->getMockBuilder(RedirectListener::class)
            ->onlyMethods(['_request'])
            ->disableoriginalConstructor()
            ->getMock();

        if (empty($subject->request)) {
            $request = new ServerRequest();
        } else {
            $request = $subject->request;
        }

        $listener
            ->expects($this->any())
            ->method('_request')
            ->with()
            ->willReturn($request);
        $listener->setup();

        $this->setReflectionClassInstance($listener);

        $result = $this->callProtectedMethod('_getUrl', [$subject, $url], $listener);
        $this->assertEquals($expected, $result);
    }
}

<?php
namespace Crud\TestCase\Event;

use Crud\Event\Subject;
use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class SubjectTest extends TestCase
{

    public function setup()
    {
        parent::setup();

        $this->Subject = new Subject(['action' => 'index']);
    }

    public function teardown()
    {
        parent::teardown();

        unset($this->Subject);
    }

    /**
     * Test that shouldProcess works
     *
     * Our action is "index"
     *
     * @return void
     */
    public function testShouldProcess()
    {
        $this->assertTrue($this->Subject->shouldProcess('only', 'index'));
        $this->assertFalse($this->Subject->shouldProcess('only', 'view'));
        $this->assertTrue($this->Subject->shouldProcess('only', ['index']));
        $this->assertFalse($this->Subject->shouldProcess('only', ['view']));

        $this->assertFalse($this->Subject->shouldProcess('not', ['index']));
        $this->assertTrue($this->Subject->shouldProcess('not', ['view']));

        $this->assertFalse($this->Subject->shouldProcess('not', 'index'));
        $this->assertTrue($this->Subject->shouldProcess('not', 'view'));
    }

    /**
     * Test that event adding works
     *
     * @return void
     */
    public function testEventNames()
    {
        $this->assertFalse($this->Subject->hasEvent('test'));
        $this->assertFalse($this->Subject->hasEvent('test_two'));
        $this->assertFalse($this->Subject->hasEvent('test_three'));
        $this->assertFalse($this->Subject->hasEvent('invalid'));

        $this->Subject->addEvent('test');
        $this->Subject->addEvent('test_two');
        $this->Subject->addEvent('test_three');
        $this->assertTrue($this->Subject->hasEvent('test'));
        $this->assertTrue($this->Subject->hasEvent('test_two'));
        $this->assertTrue($this->Subject->hasEvent('test_three'));
        $this->assertFalse($this->Subject->hasEvent('invalid'));

        $expected = ['test', 'test_two', 'test_three'];
        $this->assertEquals($expected, $this->Subject->getEvents());
    }

    /**
     * testInvalidMode
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid mode
     * @return void
     */
    public function testInvalidMode()
    {
        $this->Subject->shouldProcess('invalid');
    }
}

<?php
namespace Crud\Test\TestCase\Traits;

use Crud\TestSuite\TestCase;
use Crud\Test\App\Model\Entity\Country;
use Crud\Traits\JsonApiTrait;
use stdClass;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class JsonApiTraitTest extends TestCase
{
    use JsonApiTrait;

    /**
     * _getClassName()
     *
     * @return void
     */
    public function testGetClassName()
    {
        // assert false for arguments that are not a class
        $this->assertFalse($this->_getClassName('string'));
        $this->assertFalse($this->_getClassName(123));
        $this->assertFalse($this->_getClassName(true));
        $this->assertFalse($this->_getClassName(['dummy' => 'array']));

        // assert success
        $object = new stdClass();
        $expected = 'stdClass';
        $this->assertSame($expected, $this->_getClassName($object));

        $object = new Country();
        $expected = 'Country';
        $this->assertSame($expected, $this->_getClassName($object));
    }

    /**
     * _stringIsSingular()
     *
     * @return void
     */
    public function testStringIsSingular()
    {
        //assert success
        $this->assertTrue($this->_stringIsSingular('country'));
        $this->assertTrue($this->_stringIsSingular('Country'));

        // assert fails
        $this->assertFalse($this->_stringIsSingular('countries'));
        $this->assertFalse($this->_stringIsSingular('Countries'));
    }
}

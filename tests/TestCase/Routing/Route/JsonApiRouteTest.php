<?php
namespace Crud\Test\TestCase\Routing\Route;

use Crud\TestSuite\TestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class JsonApiRouteTest extends TestCase
{

    /**
     * Fixtures property.
     *
     * @var array
     */
    public $fixtures = [
        'plugin.crud.countries',
        'plugin.crud.cultures',
        'plugin.crud.currencies',
    ];

    /**
     * Mocked JsonApiRoute used by all tests.
     *
     * @var \Crud\Routing\Route\JsonApiRoute
     */
    protected $_route;

    /**
     * Setup
     */
    public function setup()
    {
        $this->_route = $this
            ->getMockBuilder('\Crud\Routing\Route\JsonApiRoute')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->setReflectionClassInstance($this->_route);
    }

    /**
     * Make sure routes get parsed
     *
     * @return void
     */
    public function testParse()
    {
        $this->assertFalse($this->_route->parse('http://my.app/dogs/1/relationships/cat'));

        $expected = [
            'controller' => 'Currencies',
            'action' => 'view',
            'pass' => [
                1
            ]
        ];

        $this->assertSame($expected, $this->_route->parse('http://my.app/countries/1/relationships/currency'));
    }

    /**
     * Test `self` links inside relationships node with belongsTo relation.
     *
     * E.g. http://my.app/countries/3/relationships/currency
     *
     * @return void
     */
    public function testBelongsToRelationshipSelfLink()
    {
        // assert non-matching routes are ignored
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries?page=1'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries/1'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries/1?query=dummy'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries/view/1'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries/view/1?query=dummy'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries/1/relationships/currency/1?field=dummy'], $this->_route));

        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries?page=1'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries/1'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries/1?query=dummy'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries/view/1'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries/view/1?query=dummy'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries/1/relationships/currency/1?field=dummy'], $this->_route));

        // assert matching routes that have an hasMany relationship instead of a belongsTo relationship are ignored
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries/1/relationships/cultures'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries/1/relationships/cats'], $this->_route));

        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries/1/relationships/cultures'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries/1/relationships/cats'], $this->_route));

        // assert matching routes without a related record in the database are ignored
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries/666/relationships/currency'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/dogs/1/relationships/cat'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/dogs/1/relationships/cat'], $this->_route));

        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries/666/relationships/currency'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/dogs/1/relationships/cat'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/dogs/1/relationships/cat'], $this->_route));

        // assert matching routes with a related database record but without the sought for foreign key are ignored
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries/1/relationships/cat'], $this->_route));
        $this->assertFalse($this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries/1/relationships/cat'], $this->_route));

        // assert success
        $expected = [
            'controller' => 'Currencies',
            'action' => 'view',
            'pass' => [
                1
            ]
        ];

        $this->assertSame($expected, $this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries/1/relationships/currency'], $this->_route));
        $this->assertSame($expected, $this->callProtectedMethod('_belongsToRelationshipSelfLink', ['/countries/1/relationships/currency?query=dummy'], $this->_route));

        $this->assertSame($expected, $this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries/1/relationships/currency'], $this->_route));
        $this->assertSame($expected, $this->callProtectedMethod('_belongsToRelationshipSelfLink', ['http://my.app/countries/1/relationships/currency?query=dummy'], $this->_route));
    }
}

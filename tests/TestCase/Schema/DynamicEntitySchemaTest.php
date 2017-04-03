<?php
namespace Crud\Test\TestCase\Schema\JsonApi;

use Cake\Controller\Controller;
use Cake\ORM\TableRegistry;
use Crud\Listener\JsonApiListener;
use Crud\TestSuite\TestCase;
use Neomerx\JsonApi\Factories\Factory;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class DynamicEntitySchemaTest extends TestCase
{

    /**
     * fixtures property
     *
     * @var array
     */
    public $fixtures = [
        'plugin.crud.countries',
        'plugin.crud.cultures',
        'plugin.crud.currencies',
    ];

    /**
     * Test NeoMerx override getAttributes().
     *
     * @return void
     */
    public function testGetAttributes()
    {
        // fetch data to test against
        $table = TableRegistry::get('Countries');
        $query = $table->find()
            ->where([
                'Countries.id' => 2
            ])
            ->contain([
                'Cultures',
                'Currencies',
            ]);

        $entity = $query->first();

        // make sure we are testing against expected baseline
        $expectedCurrencyId = 1;
        $expectedFirstCultureId = 2;
        $expectedSecondCultureId = 3;

        $this->assertArrayHasKey('currency', $entity);
        $this->assertSame($expectedCurrencyId, $entity['currency']['id']);

        $this->assertArrayHasKey('cultures', $entity);
        $this->assertCount(2, $entity['cultures']);
        $this->assertSame($expectedFirstCultureId, $entity['cultures'][0]['id']);
        $this->assertSame($expectedSecondCultureId, $entity['cultures'][1]['id']);

        // get required AssociationsCollection
        $listener = new JsonApiListener(new Controller());
        $this->setReflectionClassInstance($listener);
        $associations = $this->callProtectedMethod('_getContainedAssociations', [$table, $query->contain()], $listener);

        // make view return associations on get('_associations') call
        $view = $this
            ->getMockBuilder('\Cake\View\View')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $view->set('_associations', $associations);

        // setup the schema
        $schema = $this
            ->getMockBuilder('\Crud\Schema\JsonApi\DynamicEntitySchema')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setReflectionClassInstance($schema);

        $this->setProtectedProperty('_view', $view, $schema);

        // assert method
        $result = $this->callProtectedMethod('getAttributes', [$entity], $schema);

        $this->assertSame('BE', $result['code']);
        $this->assertArrayNotHasKey('id', $result);
        $this->assertArrayNotHasKey('currency', $result); // relationships should be removed
        $this->assertArrayNotHasKey('cultures', $result);
    }

    /**
     * Test both NeoMerx override methods getRelationships() and
     * getRelationshipSelfLinks() responsible for generating the
     * JSON API 'relationships` node with matching `self` links.
     *
     * @return void
     */
    public function testRelationships()
    {
        // fetch associated data to test against
        $table = TableRegistry::get('Countries');
        $query = $table->find()
            ->where([
                'Countries.id' => 2
            ])
            ->contain([
                'Cultures',
                'Currencies',
            ]);

        $entity = $query->first();

        // make sure we are testing against expected baseline
        $expectedCurrencyId = 1;
        $expectedFirstCultureId = 2;
        $expectedSecondCultureId = 3;

        $this->assertArrayHasKey('currency', $entity);
        $this->assertSame($expectedCurrencyId, $entity['currency']['id']);

        $this->assertArrayHasKey('cultures', $entity);
        $this->assertCount(2, $entity['cultures']);
        $this->assertSame($expectedFirstCultureId, $entity['cultures'][0]['id']);
        $this->assertSame($expectedSecondCultureId, $entity['cultures'][1]['id']);

        // get required AssociationsCollection
        $listener = new JsonApiListener(new Controller());
        $this->setReflectionClassInstance($listener);
        $associations = $this->callProtectedMethod('_getContainedAssociations', [$table, $query->contain()], $listener);

        // make view return associations on get('_associations') call
        $view = $this
            ->getMockBuilder('\Cake\View\View')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $view->set('_associations', $associations);
        $view->set('_absoluteLinks', false); // test relative links (listener default)

        // setup the schema
        $schema = $this
            ->getMockBuilder('\Crud\Schema\JsonApi\DynamicEntitySchema')
            ->setMethods(null)
            ->setConstructorArgs([new Factory(), $view, $table])
            ->getMock();

        $this->setReflectionClassInstance($schema);

        $this->setProtectedProperty('_view', $view, $schema);

        // assert getRelationships()
        $relationships = $this->callProtectedMethod('getRelationships', [$entity, true, []], $schema);

        $this->assertArrayHasKey('currency', $relationships);
        $this->assertSame($expectedCurrencyId, $relationships['currency']['data']['id']);

        $this->assertArrayHasKey('cultures', $relationships);
        $this->assertCount(2, $relationships['cultures']['data']);
        $this->assertSame($expectedFirstCultureId, $relationships['cultures']['data'][0]['id']);
        $this->assertSame($expectedSecondCultureId, $relationships['cultures']['data'][1]['id']);

        // assert generated belongsToLink using listener default (direct link)
        $view->set('_jsonApiBelongsToLinks', false);
        $expected = '/currencies/1';
        $result = $this->callProtectedMethod('getRelationshipSelfLink', [$entity, 'currency', null, true], $schema);
        $this->setReflectionClassInstance($result);
        $this->assertSame($expected, $this->getProtectedProperty('subHref', $result));

        // assert generated belongsToLink using JsonApi (indirect link, requires custom JsonApiRoute)
        $view->set('_jsonApiBelongsToLinks', true);
        $expected = '/countries/2/relationships/currency';
        $result = $this->callProtectedMethod('getRelationshipSelfLink', [$entity, 'currency', null, true], $schema);
        $this->setReflectionClassInstance($result);
        $this->assertSame($expected, $this->getProtectedProperty('subHref', $result));

        // assert _ getRelationshipSelfLinks() for plural (hasMany)
        $expected = '/cultures?country_id=2';

        $result = $this->callProtectedMethod('getRelationshipSelfLink', [$entity, 'cultures', null, true], $schema);
        $this->setReflectionClassInstance($result);
        $this->assertSame($expected, $this->getProtectedProperty('subHref', $result));

        // assert relationships that are valid BUT have no data present in the entity are skipped
        unset($entity['currency']);
        $this->assertArrayNotHasKey('currency', $entity);

        $result = $this->callProtectedMethod('getRelationships', [$entity, true, []], $schema);
        $this->assertArrayNotHasKey('currency', $result);
        $this->assertArrayHasKey('cultures', $result);
    }

    /**
     * Test NeoMerx override getIncludedResourceLinks() used to generate
     * `self` links inside the optional JSON API `included` node.
     *
     * @return void
     */
    public function testGetIncludedResourceLinks()
    {
        // assert relative links (listener default)
        $view = $this
            ->getMockBuilder('\Cake\View\View')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $view->set('_absoluteLinks', false);
        $view->set('_jsonApiBelongsToLinks', false);

        // get results
        $table = TableRegistry::get('Countries');

        $schema = $this
            ->getMockBuilder('\Crud\Schema\JsonApi\DynamicEntitySchema')
            ->setMethods(null)
            ->setConstructorArgs([new Factory(), $view, $table])
            ->getMock();

        $this->setReflectionClassInstance($schema);
        $this->setProtectedProperty('_view', $view, $schema);

        $entity = $table
            ->find()
            ->contain([
                'Currencies',
            ])
            ->first();
        $result = $this->callProtectedMethod('getIncludedResourceLinks', [$entity->currency], $schema);

        // assert success
        $this->assertArrayHasKey('self', $result);
        $selfLink = $result['self'];
        $this->assertTrue(is_a($selfLink, '\Neomerx\JsonApi\Document\Link'));

        $this->setReflectionClassInstance($selfLink);
        $this->assertSame('/currencies/1', $this->getProtectedProperty('subHref', $selfLink));
    }
}

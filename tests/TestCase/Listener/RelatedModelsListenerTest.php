<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Listener;

use Cake\Database\Connection;
use Cake\Database\Schema\TableSchema;
use Cake\Event\Event;
use Cake\ORM\Association;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\AssociationCollection;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Crud\Event\Subject;
use Crud\Listener\RelatedModelsListener;
use Crud\TestSuite\TestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class RelatedModelsListenerTest extends TestCase
{
    protected array $fixtures = ['core.NumberTrees'];

    /**
     * testModels
     *
     * @return void
     */
    public function testModels()
    {
        $listener = $this
            ->getMockBuilder(RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['relatedModels'])
            ->getMock();

        $listener
            ->expects($this->once())
            ->method('relatedModels')
            ->with(null, null)
            ->will($this->returnValue(null));

        $result = $listener->models();
        $expected = [];
        $this->assertEquals($expected, $result);
    }

    /**
     * testModelsEmpty
     *
     * Test behavior when 'relatedModels' is empty
     *
     * @return void
     */
    public function testModelsEmpty()
    {
        $listener = $this
            ->getMockBuilder(RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['relatedModels'])
            ->getMock();

        $listener
            ->expects($this->once())
            ->method('relatedModels')
            ->with(null, null)
            ->will($this->returnValue([]));

        $result = $listener->models();
        $expected = [];
        $this->assertEquals($expected, $result);
    }

    /**
     * testModelsEmpty
     *
     * Test behavior when 'relatedModels' is a string
     *
     * @return void
     */
    public function testModelsString()
    {
        $listener = $this
            ->getMockBuilder(RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['relatedModels', 'getAssociatedByName'])
            ->getMock();
        $listener
            ->expects($this->once())
            ->method('relatedModels')
            ->with(null)
            ->will($this->returnValue(['posts']));
        $listener
            ->expects($this->once())
            ->method('getAssociatedByName')
            ->with(['posts']);

        $listener->models();
    }

    /**
     * testModelsTrue
     *
     * Test behavior when 'relatedModels' is true
     *
     * @return void
     */
    public function testModelsTrue()
    {
        $listener = $this
            ->getMockBuilder(RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['relatedModels', 'getAssociatedByType'])
            ->getMock();
        $listener
            ->expects($this->once())
            ->method('relatedModels')
            ->with(null, null)
            ->will($this->returnValue(true));
        $listener
            ->expects($this->once())
            ->method('getAssociatedByType')
            ->with(['oneToOne', 'manyToMany', 'manyToOne']);

        $result = $listener->models();
    }

    /**
     * testGetAssociatedByTypeReturnValue
     *
     * Test return value of `getAssociatedByType`
     *
     * @return void
     */
    public function testGetAssociatedByTypeReturnValue()
    {
        $listener = $this
            ->getMockBuilder(RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['relatedModels', '_model'])
            ->getMock();
        $table = $this
            ->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['associations'])
            ->getMock();
        $associationCollection = $this
            ->getMockBuilder(AssociationCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'keys'])
            ->getMock();
        $association = $this
            ->getMockBuilder(Association::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['type', 'getName', 'eagerLoader', 'cascadeDelete', 'isOwningSide', 'saveAssociated'])
            ->getMock();

        $listener
            ->expects($this->once())
            ->method('_model')
            ->withAnyParameters()
            ->will($this->returnValue($table));
        $table
            ->expects($this->atLeastOnce())
            ->method('associations')
            ->will($this->returnValue($associationCollection));
        $associationCollection
            ->expects($this->once())
            ->method('keys')
            ->will($this->returnValue(['posts']));
        $associationCollection
            ->expects($this->once())
            ->method('get')
            ->with('posts')
            ->will($this->returnValue($association));
        $association
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Posts'));
        $association
            ->expects($this->once())
            ->method('type')
            ->will($this->returnValue('oneToOne'));

        $expected = ['Posts' => $association];
        $result = $listener->getAssociatedByType(['oneToOne', 'manyToMany', 'manyToOne']);

        $this->assertEquals($expected, $result);
    }

    /**
     * testModelReturnsAssociationsByName
     *
     * Test return value of `getAssociatedByName`
     *
     * @return void
     */
    public function testGetAssociatedByNameReturnValue()
    {
        $listener = $this
            ->getMockBuilder(RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['relatedModels', '_model'])
            ->getMock();
        $table = $this
            ->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['associations'])
            ->getMock();
        $associationCollection = $this
            ->getMockBuilder(AssociationCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $association = $this
            ->getMockBuilder(Association::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['type', 'getName', 'eagerLoader', 'cascadeDelete', 'isOwningSide', 'saveAssociated'])
            ->getMock();

        $listener
            ->expects($this->once())
            ->method('_model')
            ->withAnyParameters()
            ->will($this->returnValue($table));
        $table
            ->expects($this->once())
            ->method('associations')
            ->will($this->returnValue($associationCollection));
        $associationCollection
            ->expects($this->once())
            ->method('get')
            ->with('posts')
            ->will($this->returnValue($association));
        $association
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Posts'));

        $expected = ['Posts' => $association];
        $result = $listener->getAssociatedByName(['posts']);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that "treeList" is used if association target has TreeBehavior loaded
     *
     * @return void
     */
    public function testListFinder()
    {
        $model = $this->getTableLocator()->get('NumberTrees');
        $model->addBehavior('Tree');

        $association = $this
            ->getMockBuilder(BelongsTo::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTarget'])
            ->getMock();

        $association
            ->expects($this->any())
            ->method('getTarget')
            ->will($this->returnValue($model));

        $listener = $this
            ->getMockBuilder(RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['publishRelatedModels'])
            ->getMock();

        $result = $listener->finder($association);
        $this->assertEquals('treeList', $result);
    }

    /**
     * testbeforePaginate
     *
     * @return void
     */
    public function testbeforePaginate()
    {
        $listener = $this
            ->getMockBuilder(RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['models'])
            ->getMock();
        $table = $this
            ->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['associations', 'findAssociation', 'getSchema'])
            ->getMock();

        $table
            ->expects($this->any())
            ->method('getSchema')
            ->will($this->returnValue(new TableSchema('Users')));

        $listener
            ->expects($this->once())
            ->method('models')
            ->will($this->returnValue(['Users' => 'manyToOne']));

        $query = new SelectQuery($table);
        $subject = new Subject(['query' => $query]);
        $event = new Event('beforePaginate', $subject);

        $listener->beforePaginate($event);
        if (method_exists($event->getSubject()->query, 'getContain')) {
            $result = $event->getSubject()->query->getContain();
        } else {
            $result = $event->getSubject()->query->contain();
        }

        $this->assertEquals(['Users' => []], $result);
    }
}

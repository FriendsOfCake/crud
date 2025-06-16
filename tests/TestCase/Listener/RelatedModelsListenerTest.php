<?php
declare(strict_types=1);

namespace Crud\Test\TestCase\Listener;

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
            ->willReturn(null);

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
            ->willReturn([]);

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
            ->willReturn(['posts']);
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
            ->willReturn(true);
        $listener
            ->expects($this->once())
            ->method('getAssociatedByType')
            ->with(['oneToOne', 'manyToMany', 'manyToOne']);

        $listener->models();
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
            ->willReturn($table);
        $table
            ->expects($this->atLeastOnce())
            ->method('associations')
            ->willReturn($associationCollection);
        $associationCollection
            ->expects($this->once())
            ->method('keys')
            ->willReturn(['posts']);
        $associationCollection
            ->expects($this->once())
            ->method('get')
            ->with('posts')
            ->willReturn($association);
        $association
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Posts');
        $association
            ->expects($this->once())
            ->method('type')
            ->willReturn('oneToOne');

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
            ->willReturn($table);
        $table
            ->expects($this->once())
            ->method('associations')
            ->willReturn($associationCollection);
        $associationCollection
            ->expects($this->once())
            ->method('get')
            ->with('posts')
            ->willReturn($association);
        $association
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Posts');

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
            ->willReturn($model);

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
            ->willReturn(new TableSchema('Users'));

        $listener
            ->expects($this->once())
            ->method('models')
            ->willReturn(['Users' => 'manyToOne']);

        $query = new SelectQuery($table);
        $subject = new Subject(['query' => $query]);
        $event = new Event('beforePaginate', $subject);

        $listener->beforePaginate($event);
        $result = $event->getSubject()->query->getContain();

        $this->assertEquals(['Users' => []], $result);
    }
}

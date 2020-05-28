<?php
namespace Crud\Test\TestCase\Listener;

use Cake\Database\Schema\TableSchema;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Crud\Event\Subject;
use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class RelatedModelListenerTest extends TestCase
{

    public $fixtures = ['core.NumberTrees'];

    /**
     * testModels
     *
     * @return void
     */
    public function testModels()
    {
        $listener = $this
            ->getMockBuilder(\Crud\Listener\RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['relatedModels'])
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
            ->getMockBuilder(\Crud\Listener\RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['relatedModels'])
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
            ->getMockBuilder(\Crud\Listener\RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['relatedModels', 'getAssociatedByName'])
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
            ->getMockBuilder(\Crud\Listener\RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['relatedModels', 'getAssociatedByType'])
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
            ->getMockBuilder(\Crud\Listener\RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['relatedModels', '_table'])
            ->getMock();
        $table = $this
            ->getMockBuilder(\Cake\ORM\Table::class)
            ->disableOriginalConstructor()
            ->setMethods(['associations'])
            ->getMock();
        $associationCollection = $this
            ->getMockBuilder(\Cake\ORM\AssociationCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'keys'])
            ->getMock();
        $association = $this
            ->getMockBuilder(\Cake\ORM\Association::class)
            ->disableOriginalConstructor()
            ->setMethods(['type', 'getName', 'eagerLoader', 'cascadeDelete', 'isOwningSide', 'saveAssociated'])
            ->getMock();

        $listener
            ->expects($this->once())
            ->method('_table')
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
            ->getMockBuilder(\Crud\Listener\RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['relatedModels', '_table'])
            ->getMock();
        $table = $this
            ->getMockBuilder(\Cake\ORM\Table::class)
            ->disableOriginalConstructor()
            ->setMethods(['associations'])
            ->getMock();
        $associationCollection = $this
            ->getMockBuilder(\Cake\ORM\AssociationCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $association = $this
            ->getMockBuilder(\Cake\ORM\Association::class)
            ->disableOriginalConstructor()
            ->setMethods(['type', 'getName', 'eagerLoader', 'cascadeDelete', 'isOwningSide', 'saveAssociated'])
            ->getMock();

        $listener
            ->expects($this->once())
            ->method('_table')
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
        $model = TableRegistry::get('NumberTrees');
        $model->addBehavior('Tree');

        $association = $this
            ->getMockBuilder(\Cake\ORM\Association\BelongsTo::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTarget'])
            ->getMock();

        $association
            ->expects($this->any())
            ->method('getTarget')
            ->will($this->returnValue($model));

        $listener = $this
            ->getMockBuilder(\Crud\Listener\RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['publishRelatedModels'])
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
            ->getMockBuilder(\Crud\Listener\RelatedModelsListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['models'])
            ->getMock();
        $table = $this
            ->getMockBuilder(\Cake\ORM\Table::class)
            ->disableOriginalConstructor()
            ->setMethods(['associations', 'findAssociation', 'association', 'getSchema'])
            ->getMock();

        $table
            ->expects($this->any())
            ->method('getSchema')
            ->will($this->returnValue(new TableSchema('Users')));

        $listener
            ->expects($this->once())
            ->method('models')
            ->will($this->returnValue(['Users' => 'manyToOne']));

        $db = $this->getMockBuilder(\Cake\Database\Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query = new Query($db, $table);
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

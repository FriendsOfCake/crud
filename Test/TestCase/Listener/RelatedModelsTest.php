<?php
namespace Crud\Test\TestCase\Listener;

use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class RelatedModelTest extends TestCase {

/**
 * testModels
 *
 * @covers \Crud\Listener\RelatedModels::models
 * @return void
 */
	public function testModels() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\RelatedModels')
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
 * @covers \Crud\Listener\RelatedModels::models
 * @return void
 */
	public function testModelsEmpty() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\RelatedModels')
			->disableOriginalConstructor()
			->setMethods(['relatedModels'])
			->getMock();

		$listener
			->expects($this->once())
			->method('relatedModels')
			->with(null, null)
			->will($this->returnValue([]));

		$result = $listener->models();
		$expected = array();
		$this->assertEquals($expected, $result);
	}

/**
 * testModelsEmpty
 *
 * Test behavior when 'relatedModels' is a string
 *
 * @covers \Crud\Listener\RelatedModels::models
 * @return void
 */
	public function testModelsString() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\RelatedModels')
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
 * @covers \Crud\Listener\RelatedModels::models
 * @return void
 */
	public function testModelsTrue() {
		$listener = $this
			->getMockBuilder('\Crud\Listener\RelatedModels')
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
			->with(['oneToOne', 'belongsToMany']);

		$result = $listener->models();
	}

}

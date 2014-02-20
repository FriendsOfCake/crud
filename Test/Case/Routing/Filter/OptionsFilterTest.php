<?php

App::uses('OptionsFilter', 'Crud.Routing/Filter');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');

class OptionsFilterTest extends CakeTestCase {

/**
 * testSimple
 *
 * @return void
 */
	public function testSimple() {
		Router::reload();
		Router::connect('/:controller/:action/*');

		$filter = new OptionsFilter();
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		$request = new CakeRequest('controller/action/1');
		$request->addDetector('options', array(
			'callback' => function() {
				return true;
			}
		));

		$event = new CakeEvent('OptionsFilterTest', $this, compact('request', 'response'));

		$this->assertSame($response, $filter->beforeDispatch($event), 'The Options filter should return a response');
		$this->assertTrue($event->isStopped(), 'The Options filter should stop the event');

		$expected = array(
			'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE'
		);
		$this->assertSame($expected, $response->header(), 'A standard route accepts all verbs');
	}

/**
 * testRestrictedVerbs
 *
 * @return void
 */
	public function testRestrictedVerbs() {
		Router::reload();
		Router::connect('/:controller/:action/*', array('[method]' => 'GET'));

		$filter = new OptionsFilter();
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		$request = new CakeRequest('controller/action/1');
		$request->addDetector('options', array(
			'callback' => function() {
				return true;
			}
		));

		$event = new CakeEvent('OptionsFilterTest', $this, compact('request', 'response'));

		$this->assertSame($response, $filter->beforeDispatch($event), 'The Options filter should return a response');
		$this->assertTrue($event->isStopped(), 'The Options filter should stop the event');

		$expected = array(
			'Access-Control-Allow-Methods' => 'GET'
		);
		$this->assertSame($expected, $response->header(), 'Only verbs for matching routes should be returned');
	}

/**
 * testCustomVerbs
 *
 * @return void
 */
	public function testCustomVerbs() {
		Router::reload();
		Router::connect('/:controller/:action/*', array('[method]' => 'TICKLE'));
		Router::connect('/:controller/:action/*', array('[method]' => 'ANNOY'));

		Configure::write('Crud.OptionsFilter.verbs', array('GET', 'TICKLE', 'ANNOY'));

		$filter = new OptionsFilter();
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		$request = new CakeRequest('controller/action/1');
		$request->addDetector('options', array(
			'callback' => function() {
				return true;
			}
		));

		$event = new CakeEvent('OptionsFilterTest', $this, compact('request', 'response'));

		$this->assertSame($response, $filter->beforeDispatch($event), 'The Options filter should return a response');
		$this->assertTrue($event->isStopped(), 'The Options filter should stop the event');

		$expected = array(
			'Access-Control-Allow-Methods' => 'TICKLE, ANNOY'
		);
		$this->assertSame($expected, $response->header(), 'A verbs for matching routes should be returned');
	}
}

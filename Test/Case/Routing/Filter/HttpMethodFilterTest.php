<?php

App::uses('HttpMethodFilter', 'Crud.Routing/Filter');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');

class HttpMethodFilterTest extends CakeTestCase {

/**
 * testNoop
 *
 * @return void
 */
	public function testNoop() {
		Router::reload();
		Router::connect('/:controller/:action/*');

		$filter = new HttpMethodFilter();
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		$request = new CakeRequest('controller/action/1');

		$event = new CakeEvent('HttpMethodFilterTest', $this, compact('request', 'response'));

		$this->assertNull($filter->beforeDispatch($event), 'The HttpMethod filter should return null if it does nothing');
		$this->assertFalse($event->isStopped(), 'The HttpMethod filter should not stop the event for !OPTIONS requests');
		$this->assertNull($filter->afterDispatch($event), 'The HttpMethod filter should return null if it does nothing');
	}

/**
 * testOptions
 *
 * @return void
 */
	public function testOptions() {
		Router::reload();
		Router::connect('/:controller/:action/*');

		$filter = new HttpMethodFilter();
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		$request = new CakeRequest('controller/action/1');
		$request->addDetector('options', array(
			'callback' => function() {
				return true;
			}
		));

		$event = new CakeEvent('HttpMethodFilterTest', $this, compact('request', 'response'));

		$this->assertSame($response, $filter->beforeDispatch($event), 'The HttpMethod filter should return a response');
		$this->assertTrue($event->isStopped(), 'The HttpMethod filter should stop the event');

		$expected = array(
			'Access-Control-Allow-Methods' => 'GET, HEAD, POST, PUT, DELETE'
		);
		$this->assertSame($expected, $response->header(), 'A standard route accepts all verbs');
	}

/**
 * testOptionsRestrictedVerbs
 *
 * @return void
 */
	public function testOptionsRestrictedVerbs() {
		Router::reload();
		Router::connect('/:controller/:action/*', array('[method]' => 'GET'));

		$filter = new HttpMethodFilter();
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		$request = new CakeRequest('controller/action/1');
		$request->addDetector('options', array(
			'callback' => function() {
				return true;
			}
		));

		$event = new CakeEvent('HttpMethodFilterTest', $this, compact('request', 'response'));

		$this->assertSame($response, $filter->beforeDispatch($event), 'The HttpMethod filter should return a response');
		$this->assertTrue($event->isStopped(), 'The HttpMethod filter should stop the event');

		$expected = array(
			'Access-Control-Allow-Methods' => 'GET'
		);
		$this->assertSame($expected, $response->header(), 'Only verbs for matching routes should be returned');
	}

/**
 * testOptionsCustomVerbs
 *
 * @return void
 */
	public function testOptionsCustomVerbs() {
		Router::reload();
		Router::connect('/:controller/:action/*', array('[method]' => 'TICKLE'));
		Router::connect('/:controller/:action/*', array('[method]' => 'ANNOY'));

		Configure::write('Crud.HttpMethodFilter.verbs', array('GET', 'TICKLE', 'ANNOY'));

		$filter = new HttpMethodFilter();
		$response = $this->getMock('CakeResponse', array('_sendHeader'));

		$request = new CakeRequest('controller/action/1');
		$request->addDetector('options', array(
			'callback' => function() {
				return true;
			}
		));

		$event = new CakeEvent('HttpMethodFilterTest', $this, compact('request', 'response'));

		$this->assertSame($response, $filter->beforeDispatch($event), 'The HttpMethod filter should return a response');
		$this->assertTrue($event->isStopped(), 'The HttpMethod filter should stop the event');

		$expected = array(
			'Access-Control-Allow-Methods' => 'TICKLE, ANNOY'
		);
		$this->assertSame($expected, $response->header(), 'A verbs for matching routes should be returned');
	}
}

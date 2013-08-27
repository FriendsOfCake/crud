<?php
App::uses('CrudTestCase', 'Crud.Test/Support');

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiTransformationListenerTest extends CrudTestCase {

/**
 * Setup additional classes.
 *
 * @return void
 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		App::uses('Controller', 'Controller');
		App::uses('Model', 'Model');
		App::uses('CakeRequest', 'Network');
		App::uses('IndexCrudAction', 'Crud.Controller/Crud/Action');
		App::uses('ApiTransformationListener', 'Crud.Controller/Crud/Listener');
	}

/**
 * testImplementedEventsApiOnlyIsApi
 *
 * @return void
 */
	public function testImplementedEventsApiOnlyIsApi() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->setMethods(array('_request'))
			->disableOriginalConstructor()
			->getMock();

		$request = $this
			->getMockBuilder('CakeRequest')
			->setMethods(array('is'))
			->disableOriginalConstructor()
			->getMock();

		$listener
			->expects($this->once())
			->method('_request')
			->will($this->returnValue($request));

		$request
			->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => true,
			'changeTime' => true,
			'castNumbers' => true,
			'_keyMethods' => array(),
			'_valueMethods' => array()
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$expected = array('Crud.beforeRender' => array(
			'callable' => 'beforeRender',
			'priority' => 200
		));
		$result = $listener->implementedEvents();
		$this->assertSame($expected, $result);
	}

/**
 * testImplementedEventsApiOnlyIsNotApi
 *
 * @return void
 */
	public function testImplementedEventsApiOnlyIsNotApi() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->setMethods(array('_request'))
			->disableOriginalConstructor()
			->getMock();

		$request = $this
			->getMockBuilder('CakeRequest')
			->setMethods(array('is'))
			->disableOriginalConstructor()
			->getMock();

		$listener
			->expects($this->once())
			->method('_request')
			->will($this->returnValue($request));

		$request
			->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(false));

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => true,
			'changeTime' => true,
			'castNumbers' => true,
			'_keyMethods' => array(),
			'_valueMethods' => array()
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$expected = array();
		$result = $listener->implementedEvents();
		$this->assertSame($expected, $result);
	}

/**
 * testImplementedEventsNotApiOnly
 *
 * @return void
 */
	public function testImplementedEventsNotApiOnly() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->setMethods(array('_request'))
			->disableOriginalConstructor()
			->getMock();

		$listener
			->expects($this->never())
			->method('_request');

		$settings = array(
			'apiOnly' => false,
			'changeNesting' => true,
			'changeKeys' => true,
			'changeTime' => true,
			'castNumbers' => true,
			'_keyMethods' => array(),
			'_valueMethods' => array()
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$expected = array('Crud.beforeRender' => array(
			'callable' => 'beforeRender',
			'priority' => 200
		));
		$result = $listener->implementedEvents();
		$this->assertSame($expected, $result);
	}

/**
 * testBeforeRenderWithNestingChange
 *
 * @return void
 */
	public function testBeforeRenderWithNestingChange() {
		$i = 0;
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->setMethods(array('_controller', '_action', '_model', '_changeNesting', '_recurse', '_setMethods'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('IndexAction')
			->setMethods(array('viewVar'))
			->disableOriginalConstructor()
			->getMock();

		$model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->getMock();

		$listener
			->expects($this->at($i++))
			->method('_controller')
			->will($this->returnValue($controller));

		$listener
			->expects($this->at($i++))
			->method('_action')
			->will($this->returnValue($action));

		$listener
			->expects($this->at($i++))
			->method('_model')
			->will($this->returnValue($model));

		$listener
			->expects($this->at($i++))
			->method('_setMethods');

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => true,
			'changeTime' => true,
			'castNumbers' => true,
			'_keyMethods' => array(),
			'_valueMethods' => array()
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$success = true;
		$users = array(
			0 => array(
				'User' => array('id' => 5, 'name' => 'FriendsOfCake'),
				'Profile' => array('id' => 987, 'twitter' => '@FriendsOfCake')
			),
			1 => array(
				'User' => array('id' => 45, 'name' => 'CakePHP'),
				'Profile' => array('id' => 123, 'twitter' => '@cakephp')
			),
		);

		$controller->viewVars = compact('success', 'users');

		$action
			->expects($this->once())
			->method('viewVar')
			->will($this->returnValue('users'));

		$model->alias = 'User';

		$result = array();

		$nested = array(
			'id' => 5,
			'name' => 'FriendsOfCake',
			'Profile' => array('id' => 987, 'twitter' => '@FriendsOfCake')
		);
		$result[] = $nested;

		$listener
			->expects($this->at($i++))
			->method('_changeNesting')
			->with($this->identicalTo($users[0]), 'User')
			->will($this->returnValue($nested));

		$listener
			->expects($this->at($i++))
			->method('_recurse')
			->with($nested);

		$nested = array(
			'id' => 45,
			'name' => 'CakePHP',
			'Profile' => array('id' => 123, 'twitter' => '@cakephp')
		);
		$result[] = $nested;

		$listener
			->expects($this->at($i++))
			->method('_changeNesting')
			->with($this->identicalTo($users[1]), 'User')
			->will($this->returnValue($nested));

		$listener
			->expects($this->at($i++))
			->method('_recurse')
			->with($nested);

		$listener
			->expects($this->at($i++))
			->method('_controller')
			->will($this->returnValue($controller));

		$controller
			->expects($this->once())
			->method('set')
			->with('users', $result);

		$this->assertTrue($listener->beforeRender());
	}

/**
 * testBeforeRenderWithoutNestingChange
 *
 * @return void
 */
	public function testBeforeRenderWithoutNestingChange() {
		$i = 0;
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->setMethods(array('_controller', '_action', '_model', '_changeNesting', '_recurse', '_setMethods'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('IndexAction')
			->setMethods(array('viewVar'))
			->disableOriginalConstructor()
			->getMock();

		$model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->getMock();

		$listener
			->expects($this->at($i++))
			->method('_controller')
			->will($this->returnValue($controller));

		$listener
			->expects($this->at($i++))
			->method('_action')
			->will($this->returnValue($action));

		$listener
			->expects($this->at($i++))
			->method('_model')
			->will($this->returnValue($model));

		$listener
			->expects($this->at($i++))
			->method('_setMethods');

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => false,
			'changeKeys' => true,
			'changeTime' => true,
			'castNumbers' => true,
			'_keyMethods' => array(),
			'_valueMethods' => array()
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$success = true;
		$users = array(
			0 => array(
				'User' => array('id' => 5, 'name' => 'FriendsOfCake'),
				'Profile' => array('id' => 987, 'twitter' => '@FriendsOfCake')
			),
			1 => array(
				'User' => array('id' => 45, 'name' => 'CakePHP'),
				'Profile' => array('id' => 123, 'twitter' => '@cakephp')
			)
		);

		$controller->viewVars = compact('success', 'users');

		$action
			->expects($this->once())
			->method('viewVar')
			->will($this->returnValue('users'));

		$model->alias = 'User';

		$listener
			->expects($this->never())
			->method('_changeNesting');

		$listener
			->expects($this->at($i++))
			->method('_recurse')
			->with($this->identicalTo($users[0]));

		$listener
			->expects($this->at($i++))
			->method('_recurse')
			->with($this->identicalTo($users[1]));

		$listener
			->expects($this->at($i++))
			->method('_controller')
			->will($this->returnValue($controller));

		$controller
			->expects($this->once())
			->method('set')
			->with('users', $users);

		$this->assertTrue($listener->beforeRender());
	}

/**
 * testBeforeRenderWithFindFirstAndNestingChange
 *
 * @return void
 */
	public function testBeforeRenderWithFindFirstAndNestingChange() {
		$i = 0;
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->setMethods(array('_controller', '_action', '_model', '_changeNesting', '_recurse', '_setMethods'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('IndexAction')
			->setMethods(array('viewVar'))
			->disableOriginalConstructor()
			->getMock();

		$model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->getMock();

		$listener
			->expects($this->at($i++))
			->method('_controller')
			->will($this->returnValue($controller));

		$listener
			->expects($this->at($i++))
			->method('_action')
			->will($this->returnValue($action));

		$listener
			->expects($this->at($i++))
			->method('_model')
			->will($this->returnValue($model));

		$listener
			->expects($this->at($i++))
			->method('_setMethods');

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => true,
			'changeTime' => true,
			'castNumbers' => true,
			'_keyMethods' => array(),
			'_valueMethods' => array()
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$success = true;
		$user = array(
			'User' => array(
				'id' => 5,
				'name' => 'FriendsOfCake'
			),
			'Profile' => array(
				'id' => 987,
				'twitter' => '@FriendsOfCake'
			)
		);

		$controller->viewVars = compact('success', 'user');

		$action
			->expects($this->once())
			->method('viewVar')
			->will($this->returnValue('user'));

		$model->alias = 'User';

		$nested = array(
			'id' => 5,
			'name' => 'FriendsOfCake',
			'Profile' => array('id' => 987, 'twitter' => '@FriendsOfCake')
		);

		$listener
			->expects($this->at($i++))
			->method('_changeNesting')
			->with($this->identicalTo($user), 'User')
			->will($this->returnValue($nested));

		$listener
			->expects($this->at($i++))
			->method('_recurse')
			->with($nested);

		$listener
			->expects($this->at($i++))
			->method('_controller')
			->will($this->returnValue($controller));

		$controller
			->expects($this->once())
			->method('set')
			->with('user', $nested);

		$this->assertTrue($listener->beforeRender());
	}

/**
 * testBeforeRenderWithoutViewVar
 *
 * @return void
 */
	public function testBeforeRenderWithoutViewVar() {
		$i = 0;
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->setMethods(array('_controller', '_action', '_model', '_changeNesting', '_recurse', '_setMethods'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('IndexAction')
			->setMethods(array('viewVar'))
			->disableOriginalConstructor()
			->getMock();

		$model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->getMock();

		$listener
			->expects($this->at($i++))
			->method('_controller')
			->will($this->returnValue($controller));

		$listener
			->expects($this->at($i++))
			->method('_action')
			->will($this->returnValue($action));

		$listener
			->expects($this->at($i++))
			->method('_model')
			->will($this->returnValue($model));

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => true,
			'changeTime' => true,
			'castNumbers' => true,
			'_keyMethods' => array(),
			'_valueMethods' => array()
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$success = true;

		$controller->viewVars = compact('success');

		$action
			->expects($this->once())
			->method('viewVar')
			->will($this->returnValue('users'));

		$model->alias = 'User';

		$listener
			->expects($this->never())
			->method('_changeNesting');

		$listener
			->expects($this->never())
			->method('_recurse');

		$controller
			->expects($this->never())
			->method('set');

		$this->assertTrue($listener->beforeRender());
	}

/**
 * testBeforeRenderWithEmptyViewVar
 *
 * @return void
 */
	public function testBeforeRenderWithEmptyViewVar() {
		$i = 0;
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->setMethods(array('_controller', '_action', '_model', '_changeNesting', '_recurse', '_setMethods'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this
			->getMockBuilder('Controller')
			->setMethods(array('set'))
			->disableOriginalConstructor()
			->getMock();

		$action = $this
			->getMockBuilder('IndexAction')
			->setMethods(array('viewVar'))
			->disableOriginalConstructor()
			->getMock();

		$model = $this
			->getMockBuilder('Model')
			->disableOriginalConstructor()
			->getMock();

		$listener
			->expects($this->at($i++))
			->method('_controller')
			->will($this->returnValue($controller));

		$listener
			->expects($this->at($i++))
			->method('_action')
			->will($this->returnValue($action));

		$listener
			->expects($this->at($i++))
			->method('_model')
			->will($this->returnValue($model));

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => true,
			'changeTime' => true,
			'castNumbers' => true,
			'_keyMethods' => array(),
			'_valueMethods' => array()
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$success = true;
		$users = array();

		$controller->viewVars = compact('success', 'users');

		$action
			->expects($this->once())
			->method('viewVar')
			->will($this->returnValue('users'));

		$model->alias = 'User';

		$listener
			->expects($this->never())
			->method('_changeNesting');

		$listener
			->expects($this->never())
			->method('_recurse');

		$controller
			->expects($this->never())
			->method('set');

		$this->assertTrue($listener->beforeRender());
	}

/**
 * testSetMethods
 *
 * @return void
 */
	public function testSetMethods() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->disableOriginalConstructor()
			->getMock();

		$this->setReflectionClassInstance($listener);

		$closure = function($variable) {
			return $variable;
		};

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => true,
			'changeTime' => true,
			'castNumbers' => true,
			'_keyMethods' => array(),
			'_valueMethods' => array($closure)
		);

		$this->setProtectedProperty('_settings', $settings, $listener);
		$this->callProtectedMethod('_setMethods', array(), $listener);
		$result = $this->getProtectedProperty('_settings', $listener);

		$expected = array('_replaceKeys');
		$this->assertSame($expected, $result['_keyMethods']);

		$expected = array('_castNumbers', '_changeDateToUnix', $closure);
		$this->assertSame($expected, $result['_valueMethods']);

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => false,
			'changeTime' => false,
			'castNumbers' => true,
			'_keyMethods' => array(),
			'_valueMethods' => array($closure)
		);

		$this->setProtectedProperty('_settings', $settings, $listener);
		$this->callProtectedMethod('_setMethods', array(), $listener);
		$result = $this->getProtectedProperty('_settings', $listener);

		$expected = array();
		$this->assertSame($expected, $result['_keyMethods']);

		$expected = array('_castNumbers', $closure);
		$this->assertSame($expected, $result['_valueMethods']);
	}

/**
 * testRecurseWithKeysAndCasts
 *
 * @return void
 */
	public function testRecurseWithKeysAndCasts() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->disableOriginalConstructor()
			->getMock();

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => true,
			'changeTime' => true,
			'castNumbers' => true,
			'_keyMethods' => array('_replaceKeys'),
			'_valueMethods' => array('_castNumbers', '_changeDateToUnix')
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$data = array(
			'User' => array('id' => '5', 'name' => 'FriendsOfCake'),
			'Profile' => array('id' => '987', 'twitter' => '@FriendsOfCake')
		);

		$expected = array(
			'user' => array('id' => 5, 'name' => 'FriendsOfCake'),
			'profile' => array('id' => 987, 'twitter' => '@FriendsOfCake')
		);

		$this->callProtectedMethod('_recurse', array(&$data), $listener);

		$this->assertSame($expected, $data);
	}

/**
 * testRecurseNoCasts
 *
 * @return void
 */
	public function testRecurseNoCasts() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->disableOriginalConstructor()
			->getMock();

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => true,
			'changeTime' => false,
			'castNumbers' => false,
			'_keyMethods' => array('_replaceKeys'),
			'_valueMethods' => array()
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$data = array(
			'User' => array('id' => '5', 'name' => 'FriendsOfCake'),
			'Profile' => array('id' => '987', 'twitter' => '@FriendsOfCake')
		);

		$expected = array(
			'user' => array('id' => '5', 'name' => 'FriendsOfCake'),
			'profile' => array('id' => '987', 'twitter' => '@FriendsOfCake')
		);

		$this->callProtectedMethod('_recurse', array(&$data), $listener);

		$this->assertSame($expected, $data);
	}

/**
 * testRecurseNoKeys
 *
 * @return void
 */
	public function testRecurseNoKeys() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->disableOriginalConstructor()
			->getMock();

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => false,
			'changeTime' => true,
			'castNumbers' => true,
			'_keyMethods' => array(),
			'_valueMethods' => array('_castNumbers', '_changeDateToUnix')
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$data = array(
			'User' => array('id' => '5', 'name' => 'FriendsOfCake', 'created' => '2013-08-26 11:24:54'),
			'Profile' => array('id' => '987', 'twitter' => '@FriendsOfCake')
		);

		$expected = array(
			'User' => array(
				'id' => 5,
				'name' => 'FriendsOfCake',
				'created' => strtotime('2013-08-26 11:24:54')
			),
			'Profile' => array(
				'id' => 987,
				'twitter' => '@FriendsOfCake'
			)
		);

		$this->callProtectedMethod('_recurse', array(&$data), $listener);

		$this->assertSame($expected, $data);
	}

/**
 * testRecurseNoKeysAndNoCasts
 *
 * @return void
 */
	public function testRecurseNoKeysAndNoCasts() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->disableOriginalConstructor()
			->getMock();

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => false,
			'changeTime' => false,
			'castNumbers' => false,
			'_keyMethods' => array(),
			'_valueMethods' => array()
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$data = array(
			'User' => array('id' => '5', 'name' => 'FriendsOfCake'),
			'Profile' => array('id' => '987', 'twitter' => '@FriendsOfCake')
		);

		$expected = $data;

		$this->callProtectedMethod('_recurse', array(&$data), $listener);

		$this->assertSame($expected, $data);
	}

/**
 * testRecurseWithGlobalFunction
 *
 * @return void
 */
	public function testRecurseWithGlobalFunction() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->disableOriginalConstructor()
			->getMock();

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => false,
			'changeTime' => false,
			'castNumbers' => false,
			'_keyMethods' => array(),
			'_valueMethods' => array('strtoupper')
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$data = array(
			'User' => array('id' => '5', 'name' => 'FriendsOfCake'),
			'Profile' => array('id' => '987', 'twitter' => '@FriendsOfCake')
		);

		$expected = array(
			'User' => array('id' => '5', 'name' => 'FRIENDSOFCAKE'),
			'Profile' => array('id' => '987', 'twitter' => '@FRIENDSOFCAKE')
		);

		$this->callProtectedMethod('_recurse', array(&$data), $listener);

		$this->assertSame($expected, $data);
	}

/**
 * testRecurseWithStaticMethod
 *
 * @return void
 */
	public function testRecurseWithStaticMethod() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->disableOriginalConstructor()
			->getMock();

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => false,
			'changeTime' => false,
			'castNumbers' => false,
			'_keyMethods' => array(),
			'_valueMethods' => array('Inflector::slug')
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$data = array(
			'User' => array('id' => '5', 'name' => 'Friends Of Cake'),
			'Profile' => array('id' => '987', 'twitter' => '@FriendsOfCake')
		);

		$expected = array(
			'User' => array('id' => '5', 'name' => 'Friends_Of_Cake'),
			'Profile' => array('id' => '987', 'twitter' => 'FriendsOfCake')
		);

		$this->callProtectedMethod('_recurse', array(&$data), $listener);

		$this->assertSame($expected, $data);
	}

/**
 * testRecurseWithClosure
 *
 * @return void
 */
	public function testRecurseWithClosure() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->disableOriginalConstructor()
			->getMock();

		$uppercase = function($variable) {
			if (!is_string($variable)) {
				return $variable;
			}
			return strtoupper($variable);
		};

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => false,
			'changeTime' => false,
			'castNumbers' => false,
			'_keyMethods' => array(),
			'_valueMethods' => array($uppercase)
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$data = array(
			'User' => array('id' => '5', 'name' => 'FriendsOfCake'),
			'Profile' => array('id' => '987', 'twitter' => '@FriendsOfCake')
		);

		$expected = array(
			'User' => array('id' => '5', 'name' => 'FRIENDSOFCAKE'),
			'Profile' => array('id' => '987', 'twitter' => '@FRIENDSOFCAKE')
		);

		$this->callProtectedMethod('_recurse', array(&$data), $listener);

		$this->assertSame($expected, $data);
	}

/**
 * testChangeNesting
 *
 * @return void
 */
	public function testChangeNesting() {
		$listener = $this
			->getMockBuilder('ApiTransformationListener')
			->disableOriginalConstructor()
			->getMock();

		$settings = array(
			'apiOnly' => true,
			'changeNesting' => true,
			'changeKeys' => false,
			'changeTime' => false,
			'castNumbers' => false,
			'_keyMethods' => array(),
			'_valueMethods' => array()
		);

		$this->setReflectionClassInstance($listener);
		$this->setProtectedProperty('_settings', $settings, $listener);

		$data = array(
			'User' => array('id' => '5', 'name' => 'FriendsOfCake'),
			'Profile' => array('id' => '987', 'twitter' => '@FriendsOfCake')
		);

		$expected = array(
			'id' => '5',
			'name' => 'FriendsOfCake',
			'Profile' => array('id' => '987', 'twitter' => '@FriendsOfCake')
		);

		$result = $this->callProtectedMethod('_changeNesting', array(&$data, 'User'), $listener);

		$this->assertSame($expected, $result);

		$data = array(
			'User' => array('id' => '5', 'name' => 'FriendsOfCake'),
			'Profile' => array('id' => '987', 'twitter' => '@FriendsOfCake')
		);

		$expected = array(
			'id' => '987',
			'twitter' => '@FriendsOfCake',
			'User' => array(
				'id' => '5',
				'name' => 'FriendsOfCake',
			)
		);

		$result = $this->callProtectedMethod('_changeNesting', array(&$data, 'Profile'), $listener);

		$this->assertSame($expected, $result);
	}
}

<?php

App::uses('CakeEvent', 'Event');
App::uses('Controller', 'Controller');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('ScaffoldListener', 'Crud.Controller/Crud/Listener');

require_once CAKE . DS . 'Test' . DS . 'Case' . DS . 'Model' . DS . 'models.php';

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class ScaffoldListenerTest extends CakeTestCase {

/**
 * fixtures property
 *
 * @var array
 */
	public $fixtures = array('core.article', 'core.user', 'core.comment', 'core.join_thing', 'core.tag', 'core.attachment');

	protected $_beforeRenderTests = array(
		// Index (Article)
		array(
			'model' => 'Article',
			'action' => 'index',
			'controller' => 'ArticlesController',
			'expected' => array(
		    'title_for_layout' => 'Scaffold :: Index :: ',
  			'modelClass' => 'Article',
  			'primaryKey' => 'id',
  			'displayField' => 'title',
  			'singularVar' => 'article',
  			'pluralVar' => 'articlesController',
  			'singularHumanName' => 'Article',
  			'pluralHumanName' => 'Articles Controller',
  			'scaffoldFields' => array(
          'id', 'user_id', 'title', 'body', 'published', 'created', 'updated'
        ),
  			'associations' => array(
          'belongsTo' => array(
						'User' => array(
							'primaryKey' => 'id',
              'displayField' => 'id',
							'foreignKey' => 'user_id',
							'plugin' => null,
							'controller' => 'users'
						),
					),
          'hasMany' => array(
          	'Comment' => array(
							'primaryKey' => 'id',
							'displayField' => 'id',
							'foreignKey' => 'article_id',
							'plugin' => null,
							'controller' => 'comments'
						)
          ),
          'hasAndBelongsToMany' => array(
          	'Tag' => array(
							'primaryKey' => 'id',
							'displayField' => 'id',
							'foreignKey' => 'article_id',
							'plugin' => null,
							'controller' => 'tags',
							'with' => 'ArticlesTag',
						)
          )
      	)
			)
		),
		// Add (Article)
		array(
			'model' => 'Article',
			'action' => 'add',
			'controller' => 'ArticlesController',
			'expected' => array(
		    'title_for_layout' => 'Scaffold :: Add :: ',
  			'modelClass' => 'Article',
  			'primaryKey' => 'id',
  			'displayField' => 'title',
  			'singularVar' => 'article',
  			'pluralVar' => 'articlesController',
  			'singularHumanName' => 'Article',
  			'pluralHumanName' => 'Articles Controller',
  			'scaffoldFields' => array(
          'id', 'user_id', 'title', 'body', 'published', 'created', 'updated'
        ),
  			'associations' => array(
          'belongsTo' => array(
						'User' => array(
							'primaryKey' => 'id',
              'displayField' => 'id',
							'foreignKey' => 'user_id',
							'plugin' => null,
							'controller' => 'users'
						),
					),
          'hasMany' => array(
          	'Comment' => array(
							'primaryKey' => 'id',
							'displayField' => 'id',
							'foreignKey' => 'article_id',
							'plugin' => null,
							'controller' => 'comments'
						)
          ),
          'hasAndBelongsToMany' => array(
          	'Tag' => array(
							'primaryKey' => 'id',
							'displayField' => 'id',
							'foreignKey' => 'article_id',
							'plugin' => null,
							'controller' => 'tags',
							'with' => 'ArticlesTag',
						)
          )
      	)
			)
		),
		// Edit (Article)
		array(
			'model' => 'Article',
			'action' => 'edit',
			'controller' => 'ArticlesController',
			'expected' => array(
		    'title_for_layout' => 'Scaffold :: Edit :: ',
  			'modelClass' => 'Article',
  			'primaryKey' => 'id',
  			'displayField' => 'title',
  			'singularVar' => 'article',
  			'pluralVar' => 'articlesController',
  			'singularHumanName' => 'Article',
  			'pluralHumanName' => 'Articles Controller',
  			'scaffoldFields' => array(
          'id', 'user_id', 'title', 'body', 'published', 'created', 'updated'
        ),
  			'associations' => array(
          'belongsTo' => array(
						'User' => array(
							'primaryKey' => 'id',
              'displayField' => 'id',
							'foreignKey' => 'user_id',
							'plugin' => null,
							'controller' => 'users'
						),
					),
          'hasMany' => array(
          	'Comment' => array(
							'primaryKey' => 'id',
							'displayField' => 'id',
							'foreignKey' => 'article_id',
							'plugin' => null,
							'controller' => 'comments'
						)
          ),
          'hasAndBelongsToMany' => array(
          	'Tag' => array(
							'primaryKey' => 'id',
							'displayField' => 'id',
							'foreignKey' => 'article_id',
							'plugin' => null,
							'controller' => 'tags',
							'with' => 'ArticlesTag',
						)
          )
      	)
			)
		),
		// Index (User)
		array(
			'model' => 'User',
			'action' => 'index',
			'controller' => 'UsersController',
			'expected' => array(
		    'title_for_layout' => 'Scaffold :: Index :: ',
  			'modelClass' => 'User',
  			'primaryKey' => 'id',
  			'displayField' => 'id',
  			'singularVar' => 'user',
  			'pluralVar' => 'usersController',
  			'singularHumanName' => 'User',
  			'pluralHumanName' => 'Users Controller',
  			'scaffoldFields' => array(
          'id', 'user', 'password', 'created', 'updated'
        ),
  			'associations' => array(
      	)
			)
		),
		// Index (Comment)
		array(
			'model' => 'Comment',
			'action' => 'index',
			'controller' => 'CommentsController',
			'expected' => array(
		    'title_for_layout' => 'Scaffold :: Index :: ',
  			'modelClass' => 'Comment',
  			'primaryKey' => 'id',
  			'displayField' => 'id',
  			'singularVar' => 'comment',
  			'pluralVar' => 'commentsController',
  			'singularHumanName' => 'Comment',
  			'pluralHumanName' => 'Comments Controller',
  			'scaffoldFields' => array(
          'id', 'article_id', 'user_id', 'comment', 'published', 'created', 'updated'
        ),
  			'associations' => array(
          'belongsTo' => array(
          	'User' => array(
							'primaryKey' => 'id',
							'displayField' => 'id',
							'foreignKey' => 'user_id',
							'plugin' => null,
							'controller' => 'users'
						)
          ),
          'hasOne' => array(
          	'Attachment' => array(
							'primaryKey' => 'id',
							'displayField' => 'id',
							'foreignKey' => 'comment_id',
							'plugin' => null,
							'controller' => 'attachments'
          	)
          )
      	)
			)
		)
	);

	public function beforeRenderProvider() {
		$data = array();

		foreach ($this->_beforeRenderTests as $test) {
			$Request = new CakeRequest(null, false);
			$Request->action = $test['action'];

			$Controller = new Controller($Request);
			$Controller->name = $test['controller'];
			$Controller->modelClass = $test['model'];

			$Model = new $test['model']();

			$Subject = new CrudSubject();
			$Subject->model = $Model;
			$Subject->request = $Request;
			$Subject->controller = $Controller;

			$Event = new CakeEvent('Crud.beforeRender', $Subject);

			$Listener = $this->getMock('ScaffoldListener', null, array($Subject));

			$data[] = array($Listener, $Event, $Subject, $test['expected']);
		}

		return $data;
	}

/**
 * test that the proper names and variable values are set by Scaffold
 *
 * @dataProvider beforeRenderProvider
 * @return void
 */
	public function testScaffoldVariableSetting($listener, $event, $subject, $expected) {
		$listener->beforeRender($event);
		$this->assertEqual($subject->controller->viewVars, $expected);

		return;
		$params = array(
			'plugin' => null,
			'pass' => array(),
			'form' => array(),
			'named' => array(),
			'url' => array('url' => 'admin/scaffold_mock/edit'),
			'controller' => 'scaffold_mock',
			'action' => 'admin_edit',
			'admin' => true,
		);
		$this->Controller->request->base = '';
		$this->Controller->request->webroot = '/';
		$this->Controller->request->here = '/admin/scaffold_mock/edit';
		$this->Controller->request->addParams($params);

		//set router.
		Router::setRequestInfo($this->Controller->request);

		$this->Controller->constructClasses();
		$Scaffold = new TestScaffoldMock($this->Controller, $this->Controller->request);
		$result = $Scaffold->controller->viewVars;

		$this->assertEquals('Scaffold :: Admin Edit :: Scaffold Mock', $result['title_for_layout']);
		$this->assertEquals('Scaffold Mock', $result['singularHumanName']);
		$this->assertEquals('Scaffold Mock', $result['pluralHumanName']);
		$this->assertEquals('ScaffoldMock', $result['modelClass']);
		$this->assertEquals('id', $result['primaryKey']);
		$this->assertEquals('title', $result['displayField']);
		$this->assertEquals('scaffoldMock', $result['singularVar']);
		$this->assertEquals('scaffoldMock', $result['pluralVar']);
		$this->assertEquals(array('id', 'user_id', 'title', 'body', 'published', 'created', 'updated'), $result['scaffoldFields']);
		$this->assertArrayHasKey('plugin', $result['associations']['belongsTo']['User']);
	}

}

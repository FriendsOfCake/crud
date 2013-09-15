<?php

App::uses('CakeEvent', 'Event');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Controller', 'Controller');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('CrudComponent', 'Crud.Controller/Component');
App::uses('ScaffoldListener', 'Crud.Controller/Crud/Listener');

require_once CAKE . DS . 'Test' . DS . 'Case' . DS . 'Model' . DS . 'models.php';

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ScaffoldListenerTest extends CakeTestCase {

/**
 * fixtures property
 *
 * @var array
 */
	public $fixtures = array(
		'core.article',
		'core.user',
		'core.comment',
		'core.join_thing',
		'core.tag',
		'core.attachment'
	);

/**
 * Data used for beforeRenderProvider to setup
 * the tests and environments
 *
 * @var array
 */
	protected $_beforeRenderTests = array(
		// Index (Article)
		array(
			'model' => 'Article',
			'action' => 'index',
			'controller' => 'Articles',
			'className' => 'Index',
			'expected' => array(
				'title_for_layout' => 'Articles :: Index',
				'modelClass' => 'Article',
				'primaryKey' => 'id',
				'displayField' => 'title',
				'singularVar' => 'article',
				'pluralVar' => 'articles',
				'singularHumanName' => 'Article',
				'pluralHumanName' => 'Articles',
				'scaffoldFields' => array(
					'id', 'title', 'user_id', 'body', 'published', 'created', 'updated',
				),
				'scaffoldFieldsData' => array(
					'id' => array(),
					'title' => array(),
					'user_id' => array(),
					'body' => array(),
					'published' => array(),
					'created' => array(),
					'updated' => array(),
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
				),
				'redirect_url' => 'http://localhost/',
				'scaffoldFilters' => array(),
				'action' => 'index',
				'modelSchema' => array(
					'id' => array(
						'type' => 'integer',
						'null' => false,
						'default' => null,
						'length' => 11,
						'key' => 'primary',
					),
					'user_id' => array(
						'type' => 'integer',
						'null' => true,
						'default' => null,
						'length' => 11,
					),
					'title' => array(
						'type' => 'string',
						'null' => true,
						'default' => null,
						'length' => 255,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'body' => array(
						'type' => 'text',
						'null' => true,
						'default' => null,
						'length' => null,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'published' => array(
						'type' => 'string',
						'null' => true,
						'default' => 'N',
						'length' => 1,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'created' => array(
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'length' => null,
					),
					'updated' => array(
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'length' => null,
					),
				),
				'scaffoldSidebarActions' => array(
					array(
						'title' => 'Actions',
						'url' => null,
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'header',
					),
					array(
							'title' => 'Related Actions',
							'url' => null,
							'options' => array(),
							'confirmMessage' => false,
							'_type' => 'header',
					),
					array(
						'title' => 'List Users',
						'url' => array(
							'plugin' => null,
							'controller' => 'users',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New User',
						'url' => array(
							'plugin' => null,
							'controller' => 'users',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'List Comments',
						'url' => array(
							'plugin' => null,
							'controller' => 'comments',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New Comment',
						'url' => array(
							'plugin' => null,
							'controller' => 'comments',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'List Tags',
						'url' => array(
							'plugin' => null,
							'controller' => 'tags',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New Tag',
						'url' => array(
							'plugin' => null,
							'controller' => 'tags',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
				),
				'scaffoldPrimaryKeyValue' => null,
				'scaffoldDisplayFieldValue' => null,
				'scaffoldAdminTitle' => 'Admin',
				'scaffoldPageTitle' => 'Index Articles',
				'scaffoldNavigation' => false,
				'scaffoldControllerActions' => array(
					'model' => array('index'),
					'record' => array(),
				),
			),
		),
		// Add (Article)
		array(
			'model' => 'Article',
			'action' => 'add',
			'controller' => 'Articles',
			'className' => 'Add',
			'expected' => array(
				'title_for_layout' => 'Articles :: Add',
				'modelClass' => 'Article',
				'primaryKey' => 'id',
				'displayField' => 'title',
				'singularVar' => 'article',
				'pluralVar' => 'articles',
				'singularHumanName' => 'Article',
				'pluralHumanName' => 'Articles',
				'scaffoldFields' => array(
					'id', 'title', 'user_id', 'body', 'published', 'created', 'updated',
				),
				'scaffoldFieldsData' => array(
					'id' => array(),
					'title' => array(),
					'user_id' => array(),
					'body' => array(),
					'published' => array(),
					'created' => array(),
					'updated' => array(),
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
				),
				'redirect_url' => 'http://localhost/',
				'scaffoldFilters' => array(),
				'action' => 'add',
				'modelSchema' => array(
					'id' => array(
						'type' => 'integer',
						'null' => false,
						'default' => null,
						'length' => 11,
						'key' => 'primary',
					),
					'user_id' => array(
						'type' => 'integer',
						'null' => true,
						'default' => null,
						'length' => 11,
					),
					'title' => array(
						'type' => 'string',
						'null' => true,
						'default' => null,
						'length' => 255,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'body' => array(
						'type' => 'text',
						'null' => true,
						'default' => null,
						'length' => null,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'published' => array(
						'type' => 'string',
						'null' => true,
						'default' => 'N',
						'length' => 1,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'created' => array(
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'length' => null,
					),
					'updated' => array(
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'length' => null,
					),
				),
				'scaffoldSidebarActions' => array(
					array(
						'title' => 'Actions',
						'url' => null,
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'header',
					),
					array(
						'title' => 'Related Actions',
						'url' => null,
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'header',
					),
					array(
						'title' => 'List Users',
						'url' => array(
							'plugin' => null,
							'controller' => 'users',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New User',
						'url' => array(
							'plugin' => null,
							'controller' => 'users',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'List Comments',
						'url' => array(
							'plugin' => null,
							'controller' => 'comments',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New Comment',
						'url' => array(
							'plugin' => null,
							'controller' => 'comments',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'List Tags',
						'url' => array(
							'plugin' => null,
							'controller' => 'tags',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New Tag',
						'url' => array(
							'plugin' => null,
							'controller' => 'tags',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
				),
				'scaffoldAdminTitle' => 'Admin',
				'scaffoldPageTitle' => 'Add Articles',
				'scaffoldNavigation' => false,
				'scaffoldPrimaryKeyValue' => null,
				'scaffoldDisplayFieldValue' => null,
				'scaffoldControllerActions' => array(
					'model' => array('add'),
					'record' => array(),
				),
			),
		),
		// Edit (Article)
		array(
			'model' => 'Article',
			'action' => 'edit',
			'controller' => 'Articles',
			'className' => 'Edit',
			'expected' => array(
				'title_for_layout' => 'Articles :: Edit',
				'modelClass' => 'Article',
				'primaryKey' => 'id',
				'displayField' => 'title',
				'singularVar' => 'article',
				'pluralVar' => 'articles',
				'singularHumanName' => 'Article',
				'pluralHumanName' => 'Articles',
				'scaffoldFields' => array(
					'id',  'title',  'user_id',  'body',  'published',  'created',  'updated',
				 ),
				'scaffoldFieldsData' => array(
					'id' => array(),
					'title' => array(),
					'user_id' => array(),
					'body' => array(),
					'published' => array(),
					'created' => array(),
					'updated' => array(),
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
				),
				'redirect_url' => 'http://localhost/',
				'scaffoldFilters' => array(),
				'action' => 'edit',
				'modelSchema' => array(
					'id' => array(
						'type' => 'integer',
						'null' => false,
						'default' => null,
						'length' => 11,
						'key' => 'primary',
					),
					'user_id' => array(
						'type' => 'integer',
						'null' => true,
						'default' => null,
						'length' => 11,
					),
					'title' => array(
						'type' => 'string',
						'null' => true,
						'default' => null,
						'length' => 255,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'body' => array(
						'type' => 'text',
						'null' => true,
						'default' => null,
						'length' => null,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'published' => array(
						'type' => 'string',
						'null' => true,
						'default' => 'N',
						'length' => 1,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'created' => array(
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'length' => null,
					),
					'updated' => array(
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'length' => null,
					),
				),
				'scaffoldSidebarActions' => array(
					array(
						'title' => 'Actions',
						'url' => null,
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'header',
					),
					array(
						'title' => 'Related Actions',
						'url' => null,
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'header',
					),
					array(
						'title' => 'List Users',
						'url' => array(
							'plugin' => null,
							'controller' => 'users',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New User',
						'url' => array(
							'plugin' => null,
							'controller' => 'users',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'List Comments',
						'url' => array(
							'plugin' => null,
							'controller' => 'comments',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New Comment',
						'url' => array(
							'plugin' => null,
							'controller' => 'comments',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'List Tags',
						'url' => array(
							'plugin' => null,
							'controller' => 'tags',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New Tag',
						'url' => array(
							'plugin' => null,
							'controller' => 'tags',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
				),
				'scaffoldNavigation' => false,
				'scaffoldControllerActions' => array(
					'model' => array(),
					'record' => array('edit'),
				),
				'scaffoldPrimaryKeyValue' => null,
				'scaffoldDisplayFieldValue' => null,
				'scaffoldAdminTitle' => 'Admin',
				'scaffoldPageTitle' => 'Edit Article',
			),
		),
		// Index (User)
		array(
			'model' => 'User',
			'action' => 'index',
			'controller' => 'Users',
			'className' => 'Index',
			'expected' => array(
				'title_for_layout' => 'Users :: Index',
				'modelClass' => 'User',
				'primaryKey' => 'id',
				'displayField' => 'id',
				'singularVar' => 'user',
				'pluralVar' => 'users',
				'singularHumanName' => 'User',
				'pluralHumanName' => 'Users',
				'scaffoldFields' => array(
					'id', 'user', 'password', 'created', 'updated',
				 ),
				'scaffoldFieldsData' => array(
					'id' => array(),
					'user' => array(),
					'password' => array(),
					'created' => array(),
					'updated' => array(),
				),
				'associations' => array(
					'hasMany' => array(),
					'hasAndBelongsToMany' => array(),
				),
				'redirect_url' => 'http://localhost/',
				'scaffoldFilters' => array(),
				'action' => 'index',
				'modelSchema' => array(
					'id' => array(
						'type' => 'integer',
						'null' => false,
						'default' => null,
						'length' => 11,
						'key' => 'primary',
					),
					'user' => array(
						'type' => 'string',
						'null' => true,
						'default' => null,
						'length' => 255,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'password' => array(
						'type' => 'string',
						'null' => true,
						'default' => null,
						'length' => 255,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'created' => array(
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'length' => null,
					),
					'updated' => array(
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'length' => null,
					),
				),
				'scaffoldSidebarActions' => array(
					array(
						'title' => 'Actions',
						'url' => null,
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'header',
					),
					array(
						'title' => 'Related Actions',
						'url' => null,
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'header',
					),
				),
				'scaffoldNavigation' => false,
				'scaffoldControllerActions' => array(
					'model' => array('index'),
					'record' => array(),
				),
				'scaffoldPrimaryKeyValue' => null,
				'scaffoldDisplayFieldValue' => null,
				'scaffoldAdminTitle' => 'Admin',
				'scaffoldPageTitle' => 'Index Users',
			),
		),
		// Index (Comment)
		array(
			'model' => 'Comment',
			'action' => 'index',
			'controller' => 'Comments',
			'className' => 'Index',
			'expected' => array(
				'title_for_layout' => 'Comments :: Index',
				'modelClass' => 'Comment',
				'primaryKey' => 'id',
				'displayField' => 'id',
				'singularVar' => 'comment',
				'pluralVar' => 'comments',
				'singularHumanName' => 'Comment',
				'pluralHumanName' => 'Comments',
				'scaffoldFields' => array(
					'id',  'comment',  'article_id',  'user_id',  'published',  'created',  'updated',
				),
				'scaffoldFieldsData' => array(
					'id' => array(),
					'comment' => array(),
					'article_id' => array(),
					'user_id' => array(),
					'published' => array(),
					'created' => array(),
					'updated' => array(),
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
						'Article' => array(
							'primaryKey' => 'id',
							'displayField' => 'title',
							'foreignKey' => 'article_id',
							'plugin' => null,
							'controller' => 'articles'
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
					),
					'hasMany' => array(),
					'hasAndBelongsToMany' => array(),
				),
				'redirect_url' => 'http://localhost/',
				'scaffoldFilters' => array(),
				'action' => 'index',
				'modelSchema' => array(
					'id' => array(
						'type' => 'integer',
						'null' => false,
						'default' => null,
						'length' => 11,
						'key' => 'primary',
					),
					'article_id' => array(
						'type' => 'integer',
						'null' => false,
						'default' => null,
						'length' => 11,
					),
					'user_id' => array(
						'type' => 'integer',
						'null' => false,
						'default' => null,
						'length' => 11,
					),
					'comment' => array(
						'type' => 'text',
						'null' => true,
						'default' => null,
						'length' => null,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'published' => array(
						'type' => 'string',
						'null' => true,
						'default' => 'N',
						'length' => 1,
						'collate' => 'utf8_unicode_ci',
						'charset' => 'utf8',
					),
					'created' => array(
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'length' => null,
					),
					'updated' => array(
						'type' => 'datetime',
						'null' => true,
						'default' => null,
						'length' => null,
					),
				),
				'scaffoldSidebarActions' => array(
					array(
						'title' => 'Actions',
						'url' => null,
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'header',
					),
					array(
						'title' => 'Related Actions',
						'url' => null,
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'header',
					),
					array(
						'title' => 'List Articles',
						'url' => array(
							'plugin' => null,
							'controller' => 'articles',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New Article',
						'url' => array(
							'plugin' => null,
							'controller' => 'articles',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'List Users',
						'url' => array(
							'plugin' => null,
							'controller' => 'users',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New User',
						'url' => array(
							'plugin' => null,
							'controller' => 'users',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'List Attachments',
						'url' => array(
							'plugin' => null,
							'controller' => 'attachments',
							'action' => 'index',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
					array(
						'title' => 'New Attachment',
						'url' => array(
							'plugin' => null,
							'controller' => 'attachments',
							'action' => 'add',
						),
						'options' => array(),
						'confirmMessage' => false,
						'_type' => 'link',
					),
				),
				'scaffoldNavigation' => false,
				'scaffoldControllerActions' => array(
					'model' => array('index'),
					'record' => array(),
				),
				'scaffoldPrimaryKeyValue' => null,
				'scaffoldDisplayFieldValue' => null,
				'scaffoldAdminTitle' => 'Admin',
				'scaffoldPageTitle' => 'Index Comments',
			),
		)
	);

/**
 * Data provider for testBeforeRender
 *
 * Setup the required classes and their
 * relations
 *
 * @return array
 */
	public function beforeRenderProvider() {
		$data = array();

		foreach ($this->_beforeRenderTests as $test) {
			$Request = new CakeRequest(null, false);
			$Request->action = $test['action'];
			$Response = new CakeResponse();

			$Controller = new Controller($Request);
			$Controller->name = $test['controller'];
			$Controller->modelClass = $test['model'];
			$Controller->viewPath = Inflector::pluralize($test['model']);
			$Controller->__construct($Request, $Response);
			$Controller->methods = array();

			$Collection = $this->getMock('ComponentCollection', null);
			$Collection->init($Controller);
			$Controller->Components = $Collection;

			$Crud = $this->getMock('CrudComponent', null, array($Collection, array(
				'actions' => array($test['action'] => array('className' => $test['className']))
			)));
			$Crud->initialize($Controller);

			$Model = new $test['model']();

			$Subject = new CrudSubject();
			$Subject->model = $Model;
			$Subject->request = $Request;
			$Subject->controller = $Controller;
			$Subject->crud = $Crud;

			$Event = new CakeEvent('Crud.beforeRender', $Subject);

			$Listener = $this->getMock('ScaffoldListener', null, array($Subject));

			$data[] = array($Listener, $Event, $test['expected']);
		}

		return $data;
	}

/**
 * test that the proper names and variable values are set by Scaffold
 *
 * @dataProvider beforeRenderProvider
 * @param CrudListener $Listener
 * @param CakeEvent $Event
 * @param array $expected
 * @return void
 */
	public function testBeforeRender($Listener, $Event, $expected) {
		$Listener->beforeRender($Event);
		$this->assertEquals($expected, $Event->subject->controller->viewVars);
	}

/**
 * Test that implementedEvents return the correct events
 *
 * @return void
 */
	public function testImplementedEvents() {
		$Subject = new CrudSubject();
		$Listener = $this->getMock('ScaffoldListener', null, array($Subject));

		$expected = array(
			'Crud.beforeRender' => 'beforeRender',
			'Crud.beforeFind' => 'beforeFind',
			'Crud.beforePaginate' => 'beforePaginate'
		);

		$result = $Listener->implementedEvents();
		$this->assertEquals($expected, $result);
	}

}

<?php
/**
 * Short description for class.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class PostsTagFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string 'PostsTag'
 */
	public $name = 'PostsTag';

/**
 * Table to be created
 *
 * @var string 'PostsTag'
 */
	public $table = 'posts_tags';

/**
 * fields property
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'post_id' => array('type' => 'integer', 'null' => false),
		'tag_id' => array('type' => 'integer', 'null' => false)
	);

/**
 * records property
 *
 * @var array
 */
	public $records = array(

	);
}

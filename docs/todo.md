---
title: TODO
layout: default
---

# TODO

Improvements to be done in Crud

## Move the defaults array into the action configuration

{% highlight php %}
<?php
array(
	// Enable CRUD actions
	'Crud.Crud' => array(
		'actions' => array(
			'index',
			'add',
			'edit',
			'view',
			'delete'
		),
		'defaults' => array(
			'actions' => array(
				'add' => array(
					'relatedModels' => array('Author')
				),
				'edit' => array(
					'relatedModels' => array('Tag', 'Cms.Page')
				)
			)
		)
	)
);
?>
{% endhighlight %}

could be

{% highlight php %}
<?php
array(
	// Enable CRUD actions
	'Crud.Crud' => array(
		'actions' => array(
			'index',
			'add' => array(
				'className' => 'MyPlugin.MyAdd',
				'relatedModels' => array('Author')
			),
			'edit' => array(
				'relatedModels' => array('Tag', 'Cms.Page')
			),
			'view',
			'delete'
		)
	)
);
?>
{% endhighlight %}

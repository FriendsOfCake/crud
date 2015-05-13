<?php //netteCache[01]000395a:2:{s:4:"time";s:21:"0.66188200 1431522013";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:19:"Nette\Caching\Cache";i:1;s:9:"checkFile";}i:1;s:75:"/var/www/jippignu/data/www/cakephp.dk/apigen/templates/bootstrap/todo.latte";i:2;i:1347136010;}i:1;a:3:{i:0;a:2:{i:0;s:19:"Nette\Caching\Cache";i:1;s:10:"checkConst";}i:1;s:25:"Nette\Framework::REVISION";i:2;s:28:"$WCREV$ released on $WCDATE$";}}}?><?php

// source file: /var/www/jippignu/data/www/cakephp.dk/apigen/templates/bootstrap/todo.latte

?><?php
// prolog Nette\Latte\Macros\CoreMacros
list($_l, $_g) = Nette\Latte\Macros\CoreMacros::initRuntime($template, 'cr4vy2xr0h')
;
// prolog Nette\Latte\Macros\UIMacros
//
// block title
//
if (!function_exists($_l->blocks['title'][] = '_lb5b1306578a_title')) { function _lb5b1306578a_title($_l, $_args) { extract($_args)
?>Todo<?php
}}

//
// block content
//
if (!function_exists($_l->blocks['content'][] = '_lb49e025a0fe_content')) { function _lb49e025a0fe_content($_l, $_args) { extract($_args)
?><div id="content">
	<h1><?php call_user_func(reset($_l->blocks['title']), $_l, get_defined_vars()) ?></h1>


<?php if ($todoClasses): ?>
	<h2>Classes summary</h2>
	<table class="summary table table-bordered table-striped" id="classes">
<?php call_user_func(reset($_l->blocks['classes']), $_l, array('items' => $todoClasses) + get_defined_vars()) ?>
	</table>
<?php endif ?>

<?php if ($todoInterfaces): ?>
	<h2>Interfaces summary</h2>
	<table class="summary table table-bordered table-striped" id="interfaces">
<?php call_user_func(reset($_l->blocks['classes']), $_l, array('items' => $todoInterfaces) + get_defined_vars()) ?>
	</table>
<?php endif ?>

<?php if ($todoTraits): ?>
	<h2>Traits summary</h2>
	<table class="summary table table-bordered table-striped" id="traits">
<?php call_user_func(reset($_l->blocks['classes']), $_l, array('items' => $todoTraits) + get_defined_vars()) ?>
	</table>
<?php endif ?>

<?php if ($todoExceptions): ?>
	<h2>Exceptions summary</h2>
	<table class="summary table table-bordered table-striped" id="exceptions">
<?php call_user_func(reset($_l->blocks['classes']), $_l, array('items' => $todoExceptions) + get_defined_vars()) ?>
	</table>
<?php endif ?>

<?php if ($todoMethods): ?>
	<h2>Methods summary</h2>
	<table class="summary table table-bordered table-striped" id="methods">
<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator($todoMethods) as $method): ?>
	<tr>
<?php $count = count($method->annotations['todo']) ?>
		<td class="name" rowspan="<?php echo htmlSpecialChars($count) ?>"><a href="<?php echo htmlSpecialChars($template->classUrl($method->declaringClassName)) ?>
"><?php echo Nette\Templating\Helpers::escapeHtml($method->declaringClassName, ENT_NOQUOTES) ?></a></td>
		<td class="name" rowspan="<?php echo htmlSpecialChars($count) ?>"><code><a href="<?php echo htmlSpecialChars($template->methodUrl($method)) ?>
"><?php echo Nette\Templating\Helpers::escapeHtml($method->name, ENT_NOQUOTES) ?>()</a></code></td>
<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator($method->annotations['todo']) as $description): ?>
		<td><?php echo $template->annotation($description, 'todo', $method) ?></td><?php if (!$iterator->isLast()): ?>
</tr><tr><?php endif ?>

<?php $iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>
	</tr>
<?php $iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>
	</table>
<?php endif ?>

<?php if ($todoConstants): ?>
	<h2>Constants summary</h2>
	<table class="summary table table-bordered table-striped" id="constants">
<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator($todoConstants) as $constant): ?>
	<tr>
<?php $count = count($constant->annotations['todo']) ;if ($constant->declaringClassName): ?>
		<td class="name" rowspan="<?php echo htmlSpecialChars($count) ?>"><a href="<?php echo htmlSpecialChars($template->classUrl($constant->declaringClassName)) ?>
"><?php echo Nette\Templating\Helpers::escapeHtml($constant->declaringClassName, ENT_NOQUOTES) ?></a></td>
		<td class="name" rowspan="<?php echo htmlSpecialChars($count) ?>"><code><a href="<?php echo htmlSpecialChars($template->constantUrl($constant)) ?>
"><b><?php echo Nette\Templating\Helpers::escapeHtml($constant->name, ENT_NOQUOTES) ?></b></a></code></td>
<?php else: if ($namespaces || $classes || $interfaces || $traits || $exceptions): ?>
		<td class="name" rowspan="<?php echo htmlSpecialChars($count) ?>"><?php if ($constant->namespaceName): ?>
<a href="<?php echo htmlSpecialChars($template->namespaceUrl($constant->namespaceName)) ?>
"><?php echo Nette\Templating\Helpers::escapeHtml($constant->namespaceName, ENT_NOQUOTES) ?>
</a><?php endif ?>
</td>
<?php endif ?>
		<td rowspan="<?php echo htmlSpecialChars($count) ?>"<?php if ($_l->tmp = array_filter(array('name'))) echo ' class="' . htmlSpecialChars(implode(" ", array_unique($_l->tmp))) . '"' ?>
><code><a href="<?php echo htmlSpecialChars($template->constantUrl($constant)) ?>
"><b><?php echo Nette\Templating\Helpers::escapeHtml($constant->shortName, ENT_NOQUOTES) ?></b></a></code></td>
<?php endif ;$iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator($constant->annotations['todo']) as $description): ?>
		<td><?php echo $template->annotation($description, 'todo', $constant) ?></td><?php if (!$iterator->isLast()): ?>
</tr><tr><?php endif ?>

<?php $iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>
	</tr>
<?php $iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>
	</table>
<?php endif ?>

<?php if ($todoProperties): ?>
	<h2>Properties summary</h2>
	<table class="summary table table-bordered table-striped" id="properties">
<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator($todoProperties) as $property): ?>
	<tr>
<?php $count = count($property->annotations['todo']) ?>
		<td class="name" rowspan="<?php echo htmlSpecialChars($count) ?>"><a href="<?php echo htmlSpecialChars($template->classUrl($property->declaringClassName)) ?>
"><?php echo Nette\Templating\Helpers::escapeHtml($property->declaringClassName, ENT_NOQUOTES) ?></a></td>
		<td class="name" rowspan="<?php echo htmlSpecialChars($count) ?>"><a href="<?php echo htmlSpecialChars($template->propertyUrl($property)) ?>
"><var>$<?php echo Nette\Templating\Helpers::escapeHtml($property->name, ENT_NOQUOTES) ?></var></a></td>
<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator($property->annotations['todo']) as $description): ?>
		<td><?php echo $template->annotation($description, 'todo', $property) ?></td><?php if (!$iterator->isLast()): ?>
</tr><tr><?php endif ?>

<?php $iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>
	</tr>
<?php $iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>
	</table>
<?php endif ?>

<?php if ($todoFunctions): ?>
	<h2>Functions summary</h2>
	<table class="summary table table-bordered table-striped" id="functions">
<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator($todoFunctions) as $function): ?>
	<tr>
<?php $count = count($function->annotations['todo']) ;if ($namespaces): ?>		<td class="name" rowspan="<?php echo htmlSpecialChars($count) ?>
"><?php if ($function->namespaceName): ?><a href="<?php echo htmlSpecialChars($template->namespaceUrl($function->namespaceName)) ?>
"><?php echo Nette\Templating\Helpers::escapeHtml($function->namespaceName, ENT_NOQUOTES) ?>
</a><?php endif ?>
</td>
<?php endif ?>
		<td class="name" rowspan="<?php echo htmlSpecialChars($count) ?>"><code><a href="<?php echo htmlSpecialChars($template->functionUrl($function)) ?>
"><?php echo Nette\Templating\Helpers::escapeHtml($function->shortName, ENT_NOQUOTES) ?></a></code></td>
<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator($function->annotations['todo']) as $description): ?>
		<td><?php echo $template->annotation($description, 'todo', $function) ?></td><?php if (!$iterator->isLast()): ?>
</tr><tr><?php endif ?>

<?php $iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>
	</tr>
<?php $iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>
	</table>
<?php endif ?>
</div>
<?php
}}

//
// block classes
//
if (!function_exists($_l->blocks['classes'][] = '_lbd0b51bba7d_classes')) { function _lbd0b51bba7d_classes($_l, $_args) { extract($_args)
;$iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator($items) as $class): ?>
	<tr>
		<td class="name" rowspan="<?php echo htmlSpecialChars(count($class->annotations['todo'])) ?>
"><a href="<?php echo htmlSpecialChars($template->classUrl($class)) ?>"><?php echo Nette\Templating\Helpers::escapeHtml($class->name, ENT_NOQUOTES) ?></a></td>
<?php $iterations = 0; foreach ($iterator = $_l->its[] = new Nette\Iterators\CachingIterator($class->annotations['todo']) as $description): ?>
		<td><?php echo $template->annotation($description, 'todo', $class) ?></td><?php if (!$iterator->isLast()): ?>
</tr><tr><?php endif ?>

<?php $iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>
	</tr>
<?php $iterations++; endforeach; array_pop($_l->its); $iterator = end($_l->its) ;
}}

//
// end of blocks
//

// template extending and snippets support

$_l->extends = '@layout.latte'; $template->_extended = $_extended = TRUE;


if ($_l->extends) {
	ob_start();

} elseif (!empty($_control->snippetMode)) {
	return Nette\Latte\Macros\UIMacros::renderSnippets($_control, $_l, get_defined_vars());
}

//
// main template
//
 $active = 'todo' ?>

<?php if ($_l->extends) { ob_end_clean(); return Nette\Latte\Macros\CoreMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render(); }
call_user_func(reset($_l->blocks['title']), $_l, get_defined_vars())  ?>


<?php call_user_func(reset($_l->blocks['content']), $_l, get_defined_vars()) ; 
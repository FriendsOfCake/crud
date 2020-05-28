<?php

use Cake\Utility\Inflector;

foreach (${$viewVar}->toArray() as $k => $v) {
	echo "<dt>" . Inflector::humanize($k) . "</dt>";
	echo "<dd>";
	echo $v;
	echo "</dd>";
}

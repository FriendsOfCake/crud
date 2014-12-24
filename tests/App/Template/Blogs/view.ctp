<?php
foreach (${$viewVar}->toArray() as $k => $v) {
	echo "<dt>" . \Cake\Utility\Inflector::humanize($k) . "</dt>";
	echo "<dd>";
	echo $v;
	echo "</dd>";
}

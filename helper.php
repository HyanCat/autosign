<?php

function kvlog($key = null, $value = null) {
	if (is_null($key)) {
		echo PHP_EOL;
		return;
	}
	date_default_timezone_set("Asia/Shanghai");
	echo date('[Y-m-d h:i:s]') . ' ';
	if (is_null($value)) {
		echo $key . PHP_EOL;
	} else {
		echo '[' . $key . "]: " . $value . PHP_EOL;
	}
}
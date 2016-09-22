<?php

require_once '../ParseHelper.php';

$tests = [
	'#id .class > div' => '',
	'h1 + p' => '',
	'h2 ~ p' => '',
	'div, span' => '',
	'#id.class input' => '',
	'input[type=text], [type="submit"]' => '',
];

foreach ($tests as $selector => $xpath) {
	echo "'".$selector."' => '".ParseHelper::css2XPath($selector)."',\n";
}

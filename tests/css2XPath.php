<?php

require_once '../ParseHelper.php';

$tests = [
	'#id .class > div' => '',
	'h1 + p' => '',
	'h2 ~ p' => '',
	'div, span' => '',
	'#id.class input' => '',
];

foreach ($tests as $selector => $xpath) {
	echo "'".$selector."' => '".ParseHelper::css2XPath($selector)."',\n";
}

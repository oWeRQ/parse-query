<?php

require_once '../vendor/autoload.php';

use Parse\SelectorHelper;

$tests = array_merge(
	require('fixtures/selectors-plain.php'),
	require('fixtures/selectors-pseudo.php'),
	require('fixtures/selectors-condition.php'),
	require('fixtures/selectors-complex.php')
);

$passed = $failed = 0;

$start = microtime(true);

$xpath = new DOMXpath(new DOMDocument());

foreach ($tests as $selector => $expect) {
	$expression = SelectorHelper::toXPath($selector);
	$xpath->query($expression);

	if (!assert($expression === $expect, $selector)) {
		$failed++;
		echo "\n'".$selector."' => '".$expression."',\n";
	} else {
		$passed++;
	}
}

$time = round(microtime(true) - $start, 4);

echo "SelectorHelper done, passed: $passed, failed: $failed, time: $time s\n";
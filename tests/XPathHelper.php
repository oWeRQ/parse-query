<?php

require_once '../XPathHelper.php';

$tests = array_merge(
	require('fixtures/selectors-plain.php'),
	require('fixtures/selectors-pseudo.php'),
	require('fixtures/selectors-condition.php'),
	require('fixtures/selectors-complex.php')
);

$passed = $failed = 0;

$start = microtime(true);

foreach ($tests as $selector => $expect) {
	$expression = XPathHelper::toXPath($selector);

	if (!assert($expression === $expect, $selector)) {
		$failed++;
		echo "\n'".$selector."' => '".$expression."',\n";
	} else {
		$passed++;
	}
}

$time = round(microtime(true) - $start, 4);

echo "XPathHelper done, passed: $passed, failed: $failed, time: $time s\n";